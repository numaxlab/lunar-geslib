<?php

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;

class ManageProductAuthors extends BaseManageRelatedRecords
{
    protected static string $resource = ProductResource::class;

    protected static string $relationship = 'authors';

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::customers');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunar-geslib::product.pages.authors.label');
    }

    public function getTitle(): string
    {
        return __('lunar-geslib::product.pages.authors.label');
    }

    public function table(Table $table): Table
    {
        return $table;
    }
}