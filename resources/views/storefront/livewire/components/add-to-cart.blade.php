<div>
    <button class="at-button is-primary w-full mt-5" wire:click.prevent="addToCart">
        Comprar
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
