<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Resources\AuthorResource\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Lunar\Models\Contracts\Product as ProductContract;
use Lunar\Models\Product;
use NumaxLab\Geslib\Lines\AuthorType;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\AuthorResource;

class ManageAuthorProducts extends BaseManageRelatedRecords
{
    protected static string $resource = AuthorResource::class;

    protected static string $relationship = 'products';

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::products');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunar-geslib::author.pages.products.label');
    }

    public function getTitle(): string
    {
        return __('lunar-geslib::author.pages.products.label');
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            ProductResource::getNameTableColumn()->searchable()
                ->url(fn(Model $record): string => ProductResource::getUrl('edit', [
                    'record' => $record->getKey(),
                ])),
            ProductResource::getSkuTableColumn(),
            Tables\Columns\TextColumn::make('author_type')
                ->label(
                    __('lunar-geslib::author.pages.products.actions.attach.form.author_type.label'),
                )
                ->formatStateUsing(fn(string $state): string => static::formatAuthorType($state)),
        ])->actions([
            DetachAction::make()
                ->action(function (Model $record, Table $table): void {
                    $relationship = Relation::noConstraints(fn(
                    ): \Illuminate\Database\Eloquent\Relations\Relation|\Illuminate\Database\Eloquent\Builder|null
                        => $table->getRelationship());

                    $relationship->detach($record);

                    Notification::make()
                        ->success()
                        ->body(__('lunar-geslib::author.pages.products.actions.detach.notification.success'))
                        ->send();
                }),
        ])->headerActions([
            AttachAction::make()
                ->label(
                    __('lunar-geslib::author.pages.products.actions.attach.label'),
                )
                ->form([
                    Forms\Components\Select::make('recordId')
                        ->label(
                            __('lunar-geslib::author.pages.products.actions.attach.form.record_id.label'),
                        )
                        ->required()
                        ->searchable()
                        ->getSearchResultsUsing(
                            static fn(Forms\Components\Select $component, string $search): array
                                => Product::search($search)
                                ->get()
                                ->mapWithKeys(
                                    fn(ProductContract $record): array
                                        => [
                                        $record->getKey() => $record->translateAttribute('name'),
                                    ],
                                )
                                ->all(),
                        ),
                    Forms\Components\Select::make('authorType')
                        ->label(
                            __('lunar-geslib::author.pages.products.actions.attach.form.author_type.label'),
                        )
                        ->options(static::authorTypeOptions())
                        ->required(),
                ])
                ->action(function (array $arguments, array $data, Form $form, Table $table): void {
                    $relationship = Relation::noConstraints(fn(
                    ): \Illuminate\Database\Eloquent\Relations\Relation|\Illuminate\Database\Eloquent\Builder|null
                        => $table->getRelationship());

                    $product = Product::find($data['recordId']);

                    $relationship->attach($product, [
                        'author_type' => $data['authorType'],
                        'position' => $relationship->count() + 1,
                    ]);

                    Notification::make()
                        ->success()
                        ->body(__('lunar-geslib::author.pages.products.actions.attach.notification.success'))
                        ->send();
                }),
        ]);
    }

    public static function formatAuthorType(string $authorType): string
    {
        return match ($authorType) {
            AuthorType::AUTHOR => __(
                'lunar-geslib::author.pages.products.actions.attach.form.author_type.options.author',
            ),
            AuthorType::TRANSLATOR => __(
                'lunar-geslib::author.pages.products.actions.attach.form.author_type.options.translator',
            ),
            AuthorType::ILLUSTRATOR => __(
                'lunar-geslib::author.pages.products.actions.attach.form.author_type.options.illustrator',
            ),
            AuthorType::COVER_ILLUSTRATOR => __(
                'lunar-geslib::author.pages.products.actions.attach.form.author_type.options.cover_illustrator',
            ),
            AuthorType::BACK_COVER_ILLUSTRATOR => __(
                'lunar-geslib::author.pages.products.actions.attach.form.author_type.options.back_cover_illustrator',
            ),
            default => '',
        };
    }

    public static function authorTypeOptions(): array
    {
        return [
            AuthorType::AUTHOR => __(
                'lunar-geslib::author.pages.products.actions.attach.form.author_type.options.author',
            ),
            AuthorType::TRANSLATOR => __(
                'lunar-geslib::author.pages.products.actions.attach.form.author_type.options.translator',
            ),
            AuthorType::ILLUSTRATOR => __(
                'lunar-geslib::author.pages.products.actions.attach.form.author_type.options.illustrator',
            ),
            AuthorType::COVER_ILLUSTRATOR => __(
                'lunar-geslib::author.pages.products.actions.attach.form.author_type.options.cover_illustrator',
            ),
            AuthorType::BACK_COVER_ILLUSTRATOR => __(
                'lunar-geslib::author.pages.products.actions.attach.form.author_type.options.back_cover_illustrator',
            ),
        ];
    }
}
