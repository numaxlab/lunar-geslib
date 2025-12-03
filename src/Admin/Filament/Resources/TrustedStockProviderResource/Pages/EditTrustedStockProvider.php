<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Resources\TrustedStockProviderResource\Pages;

use Filament\Actions;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\TrustedStockProviderResource;

class EditTrustedStockProvider extends BaseEditRecord
{
    protected static string $resource = TrustedStockProviderResource::class;

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::product.pages.edit.title');
    }

    public function getTitle(): string
    {
        return __('lunar-geslib::author.pages.edit.title');
    }

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
