<?php

namespace NumaxLab\Lunar\Geslib;

use Illuminate\Support\ServiceProvider;
use Lunar\Admin\Support\Facades\AttributeData;
use NumaxLab\Lunar\Geslib\Admin\Http\Livewire\Components\GeslibDashboard;
use NumaxLab\Lunar\Geslib\Admin\Http\Livewire\Components\GeslibOrderStatusWidget;
use NumaxLab\Lunar\Geslib\Admin\Support\FieldTypes\DateField;
use NumaxLab\Lunar\Geslib\Console\Commands\Geslib\Import;
use NumaxLab\Lunar\Geslib\Console\Commands\Install;
use NumaxLab\Lunar\Geslib\FieldTypes\Date;

// Added for event listening

// Added for Livewire components

// Assuming a hypothetical event for menu building. This path may need adjustment.
// use Lunar\Admin\Events\BuildingSidebar;

// use NumaxLab\Lunar\Geslib\Admin\Http\Livewire\Components\GeslibFileImportLog; // Removed
// use NumaxLab\Lunar\Geslib\Admin\Http\Livewire\Components\GeslibOrderExportLog; // Removed

// Added for Order Status Widget

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

        $this->publishes([
            __DIR__ . '/../config/geslib.php' => config_path('lunar/geslib.php'),
            __DIR__ . '/../resources/views' => resource_path('views/vendor/lunar-geslib'), // Optional: publish views
        ], ['lunar', 'lunar-geslib']); // Added group for views, kept 'lunar' for config

        $this->registerFilamentWidgets();

        if ($this->app->runningInConsole()) {
            $this->commands([
                Install::class,
                Import::class,
            ]);
        }
    }

    protected function registerFilamentWidgets()
    {
        // This is where one would attempt to register the GeslibOrderStatusWidget
        // with Lunar's OrderResource view page.
        //
        // Example (speculative, depends on Lunar's Filament setup):
        // if (class_exists(\Lunar\Admin\Filament\Resources\OrderResource::class) && class_exists(\Filament\Facades\Filament::class)) {
        //     \Filament\Facades\Filament::serving(function () {
        //         // Ideal scenario: Lunar's OrderResource provides a static method to add widgets.
        //         // e.g., \Lunar\Admin\Filament\Resources\OrderResource::addPageWidget(
        //         //          'view', // Target the 'view' page
        //         //          \NumaxLab\Lunar\Geslib\Filament\Widgets\GeslibOrderStatusWidget::class
        //         // );
        //
        //         // Another possibility: If Lunar's ViewOrder page allows dynamic widget registration.
        //         // \Lunar\Admin\Filament\Resources\OrderResource\Pages\ViewOrder::addExtraWidget(
        //         //      \NumaxLab\Lunar\Geslib\Filament\Widgets\GeslibOrderStatusWidget::class
        //         // );
        //     });
        // }
        //
        // For now, integration of GeslibOrderStatusWidget into Lunar's Order detail page
        // will likely require manual placement by the end-user if they customize
        // Lunar's OrderResource pages, or if Lunar provides a specific hook/slot system
        // not discoverable here. The widget is available as:
        // <livewire:NumaxLab.Lunar.Geslib.Filament.Widgets.GeslibOrderStatusWidget :record="$this->record" />
        // (if $this->record is the Order model on that page).
    }
}
