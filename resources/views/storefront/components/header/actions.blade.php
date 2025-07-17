<ul class="flex gap-5 text-lg">
    <li>
        @auth
            <a href="{{ route('dashboard') }}" wire:navigate>
                <i class="fa-solid fa-user" aria-hidden="true"></i>
                <span class="sr-only">{{ __('Entrar en perfil') }}</span>
            </a>
        @else
            <a href="{{ route('login') }}" wire:navigate>
                <i class="fa-solid fa-user" aria-hidden="true"></i>
                <span class="sr-only">{{ __('Acceder') }}</span>
            </a>
        @endauth
    </li>
    <li>
        <livewire:numax-lab.lunar.geslib.storefront.livewire.components.cart/>
    </li>
    <li>
        <button class="text-primary" @click="searchExpanded = !searchExpanded">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <span class="sr-only">{{ __('Buscar') }}</span>
        </button>
    </li>
    <li class="site-header-nav-toggle">
        <button
                class="text-primary"
                aria-label="{{ __('Abrir/cerrar menú') }}"
                aria-controls="site-header-nav"
                :aria-expanded="menuExpanded"
                @click="menuExpanded = !menuExpanded"
        >
            <i class="fa-solid fa-bars"
               :class="{ 'fa-bars': !menuExpanded, 'fa-xmark': menuExpanded }"
               aria-hidden="true"></i>
        </button>
    </li>
</ul>