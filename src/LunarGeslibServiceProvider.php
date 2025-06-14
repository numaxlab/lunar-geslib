<?php

namespace NumaxLab\Lunar\Geslib;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Lunar\Admin\Support\Facades\AttributeData;
use NumaxLab\Lunar\Geslib\Admin\Support\FieldTypes\DateField;
use NumaxLab\Lunar\Geslib\Console\Commands\Geslib\Import;
use NumaxLab\Lunar\Geslib\Console\Commands\Install;
use NumaxLab\Lunar\Geslib\FieldTypes\Date;
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

        if (config('lunar.geslib.api_routes_enabled', false)) {
            Route::prefix('api/geslib')
                ->middleware('api')
                ->group(__DIR__ . '/../routes/api.php');
        }

        $this->publishes([
            __DIR__ . '/../config/geslib.php' => config_path('lunar/geslib.php'),
            __DIR__ . '/../resources/views' => resource_path('views/vendor/lunar-geslib'), // Optional: publish views
        ], ['lunar', 'lunar-geslib']); // Added group for views, kept 'lunar' for config

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
