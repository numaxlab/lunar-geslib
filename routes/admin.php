<?php

use Illuminate\Support\Facades\Route;
use NumaxLab\LunarGeslib\Admin\Http\Livewire\Components\GeslibDashboard;
use NumaxLab\LunarGeslib\Admin\Http\Livewire\Components\GeslibFileImportLog;
use NumaxLab\LunarGeslib\Admin\Http\Livewire\Components\GeslibOrderExportLog; // Added

Route::group([
    'prefix' => config('lunar.admin.route_prefix', 'adminhub'),
    'middleware' => config('lunar.admin.route_middleware', ['web', 'auth', 'can:access-hub']), // Default Lunar admin middleware
    'as' => 'adminhub.geslib.', // Route name prefix
], function () {
    Route::get('/geslib-dashboard', GeslibDashboard::class)->name('dashboard');
    Route::get('/geslib-file-import-log', GeslibFileImportLog::class)->name('file-import-log');
    Route::get('/geslib-order-export-log', GeslibOrderExportLog::class)->name('order-export-log'); // Added route
});
