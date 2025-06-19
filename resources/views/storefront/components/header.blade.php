<div class="container mx-auto px-2">
    <header class="org-site-header">
        <a class="text-xl font-bold" href="{{ route('lunar.geslib.storefront.products.index') }}">
            {{ config('app.name') }}
        </a>
        <button class="site-header-nav-toggle" aria-label="Toggle navigation">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>
        <nav class="site-header-nav">
            <ul class="ml-lang-switcher">
                <li><a href="#">GL</a></li>
                <li><a href="#">ES</a></li>
                <li><a href="#">EN</a></li>
            </ul>
            <ul class="site-header-main-menu">
                <li><a href="{{ route('lunar.geslib.storefront.products.index') }}">Productos</a></li>
            </ul>
        </nav>
    </header>
</div>
