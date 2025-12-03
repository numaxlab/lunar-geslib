<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Resources\AuthorResource\Pages;

use Lunar\Admin\Support\Pages\BaseCreateRecord;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\AuthorResource;

class CreateAuthor extends BaseCreateRecord
{
    protected static string $resource = AuthorResource::class;

    #[\Override]
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
