<?php

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Resources\GeslibOrderSyncLogResource\Pages;

use Filament\Resources\Pages\ListRecords;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\GeslibOrderSyncLogResource;

class ListGeslibOrderSyncLogs extends ListRecords
{
    protected static string $resource = GeslibOrderSyncLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(), // No create action for logs
        ];
    }
}
