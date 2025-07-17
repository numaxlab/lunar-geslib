<div x-data="{ menuExpanded: false, searchExpanded: {{ request()->routeIs('lunar.geslib.storefront.search') ? 'true' : 'false' }} }">
    <div class="container mx-auto px-4">
        <header class="org-site-header lg:gap-10">
            <a class="text-xl font-bold" href="{{ route('lunar.geslib.storefront.homepage') }}" wire:navigate>
                {{ config('app.name') }}
            </a>

            <div class="lg:hidden">
                <x-lunar-geslib::header.actions/>
            </div>

            <nav
                    id="site-header-nav"
                    class="site-header-nav lg:flex lg:flex-col-reverse lg:grow"
                    :class="{ 'block': menuExpanded }"
            >
                <div class="lg:flex lg:w-full lg:justify-between">
                    <ul class="site-header-main-menu">
                        @if ($sectionCollections->isNotEmpty())
                            <li x-data="collapsible">
                                <button
                                        @click="toggle"
                                        aria-expanded="false"
                                        aria-controls="sections-submenu"
                                        class="text-primary"
                                >
                                    {{ __('Secciones') }}

                                    <i class="collapsible-icon fa-solid fa-angle-down"
                                       data-alt="fa-solid fa-angle-up"
                                       aria-hidden="true"></i>
                                </button>

                                <ul id="sections-submenu" hidden class="lg:absolute lg:z-10 lg:bg-white">
                                    @foreach($sectionCollections as $collection)
                                        <li>
                                            <a
                                                    href="{{ route('lunar.geslib.storefront.sections.show', $collection->defaultUrl->slug) }}"
                                                    wire:navigate
                                            >
                                                {{ $collection->translateAttribute('name') }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif

                        <li>
                            <a href="{{ route('lunar.geslib.storefront.itineraries.index') }}" wire:navigate>
                                {{ __('Itinerarios') }}
                            </a>
                        </li>
                    </ul>

                    <div class="hidden lg:block">
                        <x-lunar-geslib::header.actions/>
                    </div>
                </div>

                <ul class="mb-5">
                    <li><a href="#">Menú de utilidades</a></li>
                </ul>
            </nav>
        </header>
    </div>

    <div class="-mt-10 mb-10 hidden" :class="{ 'hidden': !searchExpanded, 'block': searchExpanded }">
        <livewire:numax-lab.lunar.geslib.storefront.livewire.components.search/>
    </div>
</div>