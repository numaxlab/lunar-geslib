<div class="container mx-auto px-2">
    <footer class="org-site-footer">
        <div class="lg:w-1/3 lg:flex lg:justify-between">
            <a class="block text-xl font-bold mb-5" href="{{ route('lunar.geslib.storefront.products.index') }}">
                {{ config('app.name') }}
            </a>
            <ul class="text-sm mb-5">
                <li><a href="{{ route('lunar.geslib.storefront.products.index') }}">Productos</a></li>
            </ul>
        </div>
        <div>
            <ul class="text-sm mb-5">
                <li><a href="#">Condiciones de compra</a></li>
                <li><a href="#">Política de cookies</a></li>
                <li><a href="#">Política de privacidad</a></li>
            </ul>
            <ul class="flex gap-5">
                <li>
                    <a href="#" aria-label="O noso perfil en Instagram">
                        <i class="fa-brands fa-instagram" aria-hidden="true"></i>
                    </a>
                </li>
                <li>
                    <a href="#" aria-label="O noso perfil en Bluesky">
                        <i class="fa-brands fa-bluesky" aria-hidden="true"></i>
                    </a>
                </li>
                <li>
                    <a href="#" aria-label="O noso perfil en Mastodon">
                        <i class="fa-brands fa-mastodon" aria-hidden="true"></i>
                    </a>
                </li>
            </ul>
        </div>
    </footer>
</div>
