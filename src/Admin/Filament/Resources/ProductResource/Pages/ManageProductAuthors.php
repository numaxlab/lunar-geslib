<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Resources\ProductResource\Pages;

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
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\AuthorResource\Pages\ManageAuthorProducts;
use NumaxLab\Lunar\Geslib\Models\Author;
use NumaxLab\Lunar\Geslib\Models\Contracts\Author as AuthorContract;

class ManageProductAuthors extends BaseManageRelatedRecords
{
    protected static string $resource = ProductResource::class;

    protected static string $relationship = 'contributors';

    #[\Override]
    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::customers');
    }

    #[\Override]
    public static function getNavigationLabel(): string
    {
        return __('lunar-geslib::product.pages.authors.label');
    }

    #[\Override]
    public function getTitle(): string
    {
        return __('lunar-geslib::product.pages.authors.label');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('author_type')
                    ->label(
                        __('lunar-geslib::author.pages.products.actions.attach.form.author_type.label'),
                    )
                    ->formatStateUsing(fn (string $state): string => ManageAuthorProducts::formatAuthorType($state)),
                Tables\Columns\TextColumn::make('position'),
            ])
            ->actions([
                DetachAction::make()
                    ->action(function (Model $record, Table $table): void {
                        $relationship = Relation::noConstraints(fn (
                        ): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation|null
                            => $table->getRelationship());

                        $relationship->detach($record);

                        Notification::make()
                            ->success()
                            ->body(__('lunar-geslib::author.pages.products.actions.detach.notification.success'))
                            ->send();
                    }),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label(
                        __('lunar-geslib::product.pages.authors.actions.attach.label'),
                    )
                    ->form([
                        Forms\Components\Select::make('recordId')
                            ->label(
                                __('lunar-geslib::product.pages.authors.actions.attach.form.record_id.label'),
                            )
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing(
                                static fn (Forms\Components\Select $component, string $search): array
                                    => Author::search($search)
                                    ->get()
                                    ->mapWithKeys(
                                        fn (AuthorContract $author): array
                                            => [
                                            $author->getKey() => $author->name,
                                        ],
                                    )
                                    ->all(),
                            ),
                        Forms\Components\Select::make('authorType')
                            ->label(
                                __('lunar-geslib::author.pages.products.actions.attach.form.author_type.label'),
                            )
                            ->options(ManageAuthorProducts::authorTypeOptions())
                            ->required(),
                        Forms\Components\TextInput::make('position')
                            ->label(
                                __('lunar-geslib::product.pages.authors.actions.attach.form.position.label'),
                            )
                            ->numeric()
                            ->required(),
                    ])
                    ->action(function (array $arguments, array $data, Form $form, Table $table): void {
                        $relationship = Relation::noConstraints(fn (
                        ): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation|null
                            => $table->getRelationship());

                        $author = Author::find($data['recordId']);

                        $relationship->attach($author, [
                            'author_type' => $data['authorType'],
                            'position' => $data['position'],
                        ]);

                        Notification::make()
                            ->success()
                            ->body(__('lunar-geslib::product.pages.authors.actions.attach.notification.success'))
                            ->send();
                    }),
            ]);
    }
}
