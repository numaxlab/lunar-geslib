<?php

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Extension;

use Lunar\Admin\Support\Extending\ResourceExtension;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\ProductResource\Pages;

class ProductResourceExtension extends ResourceExtension
{
    public function extendPages(array $pages): array
    {
        return [
            ...$pages,
            'authors' => Pages\ManageProductAuthors::route('/{record}/authors'),
        ];
    }

    public function extendSubNavigation(array $nav): array
    {
        return [
            ...$nav,
            //Pages\ManageProductAuthors::class,
        ];
    }
}
