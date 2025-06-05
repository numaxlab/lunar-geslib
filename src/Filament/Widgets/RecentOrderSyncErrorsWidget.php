<?php

namespace NumaxLab\Lunar\Geslib\Filament\Widgets;

use Filament\Widgets\Widget;
use NumaxLab\Lunar\Geslib\Models\LunarGeslibOrderSyncLog;
use NumaxLab\Lunar\Geslib\Filament\Resources\GeslibOrderSyncLogResource; // For linking
use Illuminate\Database\Eloquent\Collection;

class RecentOrderSyncErrorsWidget extends Widget
{
    protected static string $view = 'lunar-geslib::filament.widgets.recent-order-sync-errors-widget';

    protected int | string | array $columnSpan = '1';

    public Collection $recentSyncErrors;

    public function mount(): void
    {
        $this->recentSyncErrors = LunarGeslibOrderSyncLog::where('status', 'error')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }

    public function getTableResource(): string
    {
        return GeslibOrderSyncLogResource::class;
    }
}
