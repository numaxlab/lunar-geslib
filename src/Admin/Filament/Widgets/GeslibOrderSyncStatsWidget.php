<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GeslibOrderSyncStatsWidget extends BaseWidget
{
    #[\Override]
    protected function getStats(): array
    {
        $lastSyncedOrder = null;

        $stats = [
            Stat::make('Total Order Synced', 0)
                ->description('All order synchronization attempts logged')
                ->color('success'),
            Stat::make('Pending Syncs', 0)
                ->description(0 .' orders awaiting sync or first attempt')
                ->color('warning'),
        ];

        if ($lastSyncedOrder) {
            array_unshift(
                $stats,
                Stat::make('Last Synced', $lastSyncedOrder->synced_with_geslib_at->diffForHumans())
                    ->description(
                        'Order ID: '.$lastSyncedOrder->order_id,
                    )
                    ->color('gray'),
            );
        } else {
            array_unshift(
                $stats,
                Stat::make('Last Synced', 'N/A')
                    ->description('No order syncs recorded yet.')
                    ->color('gray'),
            );
        }

        return $stats;
    }
}
