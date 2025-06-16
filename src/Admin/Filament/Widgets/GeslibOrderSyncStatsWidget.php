<?php

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\GeslibOrderSyncLogResource;
use NumaxLab\Lunar\Geslib\Models\GeslibOrderSyncLog;

// For linking

class GeslibOrderSyncStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalLogs = GeslibOrderSyncLog::count();
        $successfulSyncs = GeslibOrderSyncLog::where('status', 'success')->count();
        $failedSyncs = GeslibOrderSyncLog::where('status', 'error')->count();
        $pendingSyncs = GeslibOrderSyncLog::where('status', 'pending')->count(); // Assuming 'pending' status

        $lastSyncAttempt = GeslibOrderSyncLog::latest('created_at')->first();

        $stats = [
            Stat::make('Total Order Sync Logs', $totalLogs)
                ->description('All order synchronization attempts logged')
                ->color('primary')
                ->url(GeslibOrderSyncLogResource::getUrl('index')),
            Stat::make('Successful Syncs', $successfulSyncs)
                ->description($successfulSyncs . ' orders synced successfully')
                ->color('success'),
            Stat::make('Failed Syncs', $failedSyncs)
                ->description($failedSyncs . ' orders failed to sync')
                ->color('danger'),
            Stat::make('Pending Syncs', $pendingSyncs)
                ->description($pendingSyncs . ' orders awaiting sync or first attempt')
                ->color('warning'),
        ];

        if ($lastSyncAttempt) {
            array_unshift(
                $stats,
                Stat::make('Last Sync Attempt', $lastSyncAttempt->created_at->diffForHumans())
                    ->description(
                        'Order ID: ' . $lastSyncAttempt->order_id . ' | Status: ' . ucfirst($lastSyncAttempt->status),
                    )
                    ->color(
                        match ($lastSyncAttempt->status) {
                            'success' => 'success',
                            'error' => 'danger',
                            'pending' => 'warning',
                            default => 'gray'
                        },
                    ),
            );
        } else {
            array_unshift(
                $stats,
                Stat::make('Last Sync Attempt', 'N/A')
                    ->description('No order sync attempts recorded yet.')
                    ->color('gray'),
            );
        }

        return $stats;
    }
}
