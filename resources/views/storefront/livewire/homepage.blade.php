<div>
    <h1 class="at-heading is-1 mb-10">Librería</h1>

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

    @foreach($sectionsCollections as $collection)
        <x-numaxlab-atomic::organisms.tier>
            <x-numaxlab-atomic::organisms.tier.header>
                <h2 class="at-heading is-2">
                    {{ $collection->translateAttribute('name') }}
                </h2>

                <a href="{{ route('lunar.geslib.storefront.sections.show', $collection->defaultUrl->slug) }}"
                   wire:navigate
                   class="at-small"
                >
                    Ver máis
                </a>
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

    @if ($itinerariesCollections->isNotEmpty())
        <x-numaxlab-atomic::organisms.tier>
            <x-numaxlab-atomic::organisms.tier.header>
                <h2 class="at-heading is-2">
                    Itinerarios
                </h2>

                <a {{ route('lunar.geslib.storefront.itineraries.index') }}
                   wire:navigate
                   class="at-small"
                >
                    Ver máis
                </a>
            </x-numaxlab-atomic::organisms.tier.header>

            <ul>
                @foreach($itinerariesCollections as $collection)
                    <li>
                        <x-numaxlab-atomic::molecules.banner
                                :href="route('lunar.geslib.storefront.itineraries.show', $collection->defaultUrl->slug)">
                            <h2 class="at-heading is-3 mb-4">{{ $collection->translateAttribute('name') }}</h2>

                            @if ($collection->translateAttribute('description'))
                                <x-slot:content>
                                    {!! $collection->translateAttribute('description') !!}
                                </x-slot:content>
                            @endif
                        </x-numaxlab-atomic::molecules.banner>
                    </li>
                @endforeach
            </ul>
        </x-numaxlab-atomic::organisms.tier>
    @endif
</div>
