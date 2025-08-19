<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Admin\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use NumaxLab\Lunar\Geslib\Admin\Filament\Pages\GeslibDashboardPage;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\AuthorResource;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\GeslibInterFileResource;
use NumaxLab\Lunar\Geslib\Admin\Filament\Widgets\GeslibFileImportStatsWidget;
use NumaxLab\Lunar\Geslib\Admin\Filament\Widgets\GeslibOrderSyncStatsWidget;
use NumaxLab\Lunar\Geslib\Admin\Filament\Widgets\RecentFileImportErrorsWidget;

class GeslibPlugin implements Plugin
{
    public static function get(): static
    {
        return static::make();
    }

    public static function make(): static
    {
        return app(static::class);
    }

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
                AuthorResource::class,
                GeslibInterFileResource::class,
            ])
            ->widgets([
                GeslibFileImportStatsWidget::class,
                GeslibOrderSyncStatsWidget::class,
                RecentFileImportErrorsWidget::class,
            ]);
    }

    public function boot(Panel $panel): void {}
}
