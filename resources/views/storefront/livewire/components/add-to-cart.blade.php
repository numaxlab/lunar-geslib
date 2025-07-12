<div>
    <button class="at-button is-primary w-full" wire:click.prevent="addToCart">
        @if ($displayPrice && $pricing)
            <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>

            {{ $pricing->priceIncTax()->formatted() }}
        @else
            Comprar
        @endif
    </button>

    @if ($errors->has('quantity'))
        <div class="p-2 mt-4 text-xs font-medium text-center text-red-700 rounded bg-red-50"
             role="alert">
            @foreach ($errors->get('quantity') as $error)
                {{ $error }}
            @endforeach
        </div>
    @endif
</div>
