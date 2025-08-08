<div class="lg:flex lg:gap-6">
    <div class="lg:w-2/3">
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

    <div class="border-t-1 mt-10 pt-3 lg:w-1/3 lg:mt-0">
        @if ($product->collections->isNotEmpty())
            <ul class="flex flex-wrap gap-2">
                @foreach ($product->collections as $collection)
                    @if (!in_array($collection->group->handle, ['product-types', 'itineraries']))
                        <li>
                            <a href="{{ $collection->url }}" class="at-small at-tag is-primary">
                                {{ $collection->translateAttribute('name') }}
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        @endif

        <dl class="at-description-list is-grid text-sm mt-5">
            @if ($product->translators->isNotEmpty())
                <dt>{{ __('Traducción') }}</dt>
                <dd>
                    @foreach ($product->translators as $author)
                        <a href="{{ route('trafikrak.storefront.bookshop.search', ['q' => $author->name]) }}">{{ $author->name }}</a>{{ $loop->last ? '' : ', ' }}
                    @endforeach
                </dd>
            @endif

            @if ($product->illustrators->isNotEmpty())
                <dt>{{ __('Ilustración') }}</dt>
                <dd>
                    @foreach ($product->illustrators as $author)
                        <a href="{{ route('trafikrak.storefront.bookshop.search', ['q' => $author->name]) }}">{{ $author->name }}</a>{{ $loop->last ? '' : ', ' }}
                    @endforeach
                </dd>
            @endif

            @if ($product->coverIllustrators->isNotEmpty())
                <dt>{{ __('Ilustración de portada') }}</dt>
                <dd>
                    @foreach ($product->coverIllustrators as $author)
                        <a href="{{ route('trafikrak.storefront.bookshop.search', ['q' => $author->name]) }}">{{ $author->name }}</a>{{ $loop->last ? '' : ', ' }}
                    @endforeach
                </dd>
            @endif

            @if ($product->backCoverIllustrators->isNotEmpty())
                <dt>{{ __('Ilustración de contraportada') }}</dt>
                <dd>
                    @foreach ($product->backCoverIllustrators as $author)
                        <a href="{{ route('trafikrak.storefront.bookshop.search', ['q' => $author->name]) }}">{{ $author->name }}</a>{{ $loop->last ? '' : ', ' }}
                    @endforeach
                </dd>
            @endif

            @foreach ($product->mappedAttributes() as $attribute)
                @if (! in_array($attribute->handle, ['name', 'subtitle', 'bookshop-reference', 'editorial-reference', 'index']))
                    @if ($product->translateAttribute($attribute->handle))
                        <dt>{{ $attribute->translate('name') }}</dt>
                        <dd>
                            {!! $product->translateAttribute($attribute->handle) !!}
                        </dd>
                    @endif
                @endif
            @endforeach

            @if ($product->variant->gtin)
                <dt class="mt-4">ISBN</dt>
                <dd class="mt-4">{{ $product->variant->gtin }}</dd>
            @endif
            @if ($product->variant->ean)
                <dt>EAN</dt>
                <dd>{{ $product->variant->ean }}</dd>
            @endif
        </dl>
    </div>
</div>