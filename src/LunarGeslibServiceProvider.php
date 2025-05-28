<?php

namespace NumaxLab\Lunar\Geslib;

use Illuminate\Support\ServiceProvider;
use Lunar\Admin\Support\Facades\AttributeData;
use NumaxLab\Lunar\Geslib\Admin\Support\FieldTypes\DateField;
use NumaxLab\Lunar\Geslib\Console\Commands\Geslib\Import;
use NumaxLab\Lunar\Geslib\Console\Commands\Install;
use NumaxLab\Lunar\Geslib\FieldTypes\Date;

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

        $this->publishes([
            __DIR__ . '/../config/geslib.php' => config_path('lunar/geslib.php'),
        ], 'lunar');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Install::class,
                Import::class,
            ]);
        }
    }
}
