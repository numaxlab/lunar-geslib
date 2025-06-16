<x-lunar-geslib::layout.default>
    <ul>
        @foreach ($products as $product)
            <li>
                <a href="{{ route('lunar.geslib.storefront.products.show', $product->defaultUrl->slug) }}">
                    {{ $product->recordTitle }}
                </a>
            </li>
        @endforeach
    </ul>
</x-lunar-geslib::layouts.default>
