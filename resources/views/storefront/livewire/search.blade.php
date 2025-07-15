<article>
    <h1 class="at-heading is-1">
        Buscador
    </h1>

    <form wire:submit="search" class="mt-6 flex flex-col gap-6">
        <div class="relative">
            <x-numaxlab-atomic::atoms.forms.input
                    type="search"
                    wire:model.live="q"
                    name="q"
                    placeholder="Buscar"
                    aria-label="Buscar por texto"
                    autocomplete="off"
            />
            <button type="submit" aria-label="Buscar" class="text-primary absolute inset-y-0 right-3">
                <i class="fa-solid fa-search" aria-hidden="true"></i>
            </button>
        </div>

        <x-numaxlab-atomic::atoms.forms.select
                name="taxon"
                id="taxon"
                aria-label="{{ __('Filtrar por taxonomía') }}"
        >
            <option value="">Todas las taxonomías</option>
        </x-numaxlab-atomic::atoms.forms.select>
    </form>

    @if ($results->isEmpty())
        <p class="mt-10">No hay resultados para tu búsqueda.</p>
    @else
        <ul class="mt-10 grid gap-6 grid-cols-2 mb-9 md:grid-cols-4 lg:grid-cols-6">
            @foreach ($results as $product)
                <li wire:key="result-{{ $product->id }}">
                    <x-lunar-geslib::product.summary
                            :product="$product"
                            :href="route('lunar.geslib.storefront.products.show', $product->defaultUrl->slug)"
                    />
                </li>
            @endforeach
        </ul>
    @endif
</article>