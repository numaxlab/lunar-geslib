<?php

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire\Components;

use Illuminate\View\View;
use Livewire\Component;
use Lunar\Base\Purchasable;
use Lunar\Facades\CartSession;
use Lunar\Facades\StorefrontSession;
use Lunar\Models\Price;

class AddToCart extends Component
{
    public ?Purchasable $purchasable = null;

    public bool $displayPrice = false;

    public ?Price $pricing;

    public function mount(): void
    {
        if ($this->displayPrice) {
            $this->pricing = $this->purchasable
                ->pricing()
                ->currency(StorefrontSession::getCurrency())
                ->customerGroups(StorefrontSession::getCustomerGroups())
                ->get()->matched;
        }
    }

    public function render(): View
    {
        return view('lunar-geslib::storefront.livewire.components.add-to-cart');
    }

    public function addToCart(): void
    {
        if ($this->purchasable->canBeFulfilledAtQuantity(1) === false) {
            $this->addError('quantity', __('Este artículo no está disponible.'));

            return;
        }

        CartSession::manager()->add($this->purchasable);

        $this->dispatch('add-to-cart');
    }
}
