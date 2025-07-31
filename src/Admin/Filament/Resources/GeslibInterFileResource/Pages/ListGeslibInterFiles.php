<?php

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Resources\GeslibInterFileResource\Pages;

use Filament\Resources\Pages\ListRecords;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\GeslibInterFileResource;

class ListGeslibInterFiles extends ListRecords
{
    protected static string $resource = GeslibInterFileResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
