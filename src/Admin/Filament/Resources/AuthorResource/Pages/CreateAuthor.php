<?php

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Resources\AuthorResource\Pages;

use Lunar\Admin\Support\Pages\BaseCreateRecord;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\AuthorResource;

class CreateAuthor extends BaseCreateRecord
{
    protected static string $resource = AuthorResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
