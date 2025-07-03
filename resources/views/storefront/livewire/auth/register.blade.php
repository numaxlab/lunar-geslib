<section class="flex flex-col gap-6">
    <h1 class="at-heading is-1">{{ __('Regístrate') }}</h1>

    <x-lunar-geslib::auth.session-status class="text-center" :status="session('status')"/>

    <form wire:submit="register" class="flex flex-col gap-6">
        <x-numaxlab-atomic::atoms.input
                wire:model="name"
                type="text"
                name="name"
                id="name"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Nombre')"
        >
            {{ __('Nombre') }}
        </x-numaxlab-atomic::atoms.input>

        <x-numaxlab-atomic::atoms.input
                wire:model="email"
                type="email"
                name="email"
                id="email"
                placeholder="email@ejemplo.com"
                required
                autocomplete="email"
        >
            {{ __('Correo electrónico') }}
        </x-numaxlab-atomic::atoms.input>

        <x-numaxlab-atomic::atoms.input
                wire:model="password"
                type="password"
                name="password"
                id="password"
                placeholder="{{ __('Contraseña') }}"
                required
                autocomplete="new-password"
        >
            {{ __('Contraseña') }}
        </x-numaxlab-atomic::atoms.input>

        <x-numaxlab-atomic::atoms.input
                wire:model="password_confirmation"
                type="password"
                name="password_confirmation"
                id="password-confirmation"
                placeholder="{{ __('Confirmar contraseña') }}"
                required
                autocomplete="new-password"
        >
            {{ __('Confirmar contraseña') }}
        </x-numaxlab-atomic::atoms.input>

        <div>
            <x-numaxlab-atomic::atoms.forms.checkbox
                    wire:model="privacy_policy"
                    value="1"
                    id="privacy-policy"
            >
                {{ __('Acepto la política de privacidad') }}
            </x-numaxlab-atomic::atoms.forms.checkbox>
        </div>

        <x-numaxlab-atomic::atoms.button type="submit" class="is-primary w-full">
            {{ __('Crear cuenta') }}
        </x-numaxlab-atomic::atoms.button>

        <p>
            {{ __('Volver a') }}
            <a href="{{ route('login') }}" wire:navigate>{{ __('iniciar sesión') }}</a>.
        </p>
    </form>
</section>