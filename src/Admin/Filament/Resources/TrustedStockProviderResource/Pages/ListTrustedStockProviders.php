<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Resources\TrustedStockProviderResource\Pages;

use Filament\Actions;
use Lunar\Admin\Support\Pages\BaseListRecords;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\TrustedStockProviderResource;

class ListTrustedStockProviders extends BaseListRecords
{
    protected static string $resource = TrustedStockProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
