<div class="flex flex-col gap-6 px-2">
    <h1 class="at-heading is-1">User dashboard</h1>

    <ul class="flex gap-4">
        <li><a href="{{ route('settings.profile') }}" wire:navigate>Perfil</a></li>
        <li><a href="{{ route('settings.password') }}" wire:navigate>Contraseña</a></li>
        <li>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <x-numaxlab-atomic::atoms.button type="submit">
                    {{ __('Logout') }}
                </x-numaxlab-atomic::atoms.button>
            </form>
        </li>
    </ul>
</div>