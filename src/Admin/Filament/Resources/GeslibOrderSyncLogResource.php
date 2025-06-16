<?php

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\GeslibOrderSyncLogResource\Pages\ListGeslibOrderSyncLogs;
use NumaxLab\Lunar\Geslib\Models\GeslibOrderSyncLog;

class GeslibOrderSyncLogResource extends Resource
{
    protected static ?string $model = GeslibOrderSyncLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Geslib';

    protected static ?string $pluralModelLabel = 'Order Sync Logs';

    public static function getPluralModelLabel(): string
    {
        return 'Envío de pedidos';
    }

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
                    ->tooltip(fn($record) => $record->geslib_endpoint_called),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'success' => 'success',
                        'error' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('message')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->message),
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGeslibOrderSyncLogs::route('/'),
        ];
    }
}
