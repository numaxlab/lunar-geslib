<?php

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Resources\AuthorResource\Pages;

use Lunar\Admin\Support\Resources\Pages\ManageUrlsRelatedRecords;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\AuthorResource;
use NumaxLab\Lunar\Geslib\Models\Contracts\Author as AuthorContract;

class ManageAuthorUrls extends ManageUrlsRelatedRecords
{
    protected static string $resource = AuthorResource::class;

    protected static string $model = AuthorContract::class;
}
