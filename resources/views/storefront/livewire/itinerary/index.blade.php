<article>
    <header>
        <nav class="ml-breadcrumb" aria-label="{{ __('Miga de pan') }}">
            <ol>
                <li><a href="{{ route('lunar.geslib.storefront.homepage') }}">{{ __('Librería') }}</a></li>
            </ol>
        </nav>

        <h1 class="at-heading is-1">{{ __('Itinerarios') }}</h1>
    </header>

    @if ($itinerariesCollections->isNotEmpty())
        <ul class="mt-7 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
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
    @endif
</article>
