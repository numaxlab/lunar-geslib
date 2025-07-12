<?php

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire;

use Illuminate\View\View;
use Lunar\Facades\StorefrontSession;
use Lunar\Models\Price;
use Lunar\Models\Product;

class ProductPage extends Page
{
    public Product $product;
    public ?Price $pricing;

    public function mount(string $slug)
    {
        $this->product = Product::channel(StorefrontSession::getChannel())
            ->customerGroup(StorefrontSession::getCustomerGroups())
            ->status('published')
            ->whereHas('productType', function ($query) {
                $query->where('id', config('lunar.geslib.product_type_id'));
            })
            ->whereHas('urls', function ($query) use ($slug) {
                $query->where('slug', $slug);
            })
            ->with(['variant', 'variant.taxClass', 'authors', 'images', 'prices', 'collections'])
            ->firstOrFail();

        $this->pricing = $this->product->variant
            ->pricing()
            ->currency(StorefrontSession::getCurrency())
            ->customerGroups(StorefrontSession::getCustomerGroups())
            ->get()->matched;
    }

    public function render(): View
    {
        return view('lunar-geslib::storefront.livewire.product.show')
            ->title($this->product->recordFullTitle);
    }
}
