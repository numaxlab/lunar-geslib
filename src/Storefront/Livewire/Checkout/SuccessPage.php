<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire\Checkout;

use Illuminate\View\View;
use Lunar\Facades\CartSession;
use Lunar\Models\Order;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Page;

class SuccessPage extends Page
{
    public Order $order;

    public function mount($fingerprint): void
    {
        $this->order = Order::where('fingerprint', $fingerprint)->firstOrFail();

        CartSession::forget();
    }

    public function render(): View
    {
        return view('lunar-geslib::storefront.livewire.checkout.success');
    }
}
