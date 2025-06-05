<?php

namespace NumaxLab\Lunar\Geslib\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use NumaxLab\Lunar\Geslib\Models\LunarGeslibOrderSyncLog;

class GeslibOrderSyncLogResource extends Resource
{
    protected static ?string $model = LunarGeslibOrderSyncLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Geslib Integration';

    protected static ?string $pluralModelLabel = 'Geslib Order Sync Logs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_id')
                    ->label('Order ID')
                    ->disabled(),
                Forms\Components\TextInput::make('geslib_endpoint_called')
                    ->label('Endpoint Called')
                    ->disabled(),
                Forms\Components\TextInput::make('status')
                    ->disabled(),
                Forms\Components\Textarea::make('message')
                    ->columnSpanFull()
                    ->disabled(),
                Forms\Components\Textarea::make('payload_to_geslib')
                    ->label('Payload to Geslib')
                    ->columnSpanFull()
                    ->disabled(),
                Forms\Components\Textarea::make('payload_from_geslib')
                    ->label('Payload from Geslib')
                    ->columnSpanFull()
                    ->disabled(),
                Forms\Components\DateTimePicker::make('created_at')
                    ->label('Logged At')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_id')
                    ->label('Order ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('geslib_endpoint_called')
                    ->label('Endpoint')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->geslib_endpoint_called),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'error' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('message')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->message),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Logged At'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'success' => 'Success',
                        'error' => 'Error',
                    ]),
                // Consider adding a date range filter if needed
                // Tables\Filters\Filter::make('created_at')
                //     ->form([
                //         Forms\Components\DatePicker::make('created_from'),
                //         Forms\Components\DatePicker::make('created_until'),
                //     ])
                //     ->query(function (Builder $query, array $data): Builder {
                //         return $query
                //             ->when(
                //                 $data['created_from'],
                //                 fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                //             )
                //             ->when(
                //                 $data['created_until'],
                //                 fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                //             );
                //     })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(), // Uses the form schema for a modal view
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
            'index' => Pages\ListGeslibOrderSyncLogs::route('/'),
            // ViewAction uses a modal by default, so a dedicated view page is often not needed
            // 'view' => Pages\ViewGeslibOrderSyncLog::route('/{record}'),
        ];
    }
}
