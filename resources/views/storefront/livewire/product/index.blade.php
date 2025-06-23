<div>
    <ul class="grid gap-6 grid-cols-2 mb-9 md:grid-cols-4 lg:grid-cols-6">
        @foreach ($products as $product)
            <li>
                <x-lunar-geslib::product.summary
                    href="{{ route('lunar.geslib.storefront.products.show', $product->defaultUrl->slug) }}"
                    image="{{ $product->thumbnail?->getUrl('medium') }}"
                >
                    {{ $product->recordTitle }}

                    @if ($product->translateAttribute('subtitle'))
                        <x-slot:subtitle>
                            {{ $product->translateAttribute('subtitle') }}
                        </x-slot:subtitle>
                    @endif

                    @if ($product->authors->isNotEmpty())
                        <x-slot:authors>
                            <ul>
                                @foreach ($product->authors as $author)
                                    <li>
                                        <p>{{ $author->translateAttribute('name') }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        </x-slot:authors>
                    @endif
                </x-lunar-geslib::product.summary>
            </li>
        @endforeach
    </ul>

    {{ $products->links() }}
</div>
