<article>
    <header>
        <nav class="ml-breadcrumb" aria-label="{{ __('Miga de pan') }}">
            <ol>
                <li><a href="{{ route('lunar.geslib.storefront.homepage') }}">{{ __('Librería') }}</a></li>
            </ol>
        </nav>

        <h1 class="at-heading is-1">{{ $sectionCollection->translateAttribute('name') }}</h1>

        <form class="my-6 flex flex-col gap-3 md:flex-row md:gap-6">
            <div class="relative w-1/2">
                <x-numaxlab-atomic::atoms.forms.input
                        type="search"
                        name="q"
                        placeholder="Buscar"
                        aria-label="Buscar por texto"
                        autocomplete="off"
                />
                <button type="submit" aria-label="Buscar" class="text-primary absolute inset-y-0 right-3">
                    <i class="fa-solid fa-search" aria-hidden="true"></i>
                </button>
            </div>

            <div class="w-1/2">
                <x-numaxlab-atomic::atoms.forms.select
                        name="taxon"
                        id="taxon"
                        aria-label="{{ __('Filtrar por taxonomía') }}"
                >
                    <option value="">Todas las taxonomías</option>
                </x-numaxlab-atomic::atoms.forms.select>
            </div>
        </form>
    </header>

    @if ($sectionCollection->products->isNotEmpty())
        <ul class="grid gap-6 grid-cols-2 mb-9 md:grid-cols-4 lg:grid-cols-6">
            @foreach ($sectionCollection->products as $product)
                <li>
                    <x-lunar-geslib::product.summary
                            :product="$product"
                            :href="route('lunar.geslib.storefront.products.show', $product->defaultUrl->slug)"
                    />
                </li>
            @endforeach
        </ul>
    @else
        <p>Esta sección no tiene artículos.</p>
    @endif
</article>