<?php

use Illuminate\Support\Facades\Route;
use NumaxLab\Lunar\Geslib\Storefront\Http\Controllers\Auth\VerifyEmailController;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Account\DashboardPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Account\HandleAddressPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Account\PasswordPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Account\ProfilePage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Actions\Logout;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Auth\ConfirmPasswordPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Auth\ForgotPasswordPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Auth\LoginPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Auth\RegisterPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Auth\ResetPasswordPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Auth\VerifyEmailPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Checkout\ShippingAndPaymentPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Checkout\SuccessPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Checkout\SummaryPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\HomePage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\ItinerariesListPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\ItineraryPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\ProductPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\SearchPage;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\SectionPage;

Route::prefix('/libreria')->group(function () {
    Route::get('/', HomePage::class)
        ->name('lunar.geslib.storefront.homepage');

    Route::get('/productos/{slug}', ProductPage::class)
        ->name('lunar.geslib.storefront.products.show');

    Route::get('/itinerarios', ItinerariesListPage::class)
        ->name('lunar.geslib.storefront.itineraries.index');

    Route::get('/itinerarios/{slug}', ItineraryPage::class)
        ->name('lunar.geslib.storefront.itineraries.show');

    Route::get('/secciones/{slug}', SectionPage::class)
        ->name('lunar.geslib.storefront.sections.show');

    Route::get('/buscar', SearchPage::class)
        ->name('lunar.geslib.storefront.search');
});

Route::prefix('/checkout')->group(function () {
    Route::get('/', SummaryPage::class)
        ->name('lunar.geslib.storefront.checkout.summary');

    Route::get('/envio-y-pago', ShippingAndPaymentPage::class)
        ->name('lunar.geslib.storefront.checkout.shipping-and-payment');

    Route::get('/finalizado/{fingerprint}', SuccessPage::class)
        ->name('lunar.geslib.storefront.checkout.success');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardPage::class)->name('dashboard');

    Route::redirect('preferencias', 'preferencias/perfil');

    Route::get('preferencias/perfil', ProfilePage::class)->name('settings.profile');
    Route::get('preferencias/contrasenha', PasswordPage::class)->name('settings.password');
    Route::get('preferencias/direcciones', HandleAddressPage::class)->name('settings.add-address');
    Route::get('preferencias/direcciones/{id}/editar', HandleAddressPage::class)->name('settings.edit-address');
});

Route::middleware('guest')->group(function () {
    Route::get('login', LoginPage::class)->name('login');
    Route::get('registrate', RegisterPage::class)->name('register');
    Route::get('recuperar-contrasenha', ForgotPasswordPage::class)->name('password.request');
    Route::get('recuperar-ctonrasenha/{token}', ResetPasswordPage::class)->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', VerifyEmailPage::class)->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::get('confirm-password', ConfirmPasswordPage::class)->name('password.confirm');
});

Route::post('logout', Logout::class)->name('logout');
