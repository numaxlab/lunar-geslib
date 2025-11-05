<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Resources;

use Filament\Actions\StaticAction;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\GeslibInterFileResource\Pages\ListGeslibInterFiles;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;

class GeslibInterFileResource extends Resource
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
        return $form->schema([]);
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
                    ->color(fn (string $state): string => match ($state) {
                        GeslibInterFile::STATUS_SUCCESS => 'success',
                        GeslibInterFile::STATUS_FAILED => 'danger',
                        GeslibInterFile::STATUS_WARNING => 'warning',
                        GeslibInterFile::STATUS_PROCESSING => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('progress')
                    ->label('Lines prog.'),
                Tables\Columns\TextColumn::make('batch_lines_count')
                    ->counts('batchLines')
                    ->label('Pend. batches'),
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
                    ->label('Estado')
                    ->options([
                        GeslibInterFile::STATUS_PENDING => 'Pendiente',
                        GeslibInterFile::STATUS_PROCESSING => 'Procesando',
                        GeslibInterFile::STATUS_WARNING => 'Advertencia',
                        GeslibInterFile::STATUS_FAILED => 'Fallo',
                        GeslibInterFile::STATUS_SUCCESS => 'Exitoso',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view_log')
                    ->label('Ver log')
                    ->icon('heroicon-o-eye')
                    ->modalContent(fn ($record,
                    ): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory => view('lunar-geslib::filament.modals.geslib-inter-file-log',
                        [
                            'log' => $record->log,
                        ]))
                    ->modalHeading('Registro de actividad')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn (StaticAction $action,
                    ): \Filament\Actions\StaticAction => $action->label('Cerrar')),
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
