<article>
    <h1 class="at-heading is-1">Mi perfil</h1>

    <ul class="mt-7">
        <li><strong>{{ $user->full_name }}</strong></li>
        <li>{{ $user->email }}</li>
        <li>
            <a href="{{ route('settings.profile') }}" wire:navigate>Gestionar perfil</a>
            <a href="{{ route('settings.password') }}" wire:navigate>Modificar contraseña</a>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button type="submit">
                    {{ __('Logout') }}
                </button>
            </form>
        </li>
    </ul>

    @if ($defaultAddress)
        <ul class="mt-7">
            <li><strong>Dirección principal</strong></li>
            <li>
                <address>
                    {{ $defaultAddress->line_one }}<br>
                    {{ $defaultAddress->postcode }} {{ $defaultAddress->city }}
                </address>
                <a href="{{ route('settings.edit-address', $defaultAddress->id) }}" wire:navigate>Editar</a>
            </li>
        </ul>
    @endif

    <x-numaxlab-atomic::organisms.tier class="mt-10">
        <x-numaxlab-atomic::organisms.tier.header class="flex gap-2">
            <h2 class="at-heading is-2">Últimos pedidos</h2>
            <a href="">Ver todos</a>
        </x-numaxlab-atomic::organisms.tier.header>

        <p>Todavía no has realizado ningún pedido.</p>
    </x-numaxlab-atomic::organisms.tier>

    <x-numaxlab-atomic::organisms.tier class="mt-10">
        <x-numaxlab-atomic::organisms.tier.header class="flex gap-2">
            <h2 class="at-heading is-2">Mis direcciones</h2>
            <a href="{{ route('settings.add-address') }}" wire:navigate>Añadir</a>
        </x-numaxlab-atomic::organisms.tier.header>

        @if ($addresses->isEmpty())
            <p>No tienes direcciones guardadas.</p>
        @else
            <ul class="flex flex-col gap-4 divide-y divide-black">
                @foreach ($addresses as $address)
                    <li>
                        <address>
                            {{ $address->line_one }}<br>
                            {{ $address->postcode }} {{ $address->city }}
                        </address>
                        <a href="{{ route('settings.edit-address', $address->id) }}" wire:navigate>Editar</a>
                        Eliminar
                    </li>
                @endforeach
            </ul>
        @endif
    </x-numaxlab-atomic::organisms.tier>
</article>