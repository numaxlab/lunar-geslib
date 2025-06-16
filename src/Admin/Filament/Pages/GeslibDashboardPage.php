<?php

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class GeslibDashboardPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Geslib';

    protected static string $view = 'lunar-geslib::filament.pages.geslib-dashboard-page';

    public static function getNavigationLabel(): string
    {
        return __('filament-panels::pages/dashboard.title');
    }

    public function getTitle(): string|Htmlable
    {
        return __('filament-panels::pages/dashboard.title');
    }
}
