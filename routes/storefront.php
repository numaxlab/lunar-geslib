<?php

use Illuminate\Support\Facades\Route;
use NumaxLab\Lunar\Geslib\Http\Controllers\Storefront\ProductController;

Route::get('/products', [ProductController::class, 'index'])
    ->name('lunar.geslib.storefront.products.index');

Route::get('/products/{slug}', [ProductController::class, 'show'])
    ->name('lunar.geslib.storefront.products.show');
