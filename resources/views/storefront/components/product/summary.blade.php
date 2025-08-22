<article>
    <a href="{{ $attributes->get('href') }}" wire:navigate class="block mb-2">
        <img
                src="{{ $product->getFirstMediaUrl(config('lunar.media.collection'), 'medium') }}"
                loading="lazy"
                alt=""/>

        <h3 class="at-heading is-4 mt-3">
            {{ $product->recordTitle }}
        </h3>
    </a>

    @if ($product->authors->isNotEmpty())
        <div class="mb-2">
            <ul>
                @foreach ($product->authors as $author)
                    <li>
                        <p>{{ $author->name }}</p>
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
