<x-numaxlab-atomic::organisms.tier class="mt-7">
    <x-numaxlab-atomic::organisms.tier.header>
        <h2 class="at-heading is-2">
            Modo de pago
        </h2>
    </x-numaxlab-atomic::organisms.tier.header>

    @if ($currentStep >= $step)
        <div class="space-y-4">
            <div class="flex gap-4">
                <button @class([
                    'px-5 py-2 text-sm border font-medium',
                    'text-primary border-primary' => $paymentType === 'cash-in-hand',
                    'text-gray-500 hover:text-gray-700' => $paymentType !== 'cash-in-hand',
                ])
                        type="button"
                        wire:click.prevent="$set('paymentType', 'cash-in-hand')">
                    Pago con recogida en tienda
                </button>
            </div>
        </div>
    @endif
</x-numaxlab-atomic::organisms.tier>
