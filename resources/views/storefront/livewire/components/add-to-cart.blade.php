<div>
    @if ($displayPrice && $pricing)
        <button
                class="at-button w-full"
                :class="{ 'text-primary border-primary': !hover, 'is-primary': hover }"
                wire:click.prevent="addToCart"
                x-data="{hover: false}"
                @mouseover="hover = true"
                @mouseout="hover = false"
        >
            <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
            <span x-show="!hover">{{ $pricing->priceIncTax()->formatted() }}</span>
            <span x-show="hover">{{ __('Comprar') }}</span>
        </button>
    @else
        <button class="at-button is-primary w-full" wire:click.prevent="addToCart">
            {{ __('Comprar') }}
        </button>
    @endif

    @if ($errors->has('quantity'))
        <div class="ml-alert is-danger mt-4 text-xs" role="alert">
            @foreach ($errors->get('quantity') as $error)
                {{ $error }}
            @endforeach
        </div>
    @endif
</div>
