<article>
    <h1 class="at-heading is-1">
        ¿Cómo quieres recibir tu pedido?
    </h1>

    @if ($this->shippingOptions->isEmpty())
        <p class="py-4 text-sm">
            No hay opciones de envío disponibles para tu dirección.
        </p>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @foreach ($this->shippingOptions as $option)
                <div>
                    <input class="hidden peer"
                           type="radio"
                           wire:model.live="chosenShipping"
                           name="shippingOption"
                           value="{{ $option->getIdentifier() }}"
                           id="{{ $option->getIdentifier() }}"/>

                    <label class="flex items-center justify-between p-4 text-sm font-medium border border-gray-100 rounded-lg shadow-sm cursor-pointer peer-checked:border-blue-500 hover:bg-gray-50 peer-checked:ring-1 peer-checked:ring-blue-500"
                           for="{{ $option->getIdentifier() }}">
                        {{ $option->getName() }}

                        {{ $option->getPrice()->formatted() }}
                    </label>
                </div>
            @endforeach
        </div>
    @endif
</article>