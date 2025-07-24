<section class="flex flex-col gap-6 sm:w-sm sm:mx-auto">
    <h1 class="at-heading is-1">{{ __('Iniciar sesión') }}</h1>

    @if (Route::has('register'))
        <p class="">
            {{ __('¿No tienes cuenta de usuaria?') }}<br>
            <a href="{{ route('register') }}" wire:navigate>{{ __('Regístrate aquí') }}</a>.
        </p>
    @endif

    <x-lunar-geslib::auth.session-status class="text-center" :status="session('status')"/>

    <form wire:submit="login" class="flex flex-col gap-6">
        <x-numaxlab-atomic::atoms.input
                wire:model="email"
                type="email"
                name="email"
                id="email"
                placeholder="email@ejemplo.com"
                required
                autofocus
                autocomplete="email"
        >
            {{ __('Correo electrónico') }}
        </x-numaxlab-atomic::atoms.input>

        <div class="relative">
            <x-numaxlab-atomic::atoms.forms.label for="password">
                {{ __('Contraseña') }}
            </x-numaxlab-atomic::atoms.forms.label>
            <x-numaxlab-atomic::atoms.forms.input
                    wire:model="password"
                    type="password"
                    id="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Contraseña')"
            />

            @if (Route::has('password.request'))
                <a class="at-small absolute end-0 top-0" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Olvidé mi contraseña') }}
                </a>
            @endif
        </div>

        <div>
            <x-numaxlab-atomic::atoms.forms.checkbox
                    wire:model="remember"
                    value="1"
                    id="remember-me"
            >
                {{ __('Acuérdate de mi') }}
            </x-numaxlab-atomic::atoms.forms.checkbox>
        </div>

        <x-numaxlab-atomic::atoms.button type="submit" class="is-primary w-full">
            {{ __('Entrar') }}
        </x-numaxlab-atomic::atoms.button>
    </form>
</section>
