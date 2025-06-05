<?php

namespace NumaxLab\Lunar\Geslib\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;
use NumaxLab\Lunar\Geslib\Jobs\ProcessGeslibInterFile;
use Filament\Notifications\Notification as FilamentNotification; // Alias to avoid conflict

class GeslibFileInterResource extends Resource
{
    protected static ?string $model = GeslibInterFile::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Geslib Integration'; // Or 'Settings' or a new custom group

    protected static ?string $pluralModelLabel = 'Geslib Inter Files';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Filename')
                    ->disabled(),
                Forms\Components\TextInput::make('status')
                    ->disabled(),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull()
                    ->disabled(),
                Forms\Components\DateTimePicker::make('created_at')
                     ->label('Imported At')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('updated_at')
                     ->label('Processed At')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name') // Filament uses 'name' for TextColumn by default for the main text
                    ->label('Filename')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'processed' => 'success',
                        'error' => 'danger',
                        'pending' => 'warning',
                        'processing' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->notes), // Show full notes on hover
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Imported At'),
                Tables\Columns\TextColumn::make('updated_at') // This is Laravel's default updated_at
                    ->dateTime()
                    ->sortable()
                    ->label('Last Update'),
                 Tables\Columns\TextColumn::make('finished_at') // Assuming 'finished_at' is when processing ended
                    ->dateTime()
                    ->sortable()
                    ->label('Processed At'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'processed' => 'Processed',
                        'error' => 'Error',
                        'archived' => 'Archived', // Assuming this status might exist
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('reprocess')
                    ->label('Reprocess')
                    ->icon('heroicon-s-arrow-path')
                    ->color('info')
                    ->visible(fn (GeslibInterFile $record): bool => $record->status === 'error')
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
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGeslibInterFiles::route('/'),
            // 'create' => Pages\CreateGeslibFileInter::route('/create'), // No create page for logs
            // 'edit' => Pages\EditGeslibFileInter::route('/{record}/edit'), // No edit page for logs
        ];
    }
}
