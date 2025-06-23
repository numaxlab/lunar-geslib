<?php

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire;

use Illuminate\View\View;
use Livewire\WithPagination;
use Lunar\Facades\StorefrontSession;
use Lunar\Models\Product;

class ProductListPage extends Page
{
    use WithPagination;

    public function render(): View
    {
        $products = Product::channel(StorefrontSession::getChannel())
            ->customerGroup(StorefrontSession::getCustomerGroups())
            ->status('published')
            ->whereHas('productType', function ($query) {
                $query->where('id', config('lunar.geslib.product_type_id'));
            })
            ->with(['variant', 'variant.taxClass', 'defaultUrl', 'urls', 'thumbnail', 'authors', 'prices'])
            ->paginate(24);

        return view('lunar-geslib::storefront.livewire.product.index', [
            'products' => $products,
        ]);
    }
}
