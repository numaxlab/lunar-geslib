<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Language;
use NumaxLab\Lunar\Geslib\Models\ProductVariant;
use NumaxLab\Lunar\Geslib\Models\TrustedStockProvider;
use NumaxLab\Lunar\Geslib\Services\CegalAvailability;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(fn () => Language::factory()->create());

afterEach(fn () => Mockery::close());

test('returns true when purchasable is always', function () {
    $variant = new ProductVariant;
    $variant->purchasable = 'always';

    expect($variant->canBeFulfilledAtQuantity(999))->toBeTrue();
});

test('returns true when cegal availability returns a provider', function () {
    $this->instance(CegalAvailability::class, Mockery::mock(CegalAvailability::class, function ($m) {
        $m
            ->shouldReceive('getAvailability')
            ->andReturn(new TrustedStockProvider);
    }));

    $variant = Mockery::mock(ProductVariant::class)->makePartial();
    $variant->purchasable = 'in_stock';

    $variant
        ->shouldAllowMockingMethod('getTotalInventory')
        ->shouldReceive('getTotalInventory')
        ->andReturn(0);

    expect($variant->canBeFulfilledAtQuantity(10))->toBeTrue();
});

test('returns true when quantity is less than or equal to total inventory and no provider', function () {
    $this->instance(CegalAvailability::class, Mockery::mock(CegalAvailability::class, function ($m) {
        $m
            ->shouldReceive('getAvailability')
            ->andReturnNull();
    }));

    $variant = Mockery::mock(ProductVariant::class)->makePartial();
    $variant->purchasable = 'in_stock';

    $variant
        ->shouldAllowMockingMethod('getTotalInventory')
        ->shouldReceive('getTotalInventory')
        ->andReturn(5);

    expect($variant->canBeFulfilledAtQuantity(5))->toBeTrue();
});

test('returns false when quantity is greater than total inventory and no provider', function () {
    $this->instance(CegalAvailability::class, Mockery::mock(CegalAvailability::class, function ($m) {
        $m
            ->shouldReceive('getAvailability')
            ->andReturnNull();
    }));

    $variant = Mockery::mock(ProductVariant::class)->makePartial();
    $variant->purchasable = 'in_stock';

    $variant
        ->shouldAllowMockingMethod('getTotalInventory')
        ->shouldReceive('getTotalInventory')
        ->andReturn(2);

    expect($variant->canBeFulfilledAtQuantity(3))->toBeFalse();
});

test('returns true for variants without gtin when inventory is sufficient', function () {
    $this->instance(CegalAvailability::class, Mockery::mock(CegalAvailability::class, function ($m) {
        $m->shouldReceive('getAvailability')->andReturnNull();
    }));

    $variant = Mockery::mock(ProductVariant::class)->makePartial();
    $variant->purchasable = 'in_stock';
    $variant->gtin = null;

    $variant
        ->shouldAllowMockingMethod('getTotalInventory')
        ->shouldReceive('getTotalInventory')
        ->andReturn(10);

    expect($variant->canBeFulfilledAtQuantity(3))->toBeTrue();
});

test('returns false for variants without gtin when inventory is insufficient', function () {
    $this->instance(CegalAvailability::class, Mockery::mock(CegalAvailability::class, function ($m) {
        $m->shouldReceive('getAvailability')->andReturnNull();
    }));

    $variant = Mockery::mock(ProductVariant::class)->makePartial();
    $variant->purchasable = 'in_stock';
    $variant->gtin = null;

    $variant
        ->shouldAllowMockingMethod('getTotalInventory')
        ->shouldReceive('getTotalInventory')
        ->andReturn(1);

    expect($variant->canBeFulfilledAtQuantity(2))->toBeFalse();
});
