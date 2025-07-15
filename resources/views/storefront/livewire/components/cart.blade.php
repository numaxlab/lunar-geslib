<div
        x-data="{
         linesVisible: @entangle('linesVisible').live
     }">
    <button x-on:click="linesVisible = !linesVisible" class="text-primary relative">
        <i class="fa-solid fa-shopping-bag" aria-hidden="true"></i>
        @if ($lines && count($lines) > 0)
            <span class="absolute bottom-0 end-0 inline-flex items-center justify-center w-4 h-4 text-xs font-medium text-white bg-primary rounded-full">
                {{ count($lines) }}
            </span>
        @endif
        <span class="sr-only">Carrito</span>
    </button>

    <div
            class="fixed top-0 end-0 z-50 w-3/4 px-5 py-9 bg-white border border-primary"
            x-show="linesVisible"
            x-on:click.away="linesVisible = false"
            x-transition
            x-cloak>

        <h2 class="at-heading is-3">Tu cesta</h2>

        <button class="absolute text-primary transition-transform top-3 right-3 hover:scale-110"
                type="button"
                aria-label="Close"
                x-on:click="linesVisible = false">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>

        <div class="mt-9">
            @if ($this->cart)
                @if ($lines)
                    <ul class="divide-y divide-black">
                        @foreach ($lines as $index => $line)
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
                                                wire:model.live="lines.{{ $index }}.quantity"
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

                                @if ($errors->get('lines.' . $index . '.quantity'))
                                    <div
                                            class="p-2 mb-4 text-xs font-medium text-center text-red-700 rounded bg-red-50"
                                            role="alert">
                                        @foreach ($errors->get('lines.' . $index . '.quantity') as $error)
                                            {{ $error }}
                                        @endforeach
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="py-4 text-sm">
                        Tu carrito está vacío
                    </p>
                @endif

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
            @else
                <p class="py-4 text-sm">
                    Tu carrito está vacío
                </p>
            @endif
        </div>

        @if ($this->cart)
            <a class="at-button is-primary" href="{{ route('lunar.geslib.storefront.checkout.summary') }}"
               wire:navigate>
                Tramitar pedido
            </a>
        @endif
    </div>
</div>
