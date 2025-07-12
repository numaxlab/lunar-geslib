<article>
    <h1 class="at-heading is-1">{{ $sectionCollection->translateAttribute('name') }}</h1>

    <ul class="grid gap-6 grid-cols-2 mb-9 md:grid-cols-4 lg:grid-cols-6">
        @foreach ($sectionCollection->products as $product)
            <li>
                <x-lunar-geslib::product.summary
                        :product="$product"
                        :href="route('lunar.geslib.storefront.products.show', $product->defaultUrl->slug)"
                />
            </li>
        @endforeach
    </ul>
</article>