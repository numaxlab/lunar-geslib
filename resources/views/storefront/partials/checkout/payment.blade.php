<x-numaxlab-atomic::organisms.tier class="mt-7">
    <x-numaxlab-atomic::organisms.tier.header>
        <h2 class="at-heading is-2">
            Modo de pago
        </h2>
    </x-numaxlab-atomic::organisms.tier.header>

    @if ($currentStep >= $step)
        <div>
            <x-numaxlab-atomic::atoms.forms.radio
                    wire:model.live="paymentType"
                    name="paymentType"
                    value="cash-on-delivery">
                Recogida en tienda
            </x-numaxlab-atomic::atoms.forms.radio>
        </div>
    @endif
</x-numaxlab-atomic::organisms.tier>
