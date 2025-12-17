<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Lunar\Models\Language;
use NumaxLab\Cegal\Client as CegalClient;
use NumaxLab\Cegal\Exceptions\CegalApiException;
use NumaxLab\Lunar\Geslib\Models\ProductVariant;
use NumaxLab\Lunar\Geslib\Models\TrustedStockProvider;
use NumaxLab\Lunar\Geslib\Services\CegalAvailability;
use Tests\Dummies\Cegal\FakeAvailabilityCollection;
use Tests\Dummies\Cegal\FakeBookAvailability;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(fn () => Language::factory()->create());

afterEach(fn () => Mockery::close());

it('returns null when cegal is disabled in config', function () {
    config()->set('lunar.geslib.cegal.enabled', false);

    $client = Mockery::mock(CegalClient::class);
    $service = new CegalAvailability($client);

    $variant = new ProductVariant;
    $variant->sku = 'sku-x';
    $variant->gtin = '9780000000000';

    expect($service->getAvailability($variant))->toBeNull();
});

it('returns null when variant has no gtin', function () {
    config()->set('lunar.geslib.cegal.enabled', true);

    $client = Mockery::mock(CegalClient::class);
    $service = new CegalAvailability($client);

    $variant = new ProductVariant;
    $variant->sku = 'sku-x';
    $variant->gtin = null;

    expect($service->getAvailability($variant))->toBeNull();
});

it('returns null when there are no trusted stock providers', function () {
    config()->set('lunar.geslib.cegal.enabled', true);
    Cache::flush();

    $client = Mockery::mock(CegalClient::class, function ($m) {
        $m->shouldNotReceive('getAvailability');
    });

    $service = new CegalAvailability($client);

    $variant = new ProductVariant;
    $variant->sku = 'sku-x';
    $variant->gtin = '9780000000000';

    expect($service->getAvailability($variant))->toBeNull();
});

it('returns null when the client throws a CegalApiException', function () {
    config()->set('lunar.geslib.cegal.enabled', true);
    Cache::flush();

    TrustedStockProvider::create([
        'name' => 'prov-a',
        'sinli_id' => 'S-A',
        'delivery_period' => '1',
        'sort_position' => 0,
    ]);

    $client = Mockery::mock(CegalClient::class, function ($m) {
        $m
            ->shouldReceive('getAvailability')
            ->andThrow(new CegalApiException(0));
    });

    $service = new CegalAvailability($client);

    $variant = new ProductVariant;
    $variant->sku = 'sku-x';
    $variant->gtin = '9780000000000';

    expect($service->getAvailability($variant))->toBeNull();
});

it('returns the trusted provider when client availability matches sinli id', function () {
    config()->set('lunar.geslib.cegal.enabled', true);
    Cache::flush();

    $provider = TrustedStockProvider::create([
        'name' => 'prov-match',
        'sinli_id' => 'SIN-1',
        'delivery_period' => '2',
        'sort_position' => 0,
    ]);

    $client = Mockery::mock(CegalClient::class, function ($m) {
        $m
            ->shouldReceive('getAvailability')
            ->andReturn(FakeAvailabilityCollection::createFake([
                FakeBookAvailability::createFake(sinliId: 'SIN-1'),
            ]));
    });

    $service = new CegalAvailability($client);

    $variant = new ProductVariant;
    $variant->sku = 'sku-x';
    $variant->gtin = '9780000000000';

    $result = $service->getAvailability($variant);

    expect($result)
        ->toBeInstanceOf(TrustedStockProvider::class)
        ->and($result->id)->toEqual($provider->id);
});

it('returns null when client availability does not match any provider', function () {
    config()->set('lunar.geslib.cegal.enabled', true);
    Cache::flush();

    TrustedStockProvider::create([
        'name' => 'prov-a',
        'sinli_id' => 'SIN-1',
        'delivery_period' => '2',
        'sort_position' => 0,
    ]);

    $client = Mockery::mock(CegalClient::class, function ($m) {
        $m
            ->shouldReceive('getAvailability')
            ->andReturn(FakeAvailabilityCollection::createFake([
                FakeBookAvailability::createFake(sinliId: 'OTHER'),
            ]));
    });

    $service = new CegalAvailability($client);

    $variant = new ProductVariant;
    $variant->sku = 'sku-x';
    $variant->gtin = '9780000000000';

    expect($service->getAvailability($variant))->toBeNull();
});

it('caches the client response so the client is only called once', function () {
    config()->set('lunar.geslib.cegal.enabled', true);
    Cache::flush();

    $provider = TrustedStockProvider::create([
        'name' => 'prov-cache',
        'sinli_id' => 'SIN-C',
        'delivery_period' => '2',
        'sort_position' => 0,
    ]);

    $client = Mockery::mock(CegalClient::class);
    $client
        ->shouldReceive('getAvailability')
        ->once()
        ->andReturn(FakeAvailabilityCollection::createFake([
            FakeBookAvailability::createFake(sinliId: 'SIN-C'),
        ]));

    $service = new CegalAvailability($client);

    $variant = new ProductVariant;
    $variant->sku = 'sku-cache';
    $variant->gtin = '9780000000000';

    $first = $service->getAvailability($variant);
    $second = $service->getAvailability($variant);

    expect($first)
        ->toBeInstanceOf(TrustedStockProvider::class)
        ->and($second)->toBeInstanceOf(TrustedStockProvider::class)
        ->and($first->id)->toEqual($provider->id)
        ->and($second->id)->toEqual($provider->id);
});
