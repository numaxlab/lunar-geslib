<article>
    <a href="{{ $attributes->get('href') }}" wire:navigate>
        <img src="{{ $product->getFirstMediaUrl(config('lunar.media.collection'), 'medium') }}" alt=""/>

        <h3 class="at-heading is-4 mt-3">
            {{ $product->recordTitle }}
        </h3>
        @if ($product->translateAttribute('subtitle'))
            <h4 class="at-heading is-5">
                {{ $product->translateAttribute('subtitle') }}
            </h4>
        @endif
    </a>

    @if ($product->authors->isNotEmpty())
        <div class="my-2">
            <ul>
                @foreach ($product->authors as $author)
                    <li>
                        <p>{{ $author->translateAttribute('name') }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <livewire:numax-lab.lunar.geslib.storefront.livewire.components.add-to-cart
            :key="'add-to-cart-' . $product->id"
            :purchasable="$product->variant"
            :display-price="true"/>
</article>
