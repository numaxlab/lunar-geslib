<?php

namespace NumaxLab\Lunar\Geslib\Filament\Resources\GeslibOrderSyncLogResource\Pages;

use NumaxLab\Lunar\Geslib\Filament\Resources\GeslibOrderSyncLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
