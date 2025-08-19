<?php

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire\Components;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use Lunar\Facades\CartSession;
use Lunar\Models\Contracts\Cart as CartContract;

/**
 * @property Collection $cartLines
 */
class Cart extends Component
{
    public array $lines;

    public bool $linesVisible = false;

    protected $listeners = [
        'add-to-cart' => 'handleAddToCart',
    ];

    public function rules(): array
    {
        return [
            'lines.*.quantity' => 'required|numeric|min:1|max:100',
        ];
    }

    public function mount(): void
    {
        $this->mapLines();
    }

    private function mapLines(): void
    {
        $this->lines = $this->cartLines->map(function ($line) {
            return [
                'id' => $line->id,
                'slug' => $line->purchasable->product->defaultUrl->slug,
                'quantity' => $line->quantity,
                'description' => $line->purchasable->getDescription(),
                'thumbnail' => $line->purchasable->getThumbnailUrl(),
                'sub_total' => $line->subTotal->formatted(),
                'unit_price' => $line->unitPriceInclTax->formatted(),
            ];
        })->toArray();
    }

    public function getCartProperty(): ?CartContract
    {
        return CartSession::current();
    }

    public function getCartLinesProperty(): Collection
    {
        return $this->cart->lines ?? collect();
    }

    public function updateLines(): void
    {
        $this->validate();

        CartSession::updateLines(collect($this->lines));

        $this->mapLines();

        $this->dispatch('cartUpdated');
    }

    public function removeLine($id): void
    {
        CartSession::remove($id);

        $this->mapLines();
    }

    public function handleAddToCart(): void
    {
        $this->mapLines();
        $this->linesVisible = true;
    }

    public function render(): View
    {
        return view('lunar-geslib::storefront.livewire.components.cart');
    }
}
