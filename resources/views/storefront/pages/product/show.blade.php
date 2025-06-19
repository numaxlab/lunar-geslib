<x-lunar-geslib::layout.default>
    <x-slot name="metaTitle">{{ $product->recordFullTitle }}</x-slot>

    <article>
        <header>
            <h1 class="at-heading is-2">{{ $product->recordTitle }}</h1>
            @if ($product->translateAttribute('subtitle'))
                <h2 class="at-heading is-3">{{ $product->translateAttribute('subtitle') }}</h2>
            @endif
            @if ($product->authors->isNotEmpty())
                <ul>
                    @foreach ($product->authors as $author)
                        <li><a href="">{{ $author->translateAttribute('name') }}</a></li>
                    @endforeach
                </ul>
            @endif

            <img src="https://picsum.photos/600/800" alt="">

            @if ($pricing)
                {{ $pricing->price->formatted() }}
            @endif
        </header>
    </article>
</x-lunar-geslib::layout.default>
