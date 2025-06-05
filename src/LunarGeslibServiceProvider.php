<?php

namespace NumaxLab\Lunar\Geslib;

use Illuminate\Support\Facades\Event; // Added for event listening
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire; // Added for Livewire components
use Lunar\Admin\Support\Facades\AttributeData;
// Assuming a hypothetical event for menu building. This path may need adjustment.
// use Lunar\Admin\Events\BuildingSidebar;
use NumaxLab\Lunar\Geslib\Admin\Http\Livewire\Components\GeslibDashboard;
// use NumaxLab\Lunar\Geslib\Admin\Http\Livewire\Components\GeslibFileImportLog; // Removed
// use NumaxLab\Lunar\Geslib\Admin\Http\Livewire\Components\GeslibOrderExportLog; // Removed
use NumaxLab\Lunar\Geslib\Admin\Http\Livewire\Components\GeslibOrderStatusWidget; // Added for Order Status Widget
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
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'lunar-geslib');

        $this->publishes([
            __DIR__ . '/../config/geslib.php' => config_path('lunar/geslib.php'), // Keep config publish
            __DIR__.'/../../resources/views' => resource_path('views/vendor/lunar-geslib'), // Optional: publish views
        ], ['lunar-geslib-views', 'lunar']); // Added group for views, kept 'lunar' for config

        // Register Livewire components
        // Livewire::component('lunar-geslib.admin.geslib-dashboard', GeslibDashboard::class); // Removed
        // Livewire::component('lunar-geslib.admin.geslib-file-import-log', GeslibFileImportLog::class); // Removed
        // Livewire::component('lunar-geslib.admin.geslib-order-export-log', GeslibOrderExportLog::class); // Removed
        // Livewire::component('lunar-geslib.admin.geslib-order-status-widget', GeslibOrderStatusWidget::class); // Removed

        // $this->registerNavigations(); // Removed old navigation logic
        $this->registerFilamentWidgets();

        if ($this->app->runningInConsole()) {
            $this->commands([
                Install::class,
                Import::class,
            ]);
        }
    }

    // protected function registerNavigations() // Removed old navigation logic
    // {
    //     // This is a speculative implementation for adding a menu item.
    //     // The actual event and menu builder might differ in Lunar AdminHub.
    //     // Replace '\Lunar\Admin\Events\BuildingSidebar::class' with the actual event if known.
    //     // Ensure the menu item structure ($menu->section(...)) matches Lunar's API.
    //     //
    //     // IMPORTANT: For email notifications (GeslibFileImportFailed, GeslibConfigurationError) to work,
    //     // the host Laravel application MUST have its mail driver correctly configured (e.g., in .env).
    //     $sidebarMenuEvent = '\Lunar\Admin\Events\BuildingSidebar'; // Using a string to avoid class not found if it's hypothetical
    //
    //     if (class_exists($sidebarMenuEvent)) {
    //         Event::listen($sidebarMenuEvent, function ($menu) {
    //             // Ensure $menu object has methods like 'section', 'route', 'group', 'icon'
    //             // This is a common pattern, but Lunar's specific API might vary.
    //             // Example:
    //             // $section = $menu->section('Geslib Integration')
    //             //    ->route('adminhub.geslib.dashboard') // This route no longer exists
    //             //    ->group('Settings') // Or 'Addons', 'Tools', etc.
    //             //    ->icon('heroicons-o-puzzle-piece'); // Example icon (heroicons are common in Livewire UIs)
    //             // $menu->add($section);
    //             //
    //             // For now, let's assume a simpler direct add if the event itself is the menu builder
    //             if (method_exists($menu, 'addSection')) {
    //                  $menu->addSection(function ($section) {
    //                     $section->name('Geslib Integration')
    //                         ->handle('geslib-integration')
    //                         // ->route('adminhub.geslib.dashboard') // This route no longer exists
    //                         ->group('settings') // Common group handle for settings
    //                         ->icon('heroicons-o-puzzle-piece'); // Example icon
    //                  });
    //             } elseif (method_exists($menu, 'addItem')) {
    //                 // A different possible API for the menu builder
    //                 $menu->addItem(function ($item) {
    //                     $item->name('Geslib Integration')
    //                         // ->route('adminhub.geslib.dashboard') // This route no longer exists
    //                         ->group('settings')
    //                         ->icon('heroicons-o-puzzle-piece');
    //                 });
    //             }
    //             // If the above doesn't work, this part needs to be adjusted to how Lunar expects menu items to be added.
    //         });
    //     }
    // }

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
