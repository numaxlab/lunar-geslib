<div class="bg-primary p-4">
    <form wire:submit="search" class="container mx-auto px-4">
        <div class="relative">
            <x-numaxlab-atomic::atoms.forms.input
                    type="search"
                    wire:model.live="query"
                    name="query"
                    placeholder="{{ __('Escribe lo que estás buscando') }}"
                    aria-label="{{ __('Buscar por texto') }}"
                    autocomplete="off"
            />
            <button type="submit" class="text-primary absolute inset-y-0 right-3">
                <i class="fa-solid fa-search" aria-hidden="true"></i>
                <span class="sr-only">{{ __('Buscar') }}</span>
            </button>
        </div>
        <button type="submit" class="at-small text-white">
            {{ __('Busca avanzada') }}
        </button>
    </form>

    @if ($results->isNotEmpty())
        <div class="absolute left-0 w-full h-full bg-white z-10">
            <div class="container mx-auto px-4">
                <ul class="divide-y py-4">
                    @foreach ($results as $result)
                        <li>
                            <article class="ml-summary flex gap-3 mt-6">
                                <a class="summary-media-wrapper w-1/3"
                                   href="{{ route('lunar.geslib.storefront.products.show', $result->defaultUrl->slug) }}">
                                    <img src="{{ $result->getFirstMediaUrl(config('lunar.media.collection'), 'small') }}"
                                         alt=""/>
                                </a>
                                <div class="w-2/3">
                                    <h2 class="at-heading">
                                        <a href="{{ route('lunar.geslib.storefront.products.show', $result->defaultUrl->slug) }}">
                                            {{ $result->recordTitle }}
                                        </a>
                                    </h2>
                                    <div class="summary-content">
                                        @if ($result->translateAttribute('subtitle'))
                                            <h4 class="at-heading is-5">
                                                {{ $result->translateAttribute('subtitle') }}
                                            </h4>
                                        @endif
                                        @if ($result->authors->isNotEmpty())
                                            <div class="my-2">
                                                <ul>
                                                    @foreach ($result->authors as $author)
                                                        <li>
                                                            <p>{{ $author->translateAttribute('name') }}</p>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</div>