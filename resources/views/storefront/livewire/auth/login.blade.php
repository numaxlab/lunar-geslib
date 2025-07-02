<div class="flex flex-col gap-6 px-100">
    <x-lunar-geslib::auth.session-status class="text-center" :status="session('status')"/>

    <form wire:submit="login" class="flex flex-col gap-6">
        <x-numaxlab-atomic::atoms.input
                wire:model="email"
                type="email"
                name="email"
                id="email"
                placeholder="email@example.com"
                required
                autofocus
                autocomplete="email"
        >
            {{ __('Email address') }}
        </x-numaxlab-atomic::atoms.input>

        <div class="relative">
            <x-numaxlab-atomic::atoms.forms.label for="password">
                {{ __('Password') }}
            </x-numaxlab-atomic::atoms.forms.label>
            <x-numaxlab-atomic::atoms.forms.input
                    wire:model="password"
                    type="password"
                    id="password"
                    required
                    autocomplete="current-password"
                    placeholder="{{ __('Password') }}"
            ></x-numaxlab-atomic::atoms.forms.input>

            @if (Route::has('password.request'))
                <a class="absolute end-0 top-0 text-sm" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <div>
            <x-numaxlab-atomic::atoms.forms.checkbox
                    wire:model="remember"
                    value="1"
                    id="remember-me"
            >
                {{ __('Remember me') }}
            </x-numaxlab-atomic::atoms.forms.checkbox>
        </div>

        <x-numaxlab-atomic::atoms.button type="submit" class="is-primary w-full">
            {{ __('Log in') }}
        </x-numaxlab-atomic::atoms.button>
    </form>

    @if (Route::has('register'))
        <div class="space-x-1 text-center text-sm text-zinc-600">
            {{ __('Don\'t have an account?') }}
            <a href="{{ route('register') }}" wire:navigate>{{ __('Sign up') }}</a>
        </div>
    @endif
</div>
