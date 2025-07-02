<div>
    <form wire:submit="updateProfileInformation" class="flex flex-col gap-6">
        <x-numaxlab-atomic::atoms.input
                wire:model="name"
                type="text"
                name="name"
                id="name"
                required
                autofocus
                autocomplete="name"
                placeholder="{{ __('Full name') }}"
        >
            {{ __('Name') }}
        </x-numaxlab-atomic::atoms.input>

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

        @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
            <div>
                <p class="mt-4">
                    {{ __('Your email address is unverified.') }}

                    <a class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                        {{ __('Click here to re-send the verification email.') }}
                    </a>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </p>
                @endif
            </div>
        @endif

        <x-numaxlab-atomic::atoms.button type="submit" class="is-primary w-full">
            {{ __('Save') }}
        </x-numaxlab-atomic::atoms.button>

        <x-lunar-geslib::action-message class="me-3" on="profile-updated">
            {{ __('Saved.') }}
        </x-lunar-geslib::action-message>
    </form>

    <form wire:submit="deleteUser" class="space-y-6 mt-9">
        <h2 class="at-heading is-2">{{ __('Delete your account and all of its resources') }}</h2>

        <p>{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}</p>

        <x-numaxlab-atomic::atoms.input
                wire:model="password"
                type="password"
                name="password"
                id="password"
                placeholder="{{ __('Password') }}"
                required
        >
            {{ __('Password') }}
        </x-numaxlab-atomic::atoms.input>

        <div class="flex justify-end space-x-2 rtl:space-x-reverse">
            <x-numaxlab-atomic::atoms.button variant="danger" type="submit">
                {{ __('Delete account') }}
            </x-numaxlab-atomic::atoms.button>
        </div>
    </form>
</div>