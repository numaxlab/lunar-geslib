<div class="container mx-auto px-2">
    <header class="org-site-header">
        <a class="text-xl font-bold" href="{{ route('lunar.geslib.storefront.products.index') }}" wire:navigate>
            {{ config('app.name') }}
        </a>
        <button class="site-header-nav-toggle" aria-label="Toggle navigation">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>
        <nav class="site-header-nav grow ml-20">
            <ul class="mb-5">
                <li><a href="#">Menú de utilidades</a></li>
            </ul>
            <div class="w-full flex justify-between">
                <ul class="site-header-main-menu">
                    <li>
                        <a href="{{ route('lunar.geslib.storefront.products.index') }}" wire:navigate>Productos</a>
                    </li>
                    <li>
                        <a href="{{ route('lunar.geslib.storefront.collections.index') }}" wire:navigate>Colecciones</a>
                    </li>
                </ul>
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
                        <a href="">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                            <span class="sr-only">Buscar</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
</div>
