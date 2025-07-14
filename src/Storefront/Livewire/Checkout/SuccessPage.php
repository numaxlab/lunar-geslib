<?php

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire\Checkout;

use Illuminate\View\View;
use Lunar\Facades\CartSession;
use Lunar\Models\Cart;
use Lunar\Models\Order;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Page;

class SuccessPage extends Page
{
    public ?Cart $cart;

    public Order $order;

    public function mount(): void
    {
        $this->cart = CartSession::current();

        dd($this->cart);

        if (!$this->cart || !$this->cart->completedOrder) {
            $this->redirect('/');

            return;
        }

        $this->order = $this->cart->completedOrder;

        CartSession::forget();
    }

    public function render(): View
    {
        return view('lunar-geslib::storefront.livewire.checkout.success');
    }
}