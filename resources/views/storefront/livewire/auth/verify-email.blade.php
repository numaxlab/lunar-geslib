<div class="flex flex-col gap-6">
    <p>{{ __('Please verify your email address by clicking on the link we just emailed to you.') }}</p>

    @if (session('status') == 'verification-link-sent')
        <p>
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </p>
    @endif

    <div class="flex flex-col items-center justify-between space-y-3">
        <x-numaxlab-atomic::atoms.button wire:click="sendVerification" type="button" class="is-primary w-full">
            {{ __('Resend verification email') }}
        </x-numaxlab-atomic::atoms.button>

        <x-numaxlab-atomic::atoms.button wire:click="logout" type="button" class="is-primary w-full">
            {{ __('Log out') }}
        </x-numaxlab-atomic::atoms.button>
    </div>
</div>