<?php

namespace NumaxLab\Lunar\Geslib\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Collection;
use NumaxLab\Lunar\Geslib\Filament\Resources\GeslibOrderSyncLogResource;
use NumaxLab\Lunar\Geslib\Models\GeslibOrderSyncLog;

// For linking

class RecentOrderSyncErrorsWidget extends Widget
{
    protected static string $view = 'lunar-geslib::filament.widgets.recent-order-sync-errors-widget';
    public Collection $recentSyncErrors;
    protected int|string|array $columnSpan = '1';

    public function mount(): void
    {
        $this->recentSyncErrors = GeslibOrderSyncLog::where('status', 'error')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }

    public function getTableResource(): string
    {
        return GeslibOrderSyncLogResource::class;
    }
}
