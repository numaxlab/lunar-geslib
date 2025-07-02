<div class="flex flex-col gap-6 px-100">
    <!-- Session Status -->
    <x-lunar-geslib::auth.session-status class="text-center" :status="session('status')"/>

    <form wire:submit="confirmPassword" class="flex flex-col gap-6">
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

        <x-numaxlab-atomic::atoms.button type="submit" class="is-primary w-full">
            {{ __('Confirm') }}
        </x-numaxlab-atomic::atoms.button>
    </form>
</div>