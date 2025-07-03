<div class="container mx-auto px-4">
    <header class="org-site-header" x-data="{ menuExpanded: false }">
        <a class="text-xl font-bold" href="{{ route('lunar.geslib.storefront.products.index') }}" wire:navigate>
            {{ config('app.name') }}
        </a>

        <ul class="flex gap-5 text-lg">
            <li>
                @auth
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <i class="fa-solid fa-user" aria-hidden="true"></i>
                        <span class="sr-only">Mi perfil</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" wire:navigate>
                        <i class="fa-solid fa-user" aria-hidden="true"></i>
                        <span class="sr-only">Acceder</span>
                    </a>
                @endauth
            </li>
            <li>
                @livewire('numax-lab.lunar.geslib.storefront.livewire.components.cart')
            </li>
            <li>
                <button>
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <span class="sr-only">Buscar</span>
                </button>
            </li>
            <li>
                <button
                        class="site-header-nav-toggle"
                        aria-label="Toggle navigation"
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

        <nav
                id="site-header-nav"
                class="site-header-nav"
                :class="{ 'block': menuExpanded }"
        >
            <div class=""> {{-- w-full flex justify-between --}}
                <ul class="site-header-main-menu">
                    <li>
                        <a href="{{ route('lunar.geslib.storefront.products.index') }}" wire:navigate>Productos</a>
                    </li>
                    <li>
                        <a href="{{ route('lunar.geslib.storefront.collections.index') }}" wire:navigate>Colecciones</a>
                    </li>
                </ul>
            </div>

            <ul class="mb-5">
                <li><a href="#">Menú de utilidades</a></li>
            </ul>
        </nav>
    </header>
</div>
