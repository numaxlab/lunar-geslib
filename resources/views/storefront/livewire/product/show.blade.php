<article>
    <header>
        <h1 class="at-heading is-1">{{ $product->recordTitle }}</h1>

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

        <img src="{{ $product->getFirstMediaUrl(config('lunar.media.collection'), 'large') }}" alt="">

        @if ($pricing)
            <div class="mt-5 text-xl">
                {{ $pricing->priceIncTax()->formatted() }}
            </div>
        @endif

        @livewire('numax-lab.lunar.geslib.storefront.livewire.components.add-to-cart', [
            'purchasable' => $product->variant
        ])
    </header>
    <div>
        @if ($product->translateAttribute('bookshop-reference'))
            <div>
                {!! $product->translateAttribute('bookshop-reference') !!}
            </div>
        @elseif ($product->translateAttribute('editorial-reference'))
            <div>
                {!! $product->translateAttribute('editorial-reference') !!}
            </div>
        @endif
    </div>
    <div>
        @if ($product->collections->isNotEmpty())
            <ul class="flex flex-wrap gap-2">
                @foreach ($product->collections as $collection)
                    <li>
                        <a href="{{ $collection->url }}" class="at-tag is-primary">
                            {{ $collection->translateAttribute('name') }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        <dl class="ml-tech-info mt-5">
            @if ($product->translateAttribute('issue-date'))
                <dt>Fecha de edición</dt>
                <dd>
                    {!! $product->translateAttribute('issue-date') !!}
                </dd>
            @endif
            @if ($product->translateAttribute('pages'))
                <dt>Páginas</dt>
                <dd>
                    {!! $product->translateAttribute('pages') !!}
                </dd>
            @endif

            @if ($product->variant->gtin)
                <dt class="mt-2">ISBN</dt>
                <dd>{{ $product->variant->gtin }}</dd>
            @endif
            @if ($product->variant->ean)
                <dt>EAN</dt>
                <dd>{{ $product->variant->ean }}</dd>
            @endif
        </dl>
    </div>
</article>
