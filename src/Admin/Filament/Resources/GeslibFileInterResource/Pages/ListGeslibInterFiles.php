<?php

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Resources\GeslibFileInterResource\Pages;

use Filament\Resources\Pages\ListRecords;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\GeslibFileInterResource;

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
