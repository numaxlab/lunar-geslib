<?php

use Illuminate\Support\Facades\Route;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\CheckoutPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\CollectionListPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\CollectionPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\ProductListPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\ProductPage;

Route::get('/', ProductListPage::class)
    ->name('lunar.geslib.storefront.products.index');

Route::get('/products/{slug}', ProductPage::class)
    ->name('lunar.geslib.storefront.products.show');

Route::get('/collections', CollectionListPage::class)
    ->name('lunar.geslib.storefront.collections.index');

Route::get('/collections/{slug}', CollectionPage::class)
    ->name('lunar.geslib.storefront.collections.show');

Route::get('/checkout', CheckoutPage::class)
    ->name('lunar.geslib.storefront.checkout.index');
