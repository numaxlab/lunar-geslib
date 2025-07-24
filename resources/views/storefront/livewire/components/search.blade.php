<div class="bg-primary p-4">
    <form wire:submit="search" class="container mx-auto px-4" x-data="{ advancedSearch: false }">
        <div class="relative">
            <x-numaxlab-atomic::atoms.forms.input
                    type="search"
                    wire:model.live="query"
                    name="query"
                    placeholder="{{ __('Escribe lo que estás buscando') }}"
                    aria-label="{{ __('Buscar en la librería') }}"
                    autocomplete="off"
            />
            <button type="submit" class="text-primary absolute inset-y-0 right-3">
                <i class="fa-solid fa-search" aria-hidden="true"></i>
                <span class="sr-only">{{ __('Buscar') }}</span>
            </button>

            @if ($results->isNotEmpty())
                <ul class="divide-y py-2 px-4 bg-white absolute w-full border border-primary">
                    @foreach ($results as $result)
                        <li>
                            <article class="ml-summary my-2">
                                <a href="{{ route('lunar.geslib.storefront.products.show', $result->defaultUrl->slug) }}">
                                    <h2 class="at-heading">
                                        {{ $result->recordTitle }}
                                    </h2>
                                    @if ($result->translateAttribute('subtitle'))
                                        <h3 class="font-normal">
                                            {{ $result->translateAttribute('subtitle') }}
                                        </h3>
                                    @endif
                                </a>
                                <div class="summary-content">
                                    @if ($result->authors->isNotEmpty())
                                        <div class="mt-2">
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
                            </article>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
        <button type="button" class="at-small text-white" x-show="!advancedSearch" @click="advancedSearch = true">
            {{ __('Busca avanzada') }}
        </button>

        <fieldset class="flex flex-col gap-3 md:flex-row mt-3" x-show="advancedSearch">
            <div class="md:w-4/12 relative">
                <x-numaxlab-atomic::atoms.forms.input
                        type="search"
                        wire:model.live="taxonQuery"
                        name="taxonQuery"
                        placeholder="{{ __('Secciones o materias') }}"
                        aria-label="{{ __('Buscar por sección o materia') }}"
                        autocomplete="off"
                />

                <x-numaxlab-atomic::atoms.forms.input
                        type="hidden"
                        wire:model.live="taxonId"
                        name="taxonId"
                />

                <x-numaxlab-atomic::atoms.forms.input
                        type="hidden"
                        wire:model.live="taxonType"
                        name="taxonType"
                />

                @if ($taxonomies->isNotEmpty())
                    <ul class="divide-y py-2 px-4 bg-white absolute w-full border border-primary">
                        @foreach ($taxonomies as $taxonomy)
                            <li>
                                {{ $taxonomy->translateAttribute('name') }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="md:w-3/12">
                <x-numaxlab-atomic::atoms.forms.select
                        wire:model.live="languageId"
                        name="language"
                        id="language"
                        aria-label="{{ __('Filtrar por idioma') }}"
                >
                    <option value="">Todos los idiomas</option>
                    @foreach($languages as $language)
                        <option value="{{ $language->id }}">
                            {{ $language->translateAttribute('name') }}
                        </option>
                    @endforeach
                </x-numaxlab-atomic::atoms.forms.select>
            </div>

            <div class="md:w-2/12">
                <x-numaxlab-atomic::atoms.forms.select
                        wire:model.live="priceRange"
                        name="priceRange"
                        id="priceRange"
                        aria-label="{{ __('Filtrar por precio') }}"
                >
                    <option value="">Todos los precios</option>
                    @foreach($this->priceRanges as $priceRange)
                        <option value="{{ $priceRange }}">
                            {{ $priceRange }}
                        </option>
                    @endforeach
                </x-numaxlab-atomic::atoms.forms.select>
            </div>

            <div class="md:w-3/12">
                <x-numaxlab-atomic::atoms.forms.select
                        wire:model.live="availabilityId"
                        name="availability"
                        id="availability"
                        aria-label="{{ __('Filtrar por disponibilidad') }}"
                >
                    <option value="">Todos las disponibilidades</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}">
                            {{ $status->translateAttribute('name') }}
                        </option>
                    @endforeach
                </x-numaxlab-atomic::atoms.forms.select>
            </div>
        </fieldset>
    </form>
</div>