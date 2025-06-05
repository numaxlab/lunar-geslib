<?php

namespace NumaxLab\Lunar\Geslib\Filament\Widgets;

use Filament\Widgets\Widget;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;
use NumaxLab\Lunar\Geslib\Filament\Resources\GeslibFileInterResource; // For linking
use Illuminate\Database\Eloquent\Collection;

class RecentFileImportErrorsWidget extends Widget
{
    protected static string $view = 'lunar-geslib::filament.widgets.recent-file-import-errors-widget';

    protected int | string | array $columnSpan = '1'; // Or 'full' if it should take full width in a grid

    public Collection $recentErrors;

    public function mount(): void
    {
        $this->recentErrors = GeslibInterFile::where('status', 'error')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }

    public function getTableResource(): string
    {
        return GeslibFileInterResource::class;
    }
}
