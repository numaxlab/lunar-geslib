<div>
    @foreach ($featuredCollections as $collection)
        <x-numaxlab-atomic::organisms.tier>
            <x-numaxlab-atomic::organisms.tier.header>
                <h2 class="at-heading is-2">
                    {{ $collection->translateAttribute('name') }}
                </h2>
            </x-numaxlab-atomic::organisms.tier.header>

            <ul class="grid gap-6 grid-cols-2 mb-9 md:grid-cols-4 lg:grid-cols-6">
                @foreach ($collection->products as $product)
                    <li>
                        <x-lunar-geslib::product.summary
                                :product="$product"
                                :href="route('lunar.geslib.storefront.products.show', $product->defaultUrl->slug)"
                        />
                    </li>
                @endforeach
            </ul>
        </x-numaxlab-atomic::organisms.tier>
    @endforeach
</div>
