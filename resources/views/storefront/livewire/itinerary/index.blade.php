<article>
    <h1 class="at-heading is-1">Itinerarios</h1>

    @if ($itinerariesCollections->isNotEmpty())
        <ul class="mt-7">
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
