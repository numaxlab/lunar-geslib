<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire\Checkout;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Lunar\Facades\CartSession;
use Lunar\Models\Contracts\Cart;
use NumaxLab\Lunar\Geslib\Storefront\Livewire\Page;

/**
 * @property Collection $cartLines
 */
class SummaryPage extends Page
{
    public ?Cart $cart = null;

    public array $lines;

    public function getCartLinesProperty(): Collection
    {
        return $this->cart->lines ?? collect();
    }

    public function rules(): array
    {
        return [
            'lines.*.quantity' => 'required|numeric|min:1|max:100',
        ];
    }

    public function mount(): void
    {
        $this->cart = CartSession::current();

        if (!$this->cart) {
            $this->redirect('/');

            return;
        }

        $this->mapLines();
    }

    private function mapLines(): void
    {
        $this->lines = $this->cartLines->map(fn($line): array
            => [
            'id' => $line->id,
            'slug' => $line->purchasable->product->defaultUrl->slug,
            'quantity' => $line->quantity,
            'description' => $line->purchasable->getDescription(),
            'thumbnail' => $line->purchasable->getThumbnailUrl(),
            'sub_total' => $line->subTotal->formatted(),
            'unit_price' => $line->unitPriceInclTax->formatted(),
        ])->toArray();
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

    public function render(): View
    {
        return view('lunar-geslib::storefront.livewire.checkout.summary');
    }
}
