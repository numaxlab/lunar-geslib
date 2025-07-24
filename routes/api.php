<?php

use Illuminate\Support\Facades\Route;
use NumaxLab\Lunar\Geslib\Http\Controllers\Api\OrderController;

Route::prefix('api/geslib')
    ->middleware('api')
    ->group(function () {
        Route::get('/orders/pending', [OrderController::class, 'indexPending'])
            ->name('lunar.geslib.orders.pending');

        Route::get('/orders/{reference}', [OrderController::class, 'show'])
            ->name('lunar.geslib.orders.show');

        Route::get('/orders/{reference}/sync', [OrderController::class, 'sync'])
            ->name('lunar.geslib.orders.sync');
    });
