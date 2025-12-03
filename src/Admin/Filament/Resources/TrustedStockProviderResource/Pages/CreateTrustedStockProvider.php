<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Resources\TrustedStockProviderResource\Pages;

use Lunar\Admin\Support\Pages\BaseCreateRecord;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\TrustedStockProviderResource;

class CreateTrustedStockProvider extends BaseCreateRecord
{
    protected static string $resource = TrustedStockProviderResource::class;

    #[\Override]
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
