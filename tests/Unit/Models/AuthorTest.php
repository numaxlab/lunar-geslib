<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection as SupportCollection;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\Url;
use NumaxLab\Lunar\Geslib\Models\Author;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(fn () => Language::factory()->create());

it('can be created', function () {
    $author = Author::factory()->create([
        'name' => 'Naomi Klein',
    ]);

    $this->assertDatabaseHas(Author::class, [
        'id' => $author->id,
        'name' => 'Naomi Klein',
    ]);
});

it('has a products relationship', function () {
    $author = Author::factory()->create();
    $product = Product::factory()->create();

    $author->products()->attach($product);

    $this->assertCount(1, $author->products);
    $this->assertInstanceOf(Product::class, $author->products->first());
});

it('can have urls', function () {
    $author = Author::factory()->create();

    $author->urls()->create([
        'default' => true,
        'language_id' => 1,
        'slug' => 'john-doe',
    ]);

    $this->assertDatabaseHas(Url::class, [
        'element_type' => Author::class,
        'element_id' => $author->id,
        'slug' => 'john-doe',
    ]);

    $this->assertInstanceOf(Url::class, $author->urls->first());
});

it('can have media', function () {
    Storage::fake('media');

    $author = Author::factory()->create();

    $this->assertInstanceOf(Collection::class, $author->media);

    $file = UploadedFile::fake()->image('photo1.jpg');

    try {
        $author->addMedia($file)->toMediaCollection('images');
    } catch (FileDoesNotExist|FileIsTooBig $e) {
        $this->fail('Could not add media');
    }

    $this->assertDatabaseHas(Media::class, [
        'model_type' => Author::class,
        'model_id' => $author->id,
        'file_name' => 'photo1.jpg',
    ]);
});

it('is searchable', function () {
    $author = Author::factory()->create();
    $this->assertTrue($author->shouldBeSearchable());
});

it('has attributes', function () {
    $author = Author::factory()->create([
        'attribute_data' => collect([
            'biography' => new \Lunar\FieldTypes\Text('Naomi Klein is a Canadian author and social activist.'),
        ]),
    ]);

    $this->assertInstanceOf(SupportCollection::class, $author->attribute_data);
});
