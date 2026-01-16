<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Lunar\FieldTypes\Text as FieldText;
use Lunar\Models\Brand;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use NumaxLab\Geslib\Lines\Action;
use NumaxLab\Lunar\Geslib\Events\GeslibArticleCreated;
use NumaxLab\Lunar\Geslib\Events\GeslibArticleUpdated;
use NumaxLab\Lunar\Geslib\InterCommands\ArticleCommand;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(fn() => Language::factory()->create());

afterEach(fn() => Mockery::close());

it('does nothing when the line action is delete and no variant exists', function () {
    Event::fake();

    $article = makeArticleLineMock([
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

    [$type, $status, $language, $editorial] = ensureCollectionsForArticle('5', '10', 'es', 'BR-001', 'COL-01');

    $article = makeArticleLineMock();

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

    $article = makeArticleLineMock();

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

    $article = makeArticleLineMock([
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

    $line = makeArticleLineMock([
        'id' => 'SKU-DEL',
        'action' => Action::fromCode(Action::DELETE),
    ]);

    $command = new ArticleCommand($line);
    $command();

    $product->refresh();

    expect($product->status)->toBe('draft');
});
