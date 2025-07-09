<article>
    <h1 class="at-heading is-1">Carrito</h1>

    <ul class="divide-y divide-black">
        @foreach ($lines as $key => $line)
            <li>
                <x-lunar-geslib::product.in-cart
                        :href="route('lunar.geslib.storefront.products.show', $line['slug'])"
                        :image="$line['thumbnail']"
                        :price="$line['unit_price']"
                        wire:key="line_{{ $line['id'] }}"
                >
                    {{ $line['description'] }}

                    <x-slot:quantity>
                        <x-numaxlab-atomic::atoms.forms.input
                                wire:model.live="lines.{{ $key }}.quantity"
                                wire:change="updateLines"
                                type="number"
                                class="text-xs"
                        />
                    </x-slot:quantity>

                    <x-slot:actions>
                        <button
                                class="at-small text-primary"
                                type="button"
                                wire:click="removeLine('{{ $line['id'] }}')">
                            Eliminar
                        </button>
                    </x-slot:actions>
                </x-lunar-geslib::product.in-cart>

                @if ($errors->get('lines.' . $key . '.quantity'))
                    <div
                            class="p-2 mb-4 text-xs font-medium text-center text-red-700 rounded bg-red-50"
                            role="alert">
                        @foreach ($errors->get('lines.' . $key . '.quantity') as $error)
                            {{ $error }}
                        @endforeach
                    </div>
                @endif
            </li>
        @endforeach
    </ul>

    <div class="border-t border-black">
        <h3 class="at-heading is-4 py-2">Resumen</h3>
        <ul class="flex flex-col divide-y divide-black border-t border-b border-black">
            <li class="at-small py-2">
                <i class="fa-solid fa-shopping-bag" aria-hidden="true"></i>
                Subtotal pedido: {{ $this->cart->subTotal->formatted() }}
            </li>
            <li class="at-small py-2">
                <i class="fa-solid fa-info" aria-hidden="true"></i>
                Gastos de envío: {{ $this->cart->shippingTotal?->formatted() }}
            </li>
        </ul>
    </div>

    <a
            class="at-button is-primary mt-4"
            href="{{ route('lunar.geslib.storefront.checkout.delivery-and-payment') }}"
            wire:navigate
    >
        Tramitar pedido
    </a>
</article>