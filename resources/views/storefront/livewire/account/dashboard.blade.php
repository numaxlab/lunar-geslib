<article>
    <h1 class="at-heading is-1">Mi perfil</h1>

    <ul>
        <li>{{ $user->full_name }}</li>
        <li>{{ $user->email }}</li>
        <li><a href="{{ route('settings.profile') }}" wire:navigate>Gestionar perfil</a></li>
        <li><a href="{{ route('settings.password') }}" wire:navigate>Modificar contraseña</a></li>
        <li>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <x-numaxlab-atomic::atoms.button type="submit">
                    {{ __('Logout') }}
                </x-numaxlab-atomic::atoms.button>
            </form>
        </li>
    </ul>
</article>