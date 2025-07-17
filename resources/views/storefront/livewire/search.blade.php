<article>
    <h1 class="at-heading is-1">
        Resultados para "{{ $q }}"
    </h1>

    @if ($results->isEmpty())
        <p class="mt-10">No hay resultados para tu búsqueda.</p>
    @else
        <ul class="mt-10 grid gap-6 grid-cols-2 mb-9 md:grid-cols-4 lg:grid-cols-6">
            @foreach ($results as $product)
                <li wire:key="result-{{ $product->id }}">
                    <x-lunar-geslib::product.summary
                            :product="$product"
                            :href="route('lunar.geslib.storefront.products.show', $product->defaultUrl->slug)"
                    />
                </li>
            @endforeach
        </ul>
    @endif
</article>
