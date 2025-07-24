<div class="flex flex-col gap-6 sm:w-sm sm:mx-auto">
    <!-- Session Status -->
    <x-lunar-geslib::auth.session-status class="text-center" :status="session('status')"/>

    <form wire:submit="resetPassword" class="flex flex-col gap-6">
        <x-numaxlab-atomic::atoms.input
                wire:model="email"
                type="email"
                name="email"
                id="email"
                placeholder="email@example.com"
                required
                autocomplete="email"
        >
            {{ __('Email address') }}
        </x-numaxlab-atomic::atoms.input>

        <x-numaxlab-atomic::atoms.input
                wire:model="password"
                type="password"
                name="password"
                id="password"
                placeholder="{{ __('Password') }}"
                required
                autocomplete="new-password"
        >
            {{ __('Password') }}
        </x-numaxlab-atomic::atoms.input>

        <x-numaxlab-atomic::atoms.input
                wire:model="password_confirmation"
                type="password"
                name="password_confirmation"
                id="password-confirmation"
                placeholder="{{ __('Confirm password') }}"
                required
                autocomplete="new-password"
        >
            {{ __('Confirm password') }}
        </x-numaxlab-atomic::atoms.input>

        <x-numaxlab-atomic::atoms.button type="submit" class="is-primary w-full">
            {{ __('Reset password') }}
        </x-numaxlab-atomic::atoms.button>
    </form>
</div>
