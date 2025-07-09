<article class="flex items-start py-4" {{ $attributes->except(['image', 'href', 'price']) }}>
    @if($attributes->get('image'))
        <img class="object-cover w-16 h-auto" src="{{ $attributes->get('image') }}" alt="">
    @endif

    <div class="flex-1 ml-4">
        <h3 class="at-heading is-4">
            <a href="{{ $attributes->get('href') }}" wire:navigate>
                {{ $slot }}
            </a>
        </h3>

        <div class="flex items-center mt-2">
            @if (isset($quantity))
                <div class="w-16">
                    {{ $quantity }}
                </div>
            @endif

            <p class="ml-2 text-xs">
                {{ $attributes->get('price') }}
            </p>
        </div>

        @if (isset($actions))
            {{ $actions }}
        @endif
    </div>
</article>