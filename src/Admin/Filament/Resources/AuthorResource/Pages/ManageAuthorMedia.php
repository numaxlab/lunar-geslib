<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Resources\AuthorResource\Pages;

use Lunar\Admin\Support\Resources\Pages\ManageMediasRelatedRecords;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\AuthorResource;

class ManageAuthorMedia extends ManageMediasRelatedRecords
{
    protected static string $resource = AuthorResource::class;
}
