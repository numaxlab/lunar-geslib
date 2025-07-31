<article class="lg:flex lg:flex-wrap lg:gap-10">
    <header class="lg:w-8/12">
        <nav class="ml-breadcrumb" aria-label="{{ __('Miga de pan') }}">
            <ol>
                <li><a href="{{ route('lunar.geslib.storefront.homepage') }}">{{ __('Librería') }}</a></li>
            </ol>
        </nav>

        <h1 class="at-heading is-1">{{ $product->recordTitle }}</h1>

        @if ($product->translateAttribute('subtitle'))
            <h2 class="at-heading is-3">{{ $product->translateAttribute('subtitle') }}</h2>
        @endif

        @if ($product->authors->isNotEmpty())
            <ul class="at-heading is-3 font-normal mt-3">
                @foreach ($product->authors as $author)
                    <li>
                        <a href="{{ route('lunar.geslib.storefront.search', ['q' => $author->translateAttribute('name')]) }}">
                            {{ $author->translateAttribute('name') }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="hidden lg:block mt-10">
            @include('lunar-geslib::storefront.partials.product.body')
        </div>
    </header>

    <div class="bg-white lg:-order-1 lg:w-3/12 lg:sticky lg:top-10">
        <img
                src="{{ $product->getFirstMediaUrl(config('lunar.media.collection'), 'large') }}"
                alt="{{ __('Portada del libro :title', ['title' => $product->recordFullTitle]) }}"
                class="w-full h-auto mt-7"
        >

        @if ($pricing)
            <div class="mt-5 mb-3 text-xl">
                {{ $pricing->priceIncTax()->formatted() }}
            </div>
        @endif

        <livewire:numax-lab.lunar.geslib.storefront.livewire.components.add-to-cart
                :key="'add-to-cart-' . $product->id"
                :purchasable="$product->variant"/>

        <a href="#" class="at-button border-primary text-primary mt-3">
            Descarga este libro
        </a>

        <a href="#" class="at-button border-primary text-primary mt-3">
            Haz una donación
        </a>
    </div>

    <div class="mt-10 lg:w-8/12 lg:ml-[25%] lg:pl-10">
        <div class="lg:hidden">
            @include('lunar-geslib::storefront.partials.product.body')
        </div>

        <x-numaxlab-atomic::organisms.tier class="mt-10">
            <x-numaxlab-atomic::organisms.tier.header>
                <h2 class="at-heading is-2">
                    {{ __('Reseñas') }}
                </h2>
            </x-numaxlab-atomic::organisms.tier.header>

            <ul class="flex flex-col gap-4 md:flex-row md:gap-6">
                @for($i=0; $i<2; $i++)
                    <li class="pr-10">
                        <x-lunar-geslib::review.summary/>
                    </li>
                @endfor
            </ul>
        </x-numaxlab-atomic::organisms.tier>

        @if ($product->associations->isNotEmpty())
            <x-numaxlab-atomic::organisms.tier class="mt-10">
                <x-numaxlab-atomic::organisms.tier.header>
                    <h2 class="at-heading is-2">
                        {{ __('Relacionados') }}
                    </h2>
                </x-numaxlab-atomic::organisms.tier.header>

                <ul class="grid gap-6 grid-cols-2 mb-9 md:grid-cols-4">
                    @foreach ($product->associations as $association)
                        <li>
                            <x-lunar-geslib::product.summary
                                    :product="$association->target"
                                    :href="route('lunar.geslib.storefront.products.show', $association->target->defaultUrl->slug)"
                            />
                        </li>
                    @endforeach
                </ul>
            </x-numaxlab-atomic::organisms.tier>
        @endif

        @if ($itineraries->isNotEmpty())
            <x-numaxlab-atomic::organisms.tier class="mt-10">
                <x-numaxlab-atomic::organisms.tier.header>
                    <h2 class="at-heading is-2">
                        {{ __('Itinerarios') }}
                    </h2>
                </x-numaxlab-atomic::organisms.tier.header>

                <ul class="grid gap-6 md:grid-cols-2">
                    @foreach($itineraries as $collection)
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
</article>
