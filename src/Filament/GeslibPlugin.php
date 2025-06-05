<?php

namespace NumaxLab\Lunar\Geslib\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use NumaxLab\Lunar\Geslib\Filament\Pages\GeslibDashboardPage;
use NumaxLab\Lunar\Geslib\Filament\Resources\GeslibFileInterResource;
use NumaxLab\Lunar\Geslib\Filament\Resources\GeslibOrderSyncLogResource;
use NumaxLab\Lunar\Geslib\Filament\Widgets\GeslibConfigCheckWidget;
use NumaxLab\Lunar\Geslib\Filament\Widgets\GeslibFileImportStatsWidget;
use NumaxLab\Lunar\Geslib\Filament\Widgets\GeslibOrderSyncStatsWidget;
use NumaxLab\Lunar\Geslib\Filament\Widgets\RecentFileImportErrorsWidget;
use NumaxLab\Lunar\Geslib\Filament\Widgets\RecentOrderSyncErrorsWidget;
// Note: GeslibOrderStatusWidget is not registered globally here,
// as it's intended for a specific resource page (Lunar's OrderResource)
// and its registration method is handled separately (currently documented as manual/hook-based).

class GeslibPlugin implements Plugin
{
    public function getId(): string
    {
        return 'lunar-geslib';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->pages([
                GeslibDashboardPage::class,
            ])
            ->resources([
                GeslibFileInterResource::class,
                GeslibOrderSyncLogResource::class,
            ])
            ->widgets([
                // These are global widgets, typically for the main Filament dashboard.
                // Widgets for specific pages (like our GeslibDashboardPage) are usually
                // included directly in the page's Blade view or via getHeaderWidgets/getFooterWidgets.
                // However, registering them here makes them available to be added to any dashboard.
                GeslibFileImportStatsWidget::class,
                GeslibOrderSyncStatsWidget::class,
                GeslibConfigCheckWidget::class,
                RecentFileImportErrorsWidget::class,
                RecentOrderSyncErrorsWidget::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        // Optional: Code to run after the panel is booted
    }

    public static function make(): static
    {
        // Ensure this is resolved via the service container if it has dependencies,
        // or use `new static()` if it doesn't.
        // app(static::class) is safer if dependencies might be injected later.
        return app(static::class);
    }

    public static function get(): static
    {
        // Helper method to easily get the plugin instance.
        // Ensure it's registered in the service container or use make().
        return static::make();
    }
}
