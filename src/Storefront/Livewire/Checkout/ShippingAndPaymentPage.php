<?php

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire\Checkout;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Lunar\Facades\CartSession;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\CartAddress;
use Lunar\Models\Contracts\Cart;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Page;

class ShippingAndPaymentPage extends Page
{
    public ?Cart $cart;

    public ?CartAddress $shipping = null;

    public ?CartAddress $billing = null;

    public bool $shippingIsBilling = true;

    public $chosenShipping = null;

    public $payment_intent = null;

    public $payment_intent_client_secret = null;

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

        $this->shipping = $this->cart->shippingAddress ?: new CartAddress;

        $this->billing = $this->cart->billingAddress ?: new CartAddress;
    }

    public function hydrate(): void
    {
        $this->cart = CartSession::current();
    }

    public function render(): View
    {
        return view('lunar-geslib::storefront.livewire.checkout.shipping-and-payment');
    }
}
