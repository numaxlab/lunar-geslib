<div class="sm:relative"
     x-data="{
         linesVisible: @entangle('linesVisible').live
     }">
    <button x-on:click="linesVisible = !linesVisible">
        <i class="fa-solid fa-shopping-bag" aria-hidden="true"></i>
        <span class="sr-only">Carrito</span>
    </button>

    <div
        class="absolute inset-x-0 top-auto z-50 w-screen max-w-sm px-6 py-8 mx-auto mt-4 bg-white border border-gray-100 shadow-xl sm:left-auto"
        x-show="linesVisible"
        x-on:click.away="linesVisible = false"
        x-transition
        x-cloak>
        <button class="absolute text-gray-500 transition-transform top-3 right-3 hover:scale-110"
                type="button"
                aria-label="Close"
                x-on:click="linesVisible = false">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-4 h-4"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <div>
            @if ($this->cart)
                @if ($lines)
                    <div class="flow-root">
                        <ul class="-my-4 overflow-y-auto divide-y divide-gray-100 max-h-96">
                            @foreach ($lines as $index => $line)
                                <li>
                                    <div class="flex py-4"
                                         wire:key="line_{{ $line['id'] }}">
                                        @if($line['thumbnail'])
                                            <img class="object-cover w-16 h-16 rounded" src="{{ $line['thumbnail'] }}"
                                                 alt="">
                                        @endif

                                        <div class="flex-1 ml-4">
                                            <p class="max-w-[20ch] text-sm font-medium">
                                                {{ $line['description'] }}
                                            </p>

                                            <div class="flex items-center mt-2">
                                                <input
                                                    class="w-16 p-2 text-xs transition-colors border border-gray-100 rounded-lg hover:border-gray-200"
                                                    type="number"
                                                    wire:model.live="lines.{{ $index }}.quantity"/>

                                                <p class="ml-2 text-xs">
                                                    {{ $line['unit_price'] }}
                                                </p>

                                                <button
                                                    class="p-2 ml-auto text-gray-600 transition-colors rounded-lg hover:bg-gray-100 hover:text-gray-700"
                                                    type="button"
                                                    wire:click="removeLine('{{ $line['id'] }}')">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         class="w-4 h-4"
                                                         fill="none"
                                                         viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

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
                    </div>
                @else
                    <p class="py-4 text-sm font-medium text-center text-gray-500">
                        Tu carrito está vacío
                    </p>
                @endif

                <dl class="flex flex-wrap pt-4 mt-6 text-sm border-t border-gray-100">
                    <dt class="w-1/2 font-medium">
                        Subtotal
                    </dt>

                    <dd class="w-1/2 text-right">
                        {{ $this->cart->subTotal->formatted() }}
                    </dd>
                </dl>
            @else
                <p class="py-4 text-sm font-medium text-center text-gray-500">
                    Tu carrito está vacío
                </p>
            @endif
        </div>

        @if ($this->cart)
            <div class="mt-4 space-y-4 text-center">
                <button class="at-button is-primary w-full" type="button" wire:click="updateLines">
                    Actualizar carrito
                </button>

                <a class="at-button is-primary" href="" wire:navigate>
                    Finalizar compra
                </a>

                <a class="at-button is-primary" href="{{ url('/') }}" wire:navigate>
                    Seguir comprando
                </a>
            </div>
        @endif
    </div>
</div>
