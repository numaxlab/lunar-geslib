<?php

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Resources;

use Filament\Forms\Form;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\GeslibFileInterResource\Pages\ListGeslibInterFiles;
use NumaxLab\Lunar\Geslib\Jobs\ProcessGeslibInterFile;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;

// Alias to avoid conflict

class GeslibFileInterResource extends Resource
{
    protected static ?string $model = GeslibInterFile::class;

    protected static ?string $navigationIcon = 'heroicon-o-command-line';

    protected static ?string $navigationGroup = 'Geslib';

    public static function getPluralModelLabel(): string
    {
        return 'Ficheros de intercambio';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Filename')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        GeslibInterFile::STATUS_SUCCESS => 'success',
                        GeslibInterFile::STATUS_FAILED => 'danger',
                        GeslibInterFile::STATUS_WARNING => 'warning',
                        GeslibInterFile::STATUS_PROCESSING => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('progress'),
                /*Tables\Columns\TextColumn::make('log')
                    ->limit(50)
                    ->tooltip(fn($record) => json_encode($record->log)),*/
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Created at'),
                Tables\Columns\TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Started at'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Last updated at'),
                Tables\Columns\TextColumn::make('finished_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Finished at'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'processed' => 'Processed',
                        'error' => 'Error',
                        'archived' => 'Archived',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('reprocess')
                    ->label('Reprocess')
                    ->icon('heroicon-s-arrow-path')
                    ->color('info')
                    ->visible(fn(GeslibInterFile $record): bool => $record->status === 'error')
                    ->requiresConfirmation()
                    ->action(function (GeslibInterFile $record) {
                        ProcessGeslibInterFile::dispatch($record);
                        FilamentNotification::make()
                            ->title('Reprocess Initiated')
                            ->body('File ' . $record->name . ' has been queued for reprocessing.')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGeslibInterFiles::route('/'),
        ];
    }
}
