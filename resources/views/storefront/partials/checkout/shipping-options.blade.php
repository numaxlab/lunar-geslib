<x-numaxlab-atomic::organisms.tier class="mt-7">
    <x-numaxlab-atomic::organisms.tier.header>
        <h2 class="at-heading is-2">
            Tipo de envío
        </h2>

        @if ($currentStep > $step)
            <x-numaxlab-atomic::atoms.button
                    type="button"
                    wire:click.prevent="$set('currentStep', {{ $step }})">
                Modificar
            </x-numaxlab-atomic::atoms.button>
        @endif
    </x-numaxlab-atomic::organisms.tier.header>

    <form wire:submit="saveShippingOption">
        @if ($currentStep >= $step)
            @if ($currentStep == $step)
                @foreach ($this->shippingOptions as $option)
                    <div wire:key="shipping-option-{{ $option->getIdentifier() }}">
                        <x-numaxlab-atomic::atoms.forms.radio
                                wire:model.live="chosenShipping"
                                name="chosenShipping"
                                value="{{ $option->getIdentifier() }}">
                            {{ $option->getName() }} {{ $option->getPrice()->formatted() }}
                        </x-numaxlab-atomic::atoms.forms.radio>
                    </div>
                @endforeach

                <x-numaxlab-atomic::atoms.button
                        class="is-primary mt-10"
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="saveShippingOption">
                        <span wire:loading.remove
                              wire:target="saveShippingOption">
                            Continuar
                        </span>

                    <span wire:loading
                          wire:target="saveShippingOption">
                            <span class="inline-flex items-center">
                                Gardando...
                            </span>
                        </span>
                </x-numaxlab-atomic::atoms.button>
            @elseif ($this->shippingOption)
                <dl class="flex flex-wrap max-w-xs text-sm">
                    <dt class="w-1/2 font-medium">
                        {{ $this->shippingOption->getName() }}
                    </dt>

                    <dd class="w-1/2 text-right">
                        {{ $this->shippingOption->getPrice()->formatted() }}
                    </dd>
                </dl>
            @endif
        @else
            <p>Primero necesitamos tus datos de envío.</p>
        @endif
    </form>
</x-numaxlab-atomic::organisms.tier>