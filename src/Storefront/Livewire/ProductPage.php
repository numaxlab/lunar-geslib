<?php

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Lunar\Facades\StorefrontSession;
use Lunar\Models\Price;
use Lunar\Models\Product;
use NumaxLab\Lunar\Geslib\Handle;

class ProductPage extends Page
{
    public Product $product;

    public ?Price $pricing;

    public Collection $itineraries;

    public function mount(string $slug): void
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
            ->with(['variant', 'variant.taxClass', 'authors', 'images', 'prices', 'collections', 'collections.group'])
            ->firstOrFail();

        $this->pricing = $this->product->variant
            ->pricing()
            ->currency(StorefrontSession::getCurrency())
            ->customerGroups(StorefrontSession::getCustomerGroups())
            ->get()->matched;

        $this->itineraries = \Lunar\Models\Collection::whereHas('group', function ($query) {
            $query->where('handle', Handle::COLLECTION_GROUP_ITINERARIES);
        })->whereHas('products', function ($query) {
            $query->where(
                $this->product->getTable().'.id',
                $this->product->id,
            );
        })->channel(StorefrontSession::getChannel())
            ->customerGroup(StorefrontSession::getCustomerGroups())
            ->orderBy('_lft', 'ASC')
            ->get();
    }

    public function render(): View
    {
        return view('lunar-geslib::storefront.livewire.product.show')
            ->title($this->product->recordFullTitle);
    }
}
