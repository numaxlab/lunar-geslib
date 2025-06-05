<?php

namespace NumaxLab\Lunar\Geslib\Filament\Pages;

use Filament\Pages\Page;

class GeslibDashboardPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Geslib Integration';

    protected static ?string $navigationLabel = 'Geslib Dashboard';

    protected static ?string $title = 'Geslib Integration Dashboard';

    protected static string $view = 'lunar-geslib::filament.pages.geslib-dashboard-page';

    // This method can be used if you are not using a custom blade view with <livewire:> tags
    // protected function getHeaderWidgets(): array
    // {
    //     return [
    //         \NumaxLab\Lunar\Geslib\Filament\Widgets\GeslibFileImportStatsWidget::class,
    //         \NumaxLab\Lunar\Geslib\Filament\Widgets\GeslibOrderSyncStatsWidget::class,
    //         // Add other widgets here if using Dashboard layout features
    //     ];
    // }

    // If you use `getContentWidgets()` it implies a certain page layout structure (e.g. from Dashboard class)
    // For a simple `Page` class with a custom view, you typically embed widgets in the blade file.
}
