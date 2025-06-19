<?php

namespace NumaxLab\Lunar\Geslib;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
use Lunar\Admin\Support\Facades\AttributeData;
use Lunar\Facades\ModelManifest;
use NumaxLab\Lunar\Geslib\Admin\Support\FieldTypes\DateField;
use NumaxLab\Lunar\Geslib\Console\Commands\Geslib\Import;
use NumaxLab\Lunar\Geslib\Console\Commands\Install;
use NumaxLab\Lunar\Geslib\FieldTypes\Date;
use NumaxLab\Lunar\Geslib\Listeners\EnrichProductFromDilveSubscriber;
use Spatie\ArrayToXml\ArrayToXml;

class LunarGeslibServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/geslib.php', 'lunar.geslib');

        AttributeData::registerFieldType(Date::class, DateField::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'lunar-geslib');
        Blade::anonymousComponentPath(__DIR__ . '/../resources/views/storefront/components', 'lunar-geslib');

        if (config('lunar.geslib.api_routes_enabled', false)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        }

        $this->publishes([
            __DIR__ . '/../config/geslib.php' => config_path('lunar/geslib.php'),
            __DIR__ . '/../resources/views' => resource_path('views/vendor/lunar/geslib'),
            __DIR__ . '/../routes/storefront.php' => base_path('routes/storefront.php'),
        ], ['lunar']);

        ModelManifest::replace(
            \Lunar\Models\Contracts\Product::class,
            \NumaxLab\Lunar\Geslib\Models\Product::class,
        );

        Event::subscribe(EnrichProductFromDilveSubscriber::class);

        Response::macro(
            'xml',
            function (array $data, int $status = 200, array $headers = [], string $rootElement = 'response') {
                $xml = ArrayToXml::convert($data, $rootElement, true, 'utf-8');

                return Response::make($xml, $status, array_merge($headers, [
                    'Content-Type' => 'application/xml',
                ]));
            },
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                Install::class,
                Import::class,
            ]);
        }
    }
}
