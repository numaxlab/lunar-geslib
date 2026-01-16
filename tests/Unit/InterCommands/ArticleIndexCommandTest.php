<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Language;
use NumaxLab\Geslib\Lines\ArticleIndex;
use NumaxLab\Lunar\Geslib\InterCommands\ArticleIndexCommand;
use NumaxLab\Lunar\Geslib\Models\Product;
use NumaxLab\Lunar\Geslib\Models\ProductVariant;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(fn() => Language::factory()->create());

it('saves index in product attribute_data', function () {
    $product = Product::factory()->create();

    ProductVariant::factory()->for($product)->create([
        'sku' => '12345',
    ]);

    $line = ArticleIndex::fromLine([
        ArticleIndex::CODE,
        '12345',
        '1',
        'Article index content',
    ]);

    $command = new ArticleIndexCommand($line);
    $command();

    $product->refresh();

    expect($product->translateAttribute('index'))->toBe('Article index content');
});
