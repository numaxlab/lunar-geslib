<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use NumaxLab\Lunar\Geslib\InterCommands\StatusCommand;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(fn () => Language::factory()->create());

function createGeslibStatusProduct(string $statusName, string $geslibCode, int $stock, string $mode): ProductVariant
{
    $group = CollectionGroup::factory()->create(['handle' => StatusCommand::HANDLE]);

    $status = Collection::factory()->create([
        'collection_group_id' => $group->id,
        'geslib_code' => $geslibCode,
        'attribute_data' => [
            'name' => new TranslatedText(collect(['es' => new Text($statusName)])),
        ],
    ]);

    $product = Product::factory()->create();
    $product->collections()->attach($status->id);

    return ProductVariant::factory()->create([
        'product_id' => $product->id,
        'stock' => $stock,
        'purchasable' => $mode,
    ]);
}

it('rejects a descatalogado article even when it still has stock', function () {
    $variant = createGeslibStatusProduct('Descatalogado', '2', 5, 'in_stock');

    expect($variant->product->isNotPurchasableByStatus())->toBeTrue()
        ->and($variant->canBeFulfilledAtQuantity(1))->toBeFalse();
});

it('rejects an agotado article', function () {
    $variant = createGeslibStatusProduct('Agotado', '3', 0, 'in_stock');

    expect($variant->canBeFulfilledAtQuantity(1))->toBeFalse()
        ->and($variant->product->notPurchasableStatus()->translateAttribute('name'))->toBe('Agotado');
});

it('keeps a sellable article without stock purchasable as a backorder', function () {
    $variant = createGeslibStatusProduct('Disponible', '1', 0, 'always');

    expect($variant->product->isNotPurchasableByStatus())->toBeFalse()
        ->and($variant->canBeFulfilledAtQuantity(1))->toBeTrue();
});

it('keeps the inventory rule for sellable articles sold from stock', function () {
    $variant = createGeslibStatusProduct('Disponible', '1', 0, 'in_stock');

    expect($variant->canBeFulfilledAtQuantity(1))->toBeFalse();
});

it('does not block articles whose status is outside the configured list', function () {
    $variant = createGeslibStatusProduct('En preparación', '9', 3, 'in_stock');

    expect($variant->canBeFulfilledAtQuantity(1))->toBeTrue();
});

it('does not block any status when the configuration is empty', function () {
    config(['lunar.geslib.not_purchasable_statuses' => []]);

    $variant = createGeslibStatusProduct('Descatalogado', '2', 5, 'in_stock');

    expect($variant->canBeFulfilledAtQuantity(1))->toBeTrue();
});
