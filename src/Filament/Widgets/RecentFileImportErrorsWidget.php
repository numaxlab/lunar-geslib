<?php

namespace NumaxLab\Lunar\Geslib\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Collection;
use NumaxLab\Lunar\Geslib\Filament\Resources\GeslibFileInterResource;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;

// For linking

class RecentFileImportErrorsWidget extends Widget
{
    protected static string $view = 'lunar-geslib::filament.widgets.recent-file-import-errors-widget';
    public Collection $recentErrors; // Or 'full' if it should take full width in a grid
    protected int|string|array $columnSpan = '1';

    public function mount(): void
    {
        $this->recentErrors = GeslibInterFile::whereNotNull('log')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }

    public function getTableResource(): string
    {
        return GeslibFileInterResource::class;
    }
}
