<?php

namespace NumaxLab\Lunar\Geslib\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;
use NumaxLab\Lunar\Geslib\Filament\Resources\GeslibFileInterResource; // For linking

class GeslibFileImportStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $lastRun = GeslibInterFile::latest('created_at')->first();
        $totalFiles = GeslibInterFile::count();
        $totalProcessed = GeslibInterFile::where('status', 'processed')->count();
        $totalErrors = GeslibInterFile::where('status', 'error')->count();
        $totalPending = GeslibInterFile::where('status', 'pending')->count();

        $stats = [
            Stat::make('Total Files Imported', $totalFiles)
                ->description('All files received from Geslib')
                ->color('primary')
                ->url(GeslibFileInterResource::getUrl('index')),
            Stat::make('Files Processed Successfully', $totalProcessed)
                ->description($totalProcessed . ' files processed without errors')
                ->color('success'),
            Stat::make('Files with Errors', $totalErrors)
                ->description($totalErrors . ' files encountered errors')
                ->color('danger'),
            Stat::make('Files Pending Processing', $totalPending)
                ->description($totalPending . ' files awaiting processing')
                ->color('warning'),
        ];

        if ($lastRun) {
            array_unshift($stats, Stat::make('Last Import Run', $lastRun->created_at->diffForHumans())
                ->description('File: '.$lastRun->name.' | Status: '.ucfirst($lastRun->status))
                ->color(match($lastRun->status) {
                    'processed' => 'success',
                    'error' => 'danger',
                    'pending' => 'warning',
                    'processing' => 'info',
                    default => 'gray'
                }));
        } else {
            array_unshift($stats, Stat::make('Last Import Run', 'N/A')
                ->description('No import runs recorded yet.')
                ->color('gray'));
        }

        return $stats;
    }
}
