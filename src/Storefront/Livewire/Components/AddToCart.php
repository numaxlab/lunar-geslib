<?php

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire\Components;

use Illuminate\View\View;
use Livewire\Component;
use Lunar\Base\Purchasable;
use Lunar\Facades\CartSession;

class AddToCart extends Component
{
    public ?Purchasable $purchasable = null;

    public function render(): View
    {
        return view('lunar-geslib::storefront.livewire.components.add-to-cart');
    }

    public function addToCart(): void
    {
        CartSession::manager()->add($this->purchasable);

        $this->dispatch('add-to-cart');
    }
}
