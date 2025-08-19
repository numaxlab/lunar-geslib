<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Collection;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\GeslibInterFileResource;
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
        return GeslibInterFileResource::class;
    }
}
