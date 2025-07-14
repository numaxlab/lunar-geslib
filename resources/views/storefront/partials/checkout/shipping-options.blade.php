<x-numaxlab-atomic::organisms.tier class="mt-7">
    <x-numaxlab-atomic::organisms.tier.header>
        <h2 class="at-heading is-2">
            Tipo de envío
        </h2>

        @if ($currentStep > $step)
            <x-numaxlab-atomic::atoms.button
                    class="is-secondary"
                    type="button"
                    wire:click.prevent="$set('currentStep', {{ $step }})">
                Modificar
            </x-numaxlab-atomic::atoms.button>
        @endif
    </x-numaxlab-atomic::organisms.tier.header>
    <form wire:submit="saveShippingOption">
        @if ($currentStep >= $step)
            @if ($currentStep == $step)
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($this->shippingOptions as $option)
                        <div>
                            <input class="hidden peer"
                                   type="radio"
                                   wire:model.live="chosenShipping"
                                   name="shippingOption"
                                   value="{{ $option->getIdentifier() }}"
                                   id="{{ $option->getIdentifier() }}"/>

                            <label class="flex items-center justify-between p-4 text-sm font-medium border border-gray-100 shadow-sm cursor-pointer peer-checked:border-blue-500 hover:bg-gray-50 peer-checked:ring-1 peer-checked:ring-blue-500"
                                   for="{{ $option->getIdentifier() }}">
                                <span>
                                    {{ $option->getName() }}
                                </span>

                                <span>
                                    {{ $option->getPrice()->formatted() }}
                                </span>
                            </label>
                        </div>
                    @endforeach
                </div>

                @if ($errors->has('chosenShipping'))
                    <p class="p-4 text-sm text-red-500">
                        {{ $errors->first('chosenShipping') }}
                    </p>
                @endif
            @elseif($currentStep > $step && $this->shippingOption)
                <dl class="flex flex-wrap max-w-xs text-sm">
                    <dt class="w-1/2 font-medium">
                        {{ $this->shippingOption->getName() }}
                    </dt>

                    <dd class="w-1/2 text-right">
                        {{ $this->shippingOption->getPrice()->formatted() }}
                    </dd>
                </dl>
            @endif

            @if ($step == $currentStep)
                <div class="mt-6 text-right">
                    <x-numaxlab-atomic::atoms.button class="is-primary w-full"
                                                     type="submit"
                                                     wire:key="shipping_submit_btn">
                        <span wire:loading.remove.delay
                              wire:target="saveShippingOption">
                            Escoger tipo de envío
                        </span>
                        <span wire:loading.delay
                              wire:target="saveShippingOption">
                            <svg class="w-5 h-5 text-white animate-spin"
                                 xmlns="http://www.w3.org/2000/svg"
                                 fill="none"
                                 viewBox="0 0 24 24">
                                <circle class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"></circle>
                                <path class="opacity-75"
                                      fill="currentColor"
                                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </span>
                    </x-numaxlab-atomic::atoms.button>
                </div>
            @endif
        @endif
    </form>
</x-numaxlab-atomic::organisms.tier>