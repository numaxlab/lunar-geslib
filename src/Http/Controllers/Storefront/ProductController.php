<?php

namespace NumaxLab\Lunar\Geslib\Http\Controllers\Storefront;

use Lunar\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::channel($this->session->getChannel())
            ->customerGroup($this->session->getCustomerGroups())
            ->status('published')
            ->whereHas('productType', function ($query) {
                $query->where('id', config('lunar.geslib.product_type_id'));
            })
            ->with(['defaultUrl', 'urls', 'thumbnail', 'prices'])
            ->paginate();

        return view('lunar-geslib::storefront.pages.product.index', compact('products'));
    }

    public function show(string $slug)
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
            ->with(['images', 'prices'])
            ->firstOrFail();

        return view('lunar-geslib::storefront.pages.product.show', compact('product'));
    }
}
