<article class="flex gap-2">
    <a href="{{ $attributes->get('href') }}" wire:navigate class="block w-1/3">
        <img
                src="{{ $product->getFirstMediaUrl(config('lunar.media.collection'), 'medium') }}"
                loading="lazy"
                alt=""/>
    </a>

    <div class="w-2/3">
        <h3 class="at-heading is-4">
            <a href="{{ $attributes->get('href') }}" wire:navigate>
                {{ $product->recordTitle }}
            </a>
        </h3>

        @if ($product->authors->isNotEmpty())
            <ul>
                @foreach ($product->authors as $author)
                    <li>
                        <p>{{ $author->name }}</p>
                    </li>
                @endforeach
            </ul>
        @endif

        @if (isset($actions))
            <div class="mt-3">
                {{ $actions }}
            </div>
        @endif
    </div>
</article>
