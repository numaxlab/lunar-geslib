<article>
    <header>
        <nav class="ml-breadcrumb" aria-label="{{ __('Miga de pan') }}">
            <ol>
                <li><a href="{{ route('lunar.geslib.storefront.homepage') }}">{{ __('Librería') }}</a></li>
                <li><a href="{{ route('lunar.geslib.storefront.itineraries.index') }}">Itinerarios</a></li>
            </ol>
        </nav>

        <h1 class="at-heading is-1">{{ $itineraryCollection->translateAttribute('name') }}</h1>

        @if ($itineraryCollection->translateAttribute('subtitle'))
            <h2 class="at-heading is-3 font-normal">{{ $itineraryCollection->translateAttribute('subtitle') }}</h2>
        @endif

        @if ($itineraryCollection->hasMedia(config('lunar.media.collection')))
            <img src="{{ $itineraryCollection->getFirstMediaUrl(config('lunar.media.collection'), 'large') }}" alt=""
                 class="mt-5">
        @endif

        @if ($itineraryCollection->translateAttribute('description'))
            <div class="mt-5">
                {!! $itineraryCollection->translateAttribute('description') !!}
            </div>
        @endif
    </header>

    <x-numaxlab-atomic::organisms.tier class="mt-9">
        <x-numaxlab-atomic::organisms.tier.header>
            <h2 class="at-heading is-2">
                Libros
            </h2>
        </x-numaxlab-atomic::organisms.tier.header>

        <ul class="grid gap-6 grid-cols-2 mb-9 md:grid-cols-4 lg:grid-cols-6">
            @foreach ($itineraryCollection->products as $product)
                <li>
                    <x-lunar-geslib::product.summary
                            :product="$product"
                            :href="route('lunar.geslib.storefront.products.show', $product->defaultUrl->slug)"
                    />
                </li>
            @endforeach
        </ul>
    </x-numaxlab-atomic::organisms.tier>
</article>