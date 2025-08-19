<?php

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Str;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\GeslibInterFileResource;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;

// For linking

class GeslibFileImportStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $lastRun = GeslibInterFile::latest('created_at')->first();
        $totalFiles = GeslibInterFile::count();
        $totalProcessed = GeslibInterFile::whereNotNull('finished_at')->count();
        $totalErrors = GeslibInterFile::whereNotNull('log')->count();
        $totalPending = GeslibInterFile::whereNull('finished_at')->count();

        $stats = [
            Stat::make('Total Files Imported', $totalFiles)
                ->description('All files received from Geslib')
                ->color('primary')
                ->url(GeslibInterFileResource::getUrl('index')),
            Stat::make('Files Processed Successfully', $totalProcessed)
                ->description($totalProcessed.' files processed without errors')
                ->color('success'),
            Stat::make('Files with Errors', $totalErrors)
                ->description($totalErrors.' files encountered errors')
                ->color('danger'),
            Stat::make('Files Pending Processing', $totalPending)
                ->description($totalPending.' files awaiting processing')
                ->color('warning'),
        ];

        if ($lastRun) {
            array_unshift(
                $stats,
                Stat::make('Last Import Run', $lastRun->created_at->diffForHumans())
                    ->description('File: '.$lastRun->name.' | Status: '.Str::ucfirst($lastRun->status))
                    ->color('success'),
            );
        } else {
            array_unshift(
                $stats,
                Stat::make('Last Import Run', 'N/A')
                    ->description('No import runs recorded yet.')
                    ->color('gray'),
            );
        }

        return $stats;
    }
}
