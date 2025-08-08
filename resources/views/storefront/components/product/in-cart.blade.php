<article class="flex gap-3 items-center py-2" {{ $attributes->except(['image', 'href', 'price']) }}>
    <i class="fa-solid fa-book-open text-3xl text-primary" aria-hidden="true"></i>

    <h3 class="at-small">
        <a href="{{ $attributes->get('href') }}" wire:navigate>
            {{ $slot }}
        </a>
    </h3>

    @if (isset($quantity))
        <div class="w-16 min-w-16">
            {{ $quantity }}
        </div>
    @endif

    <p class="at-small">
        {{ $attributes->get('price') }}
    </p>

    <div class="ml-4">
        @if (isset($actions))
            {{ $actions }}
        @endif
    </div>
</article>