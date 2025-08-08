<x-numaxlab-atomic::organisms.tier class="mt-7">
    <x-numaxlab-atomic::organisms.tier.header>
        <h2 class="at-heading is-2">
            {{ $type == 'shipping' ? 'Datos de envío' : 'Datos de facturación' }}
        </h2>

        @if ($currentStep > $step)
            <x-numaxlab-atomic::atoms.button
                    type="button"
                    class="at-small"
                    wire:click.prevent="$set('currentStep', {{ $step }})">
                Modificar
            </x-numaxlab-atomic::atoms.button>
        @endif
    </x-numaxlab-atomic::organisms.tier.header>
    <form wire:submit="saveAddress('{{ $type }}')">
        @if ($type == 'shipping' && $step == $currentStep)
            <x-numaxlab-atomic::atoms.forms.checkbox wire:model.live="shippingIsBilling">
                {{ __('Usar los mismos datos para facturación') }}
            </x-numaxlab-atomic::atoms.forms.checkbox>
        @endif

        @if ($currentStep >= $step)
            @if ($step == $currentStep)
                <div class="flex flex-col gap-6 mt-6">
                    @if ($customerAddresses->isNotEmpty())
                        <x-numaxlab-atomic::atoms.select
                                wire:model.live="{{ $type }}.customer_address_id"
                                name="{{ $type }}.customer_address_id"
                                id="{{ $type }}.customer_address_id"
                                label="{{ __('Tus direcciones') }}"
                        >
                            <option value="">Selecciona una de tus direcciones</option>
                            @foreach ($customerAddresses as $address)
                                <option value="{{ $address->id }}"
                                        wire:key="{{ $type . '-customer-address-' . $address->id }}">
                                    {{ $address->id }}
                                </option>
                            @endforeach
                        </x-numaxlab-atomic::atoms.select>
                    @endif

                    <x-numaxlab-atomic::atoms.input
                            wire:model="{{ $type }}.first_name"
                            type="text"
                            name="{{ $type }}.first_name"
                            id="{{ $type }}.first_name"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="{{ __('Nombre') }}"
                    >
                        {{ __('Nombre') }}
                    </x-numaxlab-atomic::atoms.input>

                    <x-numaxlab-atomic::atoms.input
                            wire:model="{{ $type }}.last_name"
                            type="text"
                            name="{{ $type }}.last_name"
                            id="{{ $type }}.last_name"
                            required
                            autocomplete="last-name"
                            placeholder="{{ __('Apellidos') }}"
                    >
                        {{ __('Apellidos') }}
                    </x-numaxlab-atomic::atoms.input>

                    <x-numaxlab-atomic::atoms.input
                            wire:model="{{ $type }}.company_name"
                            type="text"
                            name="{{ $type }}.company_name"
                            id="{{ $type }}.company_name"
                            placeholder="{{ __('Nombre de empresa') }}"
                    >
                        {{ __('Nombre de empresa') }}
                    </x-numaxlab-atomic::atoms.input>

                    <x-numaxlab-atomic::atoms.input
                            wire:model="{{ $type }}.contact_phone"
                            type="text"
                            name="{{ $type }}.contact_phone"
                            id="{{ $type }}.contact_phone"
                            placeholder="{{ __('Teléfono de contacto') }}"
                    >
                        {{ __('Teléfono de contacto') }}
                    </x-numaxlab-atomic::atoms.input>

                    <x-numaxlab-atomic::atoms.input
                            wire:model="{{ $type }}.contact_email"
                            type="email"
                            name="{{ $type }}.contact_email"
                            id="{{ $type }}.contact_email"
                            placeholder="{{ __('Email de contacto') }}"
                    >
                        {{ __('Email de contacto') }}
                    </x-numaxlab-atomic::atoms.input>

                    <x-numaxlab-atomic::atoms.select
                            wire:model.live="{{ $type }}.country_id"
                            name="{{ $type }}.country_id"
                            id="{{ $type }}.country_id"
                            label="{{ __('País') }}"
                    >
                        <option value="">Selecciona un país</option>
                        @foreach ($this->countries as $country)
                            <option value="{{ $country->id }}" wire:key="{{ $type . '-country-' . $country->id }}">
                                {{ $country->native }}
                            </option>
                        @endforeach
                    </x-numaxlab-atomic::atoms.select>

                    <x-numaxlab-atomic::atoms.input
                            wire:model="{{ $type }}.state"
                            type="text"
                            name="{{ $type }}.state"
                            id="{{ $type }}.state"
                            required
                            placeholder="{{ __('Provincia') }}"
                    >
                        {{ __('Provincia') }}
                    </x-numaxlab-atomic::atoms.input>

                    <x-numaxlab-atomic::atoms.input
                            wire:model="{{ $type }}.city"
                            type="text"
                            name="{{ $type }}.city"
                            id="{{ $type }}.city"
                            required
                            placeholder="{{ __('Ciudad') }}"
                    >
                        {{ __('Ciudad') }}
                    </x-numaxlab-atomic::atoms.input>

                    <x-numaxlab-atomic::atoms.input
                            wire:model="{{ $type }}.postcode"
                            type="text"
                            name="{{ $type }}.postcode"
                            id="{{ $type }}.postcode"
                            required
                            placeholder="{{ __('Código postal') }}"
                    >
                        {{ __('Código postal') }}
                    </x-numaxlab-atomic::atoms.input>

                    <x-numaxlab-atomic::atoms.input
                            wire:model="{{ $type }}.line_one"
                            type="text"
                            name="{{ $type }}.line_one"
                            id="{{ $type }}.line_one"
                            required
                            placeholder="{{ __('Línea de dirección 1') }}"
                    >
                        {{ __('Línea de dirección 1') }}
                    </x-numaxlab-atomic::atoms.input>

                    <x-numaxlab-atomic::atoms.input
                            wire:model="{{ $type }}.line_two"
                            type="text"
                            name="{{ $type }}.line_two"
                            id="{{ $type }}.line_two"
                            placeholder="{{ __('Línea de dirección 2') }}"
                    >
                        {{ __('Línea de dirección 2') }}
                    </x-numaxlab-atomic::atoms.input>

                    <x-numaxlab-atomic::atoms.button
                            class="is-primary"
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="saveAddress"
                            wire:key="{{ $type }}-submit-button">
                        <span wire:loading.remove
                              wire:target="saveAddress">
                            Continuar
                        </span>

                        <span wire:loading
                              wire:target="saveAddress">
                            Guardando...
                        </span>
                    </x-numaxlab-atomic::atoms.button>
                </div>
            @elseif($currentStep > $step)
                <dl class="grid grid-cols-1 gap-8 text-sm sm:grid-cols-2">
                    <div>
                        <div class="space-y-4">
                            <div>
                                <dt class="font-medium">
                                    Nombre
                                </dt>

                                <dd class="mt-0.5">
                                    {{ $this->{$type}->first_name }} {{ $this->{$type}->last_name }}
                                </dd>
                            </div>

                            @if ($this->{$type}->company_name)
                                <div>
                                    <dt class="font-medium">
                                        Empresa
                                    </dt>

                                    <dd class="mt-0.5">
                                        {{ $this->{$type}->company_name }}
                                    </dd>
                                </div>
                            @endif

                            @if ($this->{$type}->contact_phone)
                                <div>
                                    <dt class="font-medium">
                                        Teléfono
                                    </dt>

                                    <dd class="mt-0.5">
                                        {{ $this->{$type}->contact_phone }}
                                    </dd>
                                </div>
                            @endif

                            <div>
                                <dt class="font-medium">
                                    Email
                                </dt>

                                <dd class="mt-0.5">
                                    {{ $this->{$type}->contact_email }}
                                </dd>
                            </div>
                        </div>
                    </div>

                    <div>
                        <dt class="font-medium">
                            Dirección
                        </dt>

                        <dd class="mt-0.5">
                            {{ $this->{$type}->line_one }}<br>
                            @if ($this->{$type}->line_two)
                                {{ $this->{$type}->line_two }}<br>
                            @endif
                            @if ($this->{$type}->city)
                                {{ $this->{$type}->city }}<br>
                            @endif
                            @if ($this->{$type}->state)
                                {{ $this->{$type}->state }}<br>
                            @endif
                            {{ $this->{$type}->postcode }}<br>
                            {{ $this->{$type}->country_id }}
                        </dd>
                    </div>
                </dl>
            @endif
        @endif
    </form>
</x-numaxlab-atomic::organisms.tier>