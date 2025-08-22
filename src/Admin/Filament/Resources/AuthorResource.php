<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Support\Forms\Components\Attributes;
use Lunar\Admin\Support\Resources\BaseResource;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\AuthorResource\Pages;
use NumaxLab\Lunar\Geslib\Models\Contracts\Author as AuthorContract;

class AuthorResource extends BaseResource
{
    protected static ?string $permission = 'catalog:manage-products';

    protected static ?string $model = AuthorContract::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 3;

    protected static int $globalSearchResultsLimit = 5;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    public static function getLabel(): string
    {
        return __('lunar-geslib::author.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunar-geslib::author.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::customers');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.catalog');
    }

    public static function getDefaultSubNavigation(): array
    {
        return [
            Pages\EditAuthor::class,
            Pages\ManageAuthorMedia::class,
            Pages\ManageAuthorUrls::class,
            Pages\ManageAuthorProducts::class,
        ];
    }

    public static function getDefaultForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema(
                        static::getMainFormComponents(),
                    ),
                static::getAttributeDataFormComponent(),
            ])
            ->columns(1);
    }

    protected static function getMainFormComponents(): array
    {
        return [
            static::getNameFormComponent(),
        ];
    }

    protected static function getNameFormComponent(): Component
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('lunar-geslib::author.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getAttributeDataFormComponent(): Component
    {
        return Attributes::make();
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->searchable();
    }

    protected static function getTableColumns(): array
    {
        return [
            SpatieMediaLibraryImageColumn::make('thumbnail')
                ->collection(config('lunar.media.collection'))
                ->conversion('small')
                ->limit(1)
                ->square()
                ->label(''),
            static::getNameTableColumn(),
            Tables\Columns\TextColumn::make('products_count')
                ->counts('products')
                ->formatStateUsing(
                    fn($state): string => number_format($state, 0),
                )
                ->label(__('lunar-geslib::author.table.products_count.label')),
        ];
    }

    public static function getNameTableColumn(): Tables\Columns\Column
    {
        return Tables\Columns\TextColumn::make('name')
            ->label(__('lunar-geslib::author.table.name.label'));
    }

    public static function getDefaultPages(): array
    {
        return [
            'index' => Pages\ListAuthors::route('/'),
            'create' => Pages\CreateAuthor::route('/create'),
            'edit' => Pages\EditAuthor::route('/{record}/edit'),
            'media' => Pages\ManageAuthorMedia::route('/{record}/media'),
            'urls' => Pages\ManageAuthorUrls::route('/{record}/urls'),
            'products' => Pages\ManageAuthorProducts::route('/{record}/products'),
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->name;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
        ];
    }
}
