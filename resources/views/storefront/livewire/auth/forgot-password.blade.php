<div class="flex flex-col gap-6 px-100">
    <!-- Session Status -->
    <x-lunar-geslib::auth.session-status class="text-center" :status="session('status')"/>

    <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
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

        <x-numaxlab-atomic::atoms.button type="submit" class="is-primary w-full">
            {{ __('Email password reset link') }}
        </x-numaxlab-atomic::atoms.button>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
        {{ __('Or, return to') }}
        <a href="{{ route('login') }}" wire:navigate>{{ __('log in') }}</a>
    </div>
</div>
