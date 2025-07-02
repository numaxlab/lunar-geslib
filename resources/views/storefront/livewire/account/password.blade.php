<div>
    <form wire:submit="updatePassword" class="flex flex-col gap-6">
        <x-numaxlab-atomic::atoms.input
                wire:model="current_password"
                type="password"
                name="current_password"
                id="password"
                autocomplete="current-password"
                placeholder="{{ __('Current password') }}"
                required
        >
            {{ __('Current password') }}
        </x-numaxlab-atomic::atoms.input>

        <x-numaxlab-atomic::atoms.input
                wire:model="password"
                type="password"
                name="password"
                id="password"
                placeholder="{{ __('New password') }}"
                required
                autocomplete="new-password"
        >
            {{ __('New password') }}
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
            {{ __('Save') }}
        </x-numaxlab-atomic::atoms.button>

        <x-lunar-geslib::action-message class="me-3" on="password-updated">
            {{ __('Saved.') }}
        </x-lunar-geslib::action-message>
    </form>
</div>