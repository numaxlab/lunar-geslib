<?php

namespace NumaxLab\Lunar\Geslib\Http\Controllers\Storefront;

use Illuminate\Contracts\View\View;
use Lunar\Models\Product;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::channel($this->session->getChannel())
            ->customerGroup($this->session->getCustomerGroups())
            ->status('published')
            ->whereHas('productType', function ($query) {
                $query->where('id', config('lunar.geslib.product_type_id'));
            })
            ->with(['variant', 'variant.taxClass', 'defaultUrl', 'urls', 'thumbnail', 'authors', 'prices'])
            ->paginate(24);

        return view('lunar-geslib::storefront.pages.product.index', compact('products'));
    }

    public function show(string $slug): View
    {
        $product = Product::channel($this->session->getChannel())
            ->customerGroup($this->session->getCustomerGroups())
            ->status('published')
            ->whereHas('productType', function ($query) {
                $query->where('id', config('lunar.geslib.product_type_id'));
            })
            ->whereHas('urls', function ($query) use ($slug) {
                $query->where('slug', $slug);
            })
            ->with(['variant', 'variant.taxClass', 'authors', 'images', 'prices'])
            ->firstOrFail();

        $pricing = $product->variant
            ->pricing()
            ->currency($this->session->getCurrency())
            ->customerGroups($this->session->getCustomerGroups())
            ->get()->matched;

        $this->taxes->setPurchasable($product->variant);

        return view('lunar-geslib::storefront.pages.product.show', compact('product', 'pricing'));
    }
}
