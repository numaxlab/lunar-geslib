<?php

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Admin\Support\Resources\BaseResource;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\TrustedStockProviderResource\Pages;
use NumaxLab\Lunar\Geslib\Models\TrustedStockProvider;

class TrustedStockProviderResource extends BaseResource
{
    protected static ?string $permission = 'catalog:manage-products';

    protected static ?string $model = TrustedStockProvider::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 4;

    public static function getLabel(): string
    {
        return __('lunar-geslib::trusted-stock-provider.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunar-geslib::trusted-stock-provider.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::shipping-methods');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.catalog');
    }

    public static function getDefaultForm(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('lunar-geslib::trusted-stock-provider.form.name.label'))
                        ->required()
                        ->maxLength(255)
                        ->autofocus(),
                    Forms\Components\TextInput::make('sinli_id')
                        ->label(__('lunar-geslib::trusted-stock-provider.form.sinli_id.label'))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('delivery_period')
                        ->label(__('lunar-geslib::trusted-stock-provider.form.delivery_period.label'))
                        ->required()
                        ->maxLength(255),
                ]),
        ])->columns(1);
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('lunar-geslib::trusted-stock-provider.table.name.label'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('sinli_id')
                    ->label(__('lunar-geslib::trusted-stock-provider.table.sinli_id.label')),
            ])->defaultSort('sort_position')
            ->reorderable('sort_position');
    }

    public static function getDefaultPages(): array
    {
        return [
            'index' => Pages\ListTrustedStockProviders::route('/'),
            'create' => Pages\CreateTrustedStockProvider::route('/create'),
            'edit' => Pages\EditTrustedStockProvider::route('/{record}/edit'),
        ];
    }
}
