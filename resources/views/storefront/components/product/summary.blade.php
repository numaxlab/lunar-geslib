<article>
    <a href="{{ $attributes->get('href') }}">
        <img src="https://picsum.photos/600/800" alt="">

        <h3 class="at-heading is-4 mt-3">
            {{ $slot }}
        </h3>
        @if (isset($subtitle))
            <h4 class="at-heading is-5">
                {{ $subtitle }}
            </h4>
        @endif
    </a>

    @if (isset($authors))
        <div class="mt-2">
            {{ $authors }}
        </div>
    @endif

    <button class="at-button mt-2">
        <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
        Comprar
    </button>
</article>
