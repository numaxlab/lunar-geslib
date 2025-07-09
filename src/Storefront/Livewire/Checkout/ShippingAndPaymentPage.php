<?php

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire\Checkout;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Lunar\Facades\CartSession;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Contracts\Cart;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Page;

class ShippingAndPaymentPage extends Page
{
    public ?Cart $cart;

    public function getShippingOptionsProperty(): Collection
    {
        return ShippingManifest::getOptions($this->cart);
    }

    public function mount(): void
    {
        $this->cart = CartSession::current();

        if (!$this->cart) {
            $this->redirect('/');

            return;
        }
    }

    public function render(): View
    {
        return view('lunar-geslib::storefront.livewire.checkout.shipping-and-payment');
    }
}
