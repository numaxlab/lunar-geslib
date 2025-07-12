<article>
    <h1 class="at-heading is-1">
        @if (request()->routeIs('settings.edit-address'))
            {{ __('Editar dirección') }}
        @else
            {{ __('Añadir dirección') }}
        @endif
    </h1>

    <form wire:submit="save" class="flex flex-col gap-6 mt-10">
        <x-numaxlab-atomic::atoms.input
                wire:model="form.first_name"
                type="text"
                name="form.first_name"
                id="form.first_name"
                required
                autofocus
                autocomplete="name"
                placeholder="{{ __('Nombre') }}"
        >
            {{ __('Nombre') }}
        </x-numaxlab-atomic::atoms.input>

        <x-numaxlab-atomic::atoms.input
                wire:model="form.last_name"
                type="text"
                name="form.last_name"
                id="form.last_name"
                required
                autocomplete="last-name"
                placeholder="{{ __('Apellidos') }}"
        >
            {{ __('Apellidos') }}
        </x-numaxlab-atomic::atoms.input>

        <x-numaxlab-atomic::atoms.input
                wire:model="form.company_name"
                type="text"
                name="form.company_name"
                id="form.company_name"
                placeholder="{{ __('Nombre de empresa') }}"
        >
            {{ __('Nombre de empresa') }}
        </x-numaxlab-atomic::atoms.input>

        <x-numaxlab-atomic::atoms.select
                wire:model.live="form.country_id"
                name="form.country_id"
                id="form.country_id"
                label="{{ __('País') }}"
        >
            <option value="">Selecciona un país</option>
            @foreach ($form->countries as $country)
                <option value="{{ $country->id }}">{{ $country->name }}</option>
            @endforeach
        </x-numaxlab-atomic::atoms.select>

        @if ($form->states->isNotEmpty())
            <x-numaxlab-atomic::atoms.select
                    wire:model="form.state"
                    name="form.state"
                    id="form.state"
                    label="{{ __('Provincia') }}"
            >
                <option value="">Selecciona una provincia</option>
                @foreach($form->states as $state)
                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                @endforeach
            </x-numaxlab-atomic::atoms.select>
        @endif

        <div class="flex flex-col gap-6 md:flex-row md:gap-2">
            <x-numaxlab-atomic::atoms.input
                    wire:model="form.postcode"
                    type="text"
                    name="form.postcode"
                    id="form.postcode"
                    required
                    placeholder="{{ __('Código postal') }}"
            >
                {{ __('Código postal') }}
            </x-numaxlab-atomic::atoms.input>

            <x-numaxlab-atomic::atoms.input
                    wire:model="form.city"
                    type="text"
                    name="form.city"
                    id="form.city"
                    required
                    placeholder="{{ __('Ciudad') }}"
            >
                {{ __('Ciudad') }}
            </x-numaxlab-atomic::atoms.input>
        </div>

        <x-numaxlab-atomic::atoms.input
                wire:model="form.line_one"
                type="text"
                name="form.line_one"
                id="form.line_one"
                required
                placeholder="{{ __('Línea de dirección 1') }}"
        >
            {{ __('Línea de dirección 1') }}
        </x-numaxlab-atomic::atoms.input>

        <x-numaxlab-atomic::atoms.input
                wire:model="form.line_two"
                type="text"
                name="form.line_two"
                id="form.line_two"
                placeholder="{{ __('Línea de dirección 2') }}"
        >
            {{ __('Línea de dirección 2') }}
        </x-numaxlab-atomic::atoms.input>

        <x-numaxlab-atomic::atoms.forms.checkbox
                wire:model="form.shipping_default"
                value="1"
                name="form.shipping_default"
                id="form.shipping_default"
        >
            {{ __('Es mi dirección principal') }}
        </x-numaxlab-atomic::atoms.forms.checkbox>

        <x-numaxlab-atomic::atoms.button type="submit" class="is-primary w-full">
            {{ __('Guardar') }}
        </x-numaxlab-atomic::atoms.button>
    </form>
</article>