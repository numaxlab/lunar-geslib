<?php

namespace NumaxLab\Lunar\Geslib\Filament\Resources\GeslibFileInterResource\Pages;

use NumaxLab\Lunar\Geslib\Filament\Resources\GeslibFileInterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGeslibInterFiles extends ListRecords
{
    protected static string $resource = GeslibFileInterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(), // No create action for this log table
        ];
    }
}
