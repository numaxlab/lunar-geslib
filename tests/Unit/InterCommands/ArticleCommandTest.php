<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Lunar\FieldTypes\Text as FieldText;
use Lunar\Models\Brand;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use NumaxLab\Geslib\Lines\Action;
use NumaxLab\Geslib\Lines\Article as ArticleLine;
use NumaxLab\Lunar\Geslib\Events\GeslibArticleCreated;
use NumaxLab\Lunar\Geslib\Events\GeslibArticleUpdated;
use NumaxLab\Lunar\Geslib\InterCommands\ArticleCommand;
use NumaxLab\Lunar\Geslib\InterCommands\CollectionCommand;
use NumaxLab\Lunar\Geslib\InterCommands\LanguageCommand;
use NumaxLab\Lunar\Geslib\InterCommands\StatusCommand;
use NumaxLab\Lunar\Geslib\InterCommands\TypeCommand;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create();

    CollectionGroup::factory(['handle' => LanguageCommand::HANDLE])->create();
    CollectionGroup::factory(['handle' => TypeCommand::HANDLE])->create();
    CollectionGroup::factory(['handle' => StatusCommand::HANDLE])->create();
    CollectionGroup::factory(['handle' => CollectionCommand::HANDLE])->create();
});

afterEach(fn() => Mockery::close());

function makeArticleMock(array $overrides = []): ArticleLine
{
    $defaults = [
        'id' => 'SKU-123',
        'action' => Action::fromCode(Action::ADD),
        'title' => 'A title',
        'subtitle' => 'A subtitle',
        'createdAt' => now(),
        'noveltyDate' => null,
        'edition' => null,
        'firstEditionYear' => 2020,
        'lastEditionYear' => 2024,
        'originalTitle' => 'Original Title',
        'originalLanguageId' => 'es-ORIG',
        'pagesQty' => 111,
        'illustrationsQty' => 0,
        'editorialId' => 'BR-001',
        'collectionId' => 'COL-01',
        'typeId' => 5,
        'statusId' => 10,
        'languageId' => 'es',
        'taxes' => 1,
        'isbn' => '9780000000000',
        'ean' => '0000000000000',
        'width' => 10,
        'height' => 20,
        'weight' => 30,
        'stock' => 7,
        'priceWithoutTaxes' => 1000,
        'referencePrice' => 1200,
    ];

    $data = array_merge($defaults, $overrides);

    $article = Mockery::mock(ArticleLine::class);

    $article->shouldReceive('id')->andReturn($data['id'])->byDefault();
    $article->shouldReceive('action')->andReturn($data['action'])->byDefault();
    $article->shouldReceive('title')->andReturn($data['title'])->byDefault();
    $article->shouldReceive('subtitle')->andReturn($data['subtitle'])->byDefault();
    $article->shouldReceive('createdAt')->andReturn($data['createdAt'])->byDefault();
    $article->shouldReceive('noveltyDate')->andReturn($data['noveltyDate'])->byDefault();
    $article->shouldReceive('firstEditionYear')->andReturn($data['firstEditionYear'])->byDefault();
    $article->shouldReceive('lastEditionYear')->andReturn($data['lastEditionYear'])->byDefault();
    $article->shouldReceive('originalTitle')->andReturn($data['originalTitle'])->byDefault();
    $article->shouldReceive('originalLanguageId')->andReturn($data['originalLanguageId'])->byDefault();
    $article->shouldReceive('pagesQty')->andReturn($data['pagesQty'])->byDefault();
    $article->shouldReceive('illustrationsQty')->andReturn($data['illustrationsQty'])->byDefault();
    $article->shouldReceive('editorialId')->andReturn($data['editorialId'])->byDefault();
    $article->shouldReceive('collectionId')->andReturn($data['collectionId'])->byDefault();
    $article->shouldReceive('typeId')->andReturn($data['typeId'])->byDefault();
    $article->shouldReceive('statusId')->andReturn($data['statusId'])->byDefault();
    $article->shouldReceive('languageId')->andReturn($data['languageId'])->byDefault();
    $article->shouldReceive('taxes')->andReturn($data['taxes'])->byDefault();
    $article->shouldReceive('isbn')->andReturn($data['isbn'])->byDefault();
    $article->shouldReceive('ean')->andReturn($data['ean'])->byDefault();
    $article->shouldReceive('width')->andReturn($data['width'])->byDefault();
    $article->shouldReceive('height')->andReturn($data['height'])->byDefault();
    $article->shouldReceive('weight')->andReturn($data['weight'])->byDefault();
    $article->shouldReceive('stock')->andReturn($data['stock'])->byDefault();
    $article->shouldReceive('priceWithoutTaxes')->andReturn($data['priceWithoutTaxes'])->byDefault();
    $article->shouldReceive('referencePrice')->andReturn($data['referencePrice'])->byDefault();

    if (array_key_exists('edition', $data) && $data['edition']) {
        $article->shouldReceive('edition')->andReturn($data['edition']);
    } else {
        $article->shouldReceive('edition')->andReturn(null);
    }

    return $article;
}

function ensureCollectionsForArticle(
    string $typeId,
    string $statusId,
    string $languageId,
    string $editorialId,
    string $collectionId,
): array {
    $langGroup = CollectionGroup::where('handle', LanguageCommand::HANDLE)->first();
    $typeGroup = CollectionGroup::where('handle', TypeCommand::HANDLE)->first();
    $statusGroup = CollectionGroup::where('handle', StatusCommand::HANDLE)->first();
    $editorialGroup = CollectionGroup::where('handle', CollectionCommand::HANDLE)->first();

    $language = Collection::factory()->create([
        'collection_group_id' => $langGroup->id,
        'geslib_code' => $languageId,
    ]);

    $type = Collection::factory()->create([
        'collection_group_id' => $typeGroup->id,
        'geslib_code' => $typeId,
    ]);

    $status = Collection::factory()->create([
        'collection_group_id' => $statusGroup->id,
        'geslib_code' => $statusId,
    ]);

    $editorial = Collection::factory()->create([
        'collection_group_id' => $editorialGroup->id,
        'geslib_code' => CollectionCommand::getGeslibId($editorialId, $collectionId),
    ]);

    return [$language, $type, $status, $editorial];
}

it('does nothing when the line action is delete and no variant exists', function () {
    Event::fake();

    $article = makeArticleMock([
        'action' => Action::fromCode(Action::DELETE),
        'id' => 'NO-VARIANT',
    ]);

    $command = new ArticleCommand($article);
    $command();

    expect(Product::count())
        ->toBe(0)
        ->and(ProductVariant::count())->toBe(0);
});

it('creates a new product and variant for a physical article and dispatches created event', function () {
    Event::fake();

    Brand::factory()->create(['geslib_code' => 'BR-001']);

    [$language, $type, $status, $editorial] = ensureCollectionsForArticle('5', '10', 'es', 'BR-001', 'COL-01');

    $article = makeArticleMock();

    $command = new ArticleCommand($article, false);
    $command();

    $product = Product::first();
    $variant = ProductVariant::first();

    expect($product)
        ->not
        ->toBeNull()
        ->and($product->status)->toBe(ArticleCommand::DEFAULT_STATUS)
        ->and($variant)->not
        ->toBeNull()
        ->and($variant->sku)->toBe('SKU-123')
        ->and($variant->shippable)->toBe(1)
        ->and($variant->stock)->toBe(7)
        ->and($variant->prices)->toHaveCount(1);

    $price = $variant->prices->first();

    expect((int) $price->getRawOriginal('price'))
        ->toBe(1000)
        ->and((int) $price->getRawOriginal('compare_price'))->toBe(1200);

    $product->refresh();

    $collectionIds = $product->collections->pluck('id')->all();

    expect($collectionIds)
        ->toContain($language->id)
        ->toContain($type->id)
        ->toContain($status->id)
        ->toContain($editorial->id);

    Event::assertDispatched(GeslibArticleCreated::class);
});

it('creates an ebook variant with shippable set to false', function () {
    Event::fake();

    Brand::factory()->create(['geslib_code' => 'BR-001']);
    ensureCollectionsForArticle('5', '10', 'es', 'BR-001', 'COL-01');

    $article = makeArticleMock();

    $command = new ArticleCommand($article, true);
    $command();

    $variant = ProductVariant::first();

    expect($variant->shippable)->toBe(0);

    Event::assertDispatched(GeslibArticleCreated::class);
});

it('updates an existing product/variant and dispatches updated event', function () {
    Event::fake();

    $product = Product::factory()->create([
        'attribute_data' => collect(['name' => new FieldText('Old Name')]),
        'status' => 'published',
    ]);
    $variant = ProductVariant::factory()->for($product)->create([
        'sku' => 'SKU-123',
        'stock' => 1,
    ]);
    $variant->prices()->create([
        'price' => 500,
        'compare_price' => 600,
        'currency_id' => config('lunar.geslib.currency_id', 1),
        'min_quantity' => 1,
    ]);

    Brand::factory()->create(['geslib_code' => 'BR-001']);

    ensureCollectionsForArticle('5', '77', 'es', 'BR-001', 'COL-01');

    config()->set('lunar.geslib.not_purchasable_statuses', [77]);

    $article = makeArticleMock([
        'statusId' => 77,
        'stock' => 3,
        'priceWithoutTaxes' => 1500,
        'referencePrice' => 1800,
        'width' => 11,
        'height' => 21,
        'weight' => 31,
    ]);

    $command = new ArticleCommand($article, false);
    $command();

    $product->refresh();
    $variant->refresh();

    expect($variant->stock)
        ->toBe(3)
        ->and($variant->purchasable)->toBe('in_stock')
        ->and((int) $variant->width_value)->toBe(11)
        ->and((int) $variant->height_value)->toBe(21)
        ->and((int) $variant->weight_value)->toBe(31);

    $updatedPrice = $variant->prices()->first();

    expect((int) $updatedPrice->getRawOriginal('price'))
        ->toBe(1500)
        ->and((int) $updatedPrice->getRawOriginal('compare_price'))->toBe(1800);

    Event::assertDispatched(GeslibArticleUpdated::class);
});

it('sets product status to draft on delete when variant exists', function () {
    Event::fake();

    $product = Product::factory()->create(['status' => 'published']);
    $variant = ProductVariant::factory()->for($product)->create([
        'sku' => 'SKU-DEL',
    ]);
    $variant->prices()->create([
        'price' => 500,
        'compare_price' => 600,
        'currency_id' => 1,
        'min_quantity' => 1,
    ]);

    $line = makeArticleMock([
        'id' => 'SKU-DEL',
        'action' => Action::fromCode(Action::DELETE),
    ]);

    $command = new ArticleCommand($line);
    $command();

    $product->refresh();

    expect($product->status)->toBe('draft');
});
