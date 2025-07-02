<?php

use Illuminate\Support\Facades\Route;
use NumaxLab\Lunar\Geslib\Storefront\Http\Controllers\Auth\VerifyEmailController;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Account\DashboardPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Account\PasswordPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Account\ProfilePage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Actions\Logout;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Auth\ConfirmPasswordPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Auth\ForgotPasswordPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Auth\LoginPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Auth\RegisterPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Auth\ResetPasswordPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Auth\VerifyEmailPage;
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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardPage::class)->name('dashboard');

    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', ProfilePage::class)->name('settings.profile');
    Route::get('settings/password', PasswordPage::class)->name('settings.password');
});

Route::middleware('guest')->group(function () {
    Route::get('login', LoginPage::class)->name('login');
    Route::get('register', RegisterPage::class)->name('register');
    Route::get('forgot-password', ForgotPasswordPage::class)->name('password.request');
    Route::get('reset-password/{token}', ResetPasswordPage::class)->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', VerifyEmailPage::class)->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::get('confirm-password', ConfirmPasswordPage::class)->name('password.confirm');
});

Route::post('logout', Logout::class)->name('logout');
