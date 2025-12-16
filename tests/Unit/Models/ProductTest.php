<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\Toggle;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Language;
use NumaxLab\Geslib\Lines\AuthorType;
use NumaxLab\Lunar\Geslib\Handle;
use NumaxLab\Lunar\Geslib\Models\Author;
use NumaxLab\Lunar\Geslib\Models\Collection as LunarGeslibCollection;
use NumaxLab\Lunar\Geslib\Models\Product;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(fn () => Language::factory()->create());

it('has a contributors relationship', function () {
    $product = Product::factory()->create();

    expect($product->contributors())
        ->toBeInstanceOf(BelongsToMany::class)
        ->and($product->contributors()->getModel())->toBeInstanceOf(Author::class)
        ->and($product->contributors)->toBeInstanceOf(Collection::class)
        ->toHaveCount(0);
});

it('has all contributor types relationships', function () {
    $product = Product::factory()->create();
    $author1 = Author::factory()->create();
    $author2 = Author::factory()->create();
    $author3 = Author::factory()->create();
    $author4 = Author::factory()->create();
    $author5 = Author::factory()->create();

    $product->contributors()->attach($author1, [
        'position' => 1,
        'author_type' => AuthorType::AUTHOR,
    ]);
    $product->contributors()->attach($author2, [
        'position' => 1,
        'author_type' => AuthorType::TRANSLATOR,
    ]);
    $product->contributors()->attach($author3, [
        'position' => 1,
        'author_type' => AuthorType::ILLUSTRATOR,
    ]);
    $product->contributors()->attach($author4, [
        'position' => 1,
        'author_type' => AuthorType::COVER_ILLUSTRATOR,
    ]);
    $product->contributors()->attach($author5, [
        'position' => 1,
        'author_type' => AuthorType::BACK_COVER_ILLUSTRATOR,
    ]);

    $product->refresh();

    expect($product->contributors)
        ->toHaveCount(5)
        ->each
        ->toBeInstanceOf(Author::class)
        // authors
        ->and($product->authors)
        ->toHaveCount(1)
        ->and($product->authors->first()->id)->toBe($author1->id)
        ->and($product->authors->first()->pivot->position)->toBe(1)
        // translators
        ->and($product->translators)
        ->toHaveCount(1)
        ->and($product->translators->first()->id)->toBe($author2->id)
        // illustrators
        ->and($product->illustrators)
        ->toHaveCount(1)
        ->and($product->illustrators->first()->id)->toBe($author3->id)
        // coverIllustrators
        ->and($product->coverIllustrators)
        ->toHaveCount(1)
        ->and($product->coverIllustrators->first()->id)->toBe($author4->id)
        // backCoverIllustrators
        ->and($product->backCoverIllustrators)
        ->toHaveCount(1)
        ->and($product->backCoverIllustrators->first()->id)->toBe($author5->id);
});

it('has a favouriteOf relationship', function () {
    $product = Product::factory()->create();

    expect($product->favouriteOf())
        ->toBeInstanceOf(BelongsToMany::class)
        ->and($product->favouriteOf)->toBeInstanceOf(Collection::class);
});

it('has a taxonomies relationship', function () {
    $product = Product::factory()->create();

    expect($product->taxonomies())
        ->toBeInstanceOf(BelongsToMany::class)
        ->and($product->taxonomies()->getModel())->toBeInstanceOf(LunarGeslibCollection::class)
        ->and($product->taxonomies)->toBeInstanceOf(Collection::class)
        ->toHaveCount(0);

    $collectionGroup = CollectionGroup::factory([
        'handle' => Handle::COLLECTION_GROUP_TAXONOMIES,
    ])->create();

    $collection = LunarGeslibCollection::factory()->create([
        'collection_group_id' => $collectionGroup->id,
        'attribute_data' => collect([
            'name' => new TranslatedText(collect([
                'es' => new Text('Ecología'),
            ])),
            'is-section' => new Toggle(false),
        ]),
    ]);

    $product->taxonomies()->attach($collection);

    $product->refresh();

    expect($product->taxonomies)->toHaveCount(1);
});

it('has an empty section taxonomy', function () {
    $product = Product::factory()->create();

    expect($product->getSectionTaxonomy())->toBeNull();

    $collectionGroup = CollectionGroup::factory([
        'handle' => Handle::COLLECTION_GROUP_TAXONOMIES,
    ])->create();

    $collection = LunarGeslibCollection::factory()->create([
        'collection_group_id' => $collectionGroup->id,
        'attribute_data' => collect([
            'name' => new TranslatedText(collect([
                'es' => new Text('Ecología'),
            ])),
            'is-section' => new Toggle(false),
        ]),
    ]);

    $product->taxonomies()->attach($collection);

    $product->refresh();

    expect($product->getSectionTaxonomy())->toBeNull();
});

it('has a section taxonomy', function () {
    $collectionGroup = CollectionGroup::factory([
        'handle' => Handle::COLLECTION_GROUP_TAXONOMIES,
    ])->create();

    $collection = LunarGeslibCollection::factory()->create([
        'collection_group_id' => $collectionGroup->id,
        'attribute_data' => collect([
            'name' => new TranslatedText(collect([
                'es' => new Text('Ecología'),
            ])),
            'is-section' => new Toggle(true),
        ]),
    ]);

    $product = Product::factory()->create();

    $product->taxonomies()->attach($collection);

    $product->refresh();

    expect($product->taxonomies->count())
        ->toBe(1)
        ->and($product->getSectionTaxonomy())->toBeInstanceOf(LunarGeslibCollection::class)
        ->and($product->getSectionTaxonomy()->id)->toBe($collection->id);
});

it('has an ancestor section taxonomy', function () {
    $collectionGroup = CollectionGroup::factory([
        'handle' => Handle::COLLECTION_GROUP_TAXONOMIES,
    ])->create();

    $childCollection = LunarGeslibCollection::factory()->create([
        'collection_group_id' => $collectionGroup->id,
        'attribute_data' => collect([
            'name' => new TranslatedText(collect([
                'es' => new Text('Agroecología'),
            ])),
            'is-section' => new Toggle(false),
        ]),
    ]);

    $parentCollection = LunarGeslibCollection::factory()->create([
        'collection_group_id' => $collectionGroup->id,
        'attribute_data' => collect([
            'name' => new TranslatedText(collect([
                'es' => new Text('Ecología'),
            ])),
            'is-section' => new Toggle(true),
        ]),
    ]);

    $childCollection->appendToNode($parentCollection)->save();

    $product = Product::factory()->create();

    $product->taxonomies()->attach($childCollection);

    $product->refresh();

    expect($product->getSectionTaxonomy())
        ->toBeInstanceOf(LunarGeslibCollection::class)
        ->and($product->getSectionTaxonomy()->id)->toBe($parentCollection->id)
        ->and($product->taxonomies->count())->toBe(1);
});

it('has a editorialCollections relationship', function () {
    $product = Product::factory()->create();

    expect($product->editorialCollections())
        ->toBeInstanceOf(BelongsToMany::class)
        ->and($product->editorialCollections()->getModel())->toBeInstanceOf(LunarGeslibCollection::class)
        ->and($product->editorialCollections)->toBeInstanceOf(Collection::class)
        ->toHaveCount(0);
});

it('has a languages relationship', function () {
    $product = Product::factory()->create();

    expect($product->languages())
        ->toBeInstanceOf(BelongsToMany::class)
        ->and($product->languages()->getModel())->toBeInstanceOf(LunarGeslibCollection::class)
        ->and($product->languages)->toBeInstanceOf(Collection::class)
        ->toHaveCount(0);
});

it('has a statuses relationship', function () {
    $product = Product::factory()->create();

    expect($product->statuses())
        ->toBeInstanceOf(BelongsToMany::class)
        ->and($product->statuses()->getModel())->toBeInstanceOf(LunarGeslibCollection::class)
        ->and($product->statuses)->toBeInstanceOf(Collection::class)
        ->toHaveCount(0);
});

it('has a bindingTypes relationship', function () {
    $product = Product::factory()->create();

    expect($product->bindingTypes())
        ->toBeInstanceOf(BelongsToMany::class)
        ->and($product->bindingTypes()->getModel())->toBeInstanceOf(LunarGeslibCollection::class)
        ->and($product->bindingTypes)->toBeInstanceOf(Collection::class)
        ->toHaveCount(0);
});

it('has a recordFullTitle accessor', function () {
    $product = Product::factory()->create([
        'attribute_data' => collect([
            'name' => new Text('Book title'),
            'subtitle' => new Text('with a subtitle'),
        ]),
    ]);

    expect($product->record_full_title)
        ->toBe('Book title - with a subtitle');
});
