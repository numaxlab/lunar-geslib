<?php

use Illuminate\Support\Facades\Route;
// use NumaxLab\Lunar\Geslib\Admin\Http\Livewire\Components\GeslibDashboard; // Removed
// use NumaxLab\Lunar\Geslib\Admin\Http\Livewire\Components\GeslibFileImportLog; // Removed
// use NumaxLab\Lunar\Geslib\Admin\Http\Livewire\Components\GeslibOrderExportLog; // Removed

Route::group([
    'prefix' => config('lunar.admin.route_prefix', 'adminhub'),
    'middleware' => config('lunar.admin.route_middleware', ['web', 'auth', 'can:access-hub']), // Default Lunar admin middleware
    'as' => 'adminhub.geslib.', // Route name prefix
], function () {
    // Route::get('/geslib-dashboard', GeslibDashboard::class)->name('dashboard'); // Removed
    // Route::get('/geslib-file-import-log', GeslibFileImportLog::class)->name('file-import-log'); // Removed
    // Route::get('/geslib-order-export-log', GeslibOrderExportLog::class)->name('order-export-log'); // Removed
});
