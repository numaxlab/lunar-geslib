<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Extension;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Lunar\Admin\Support\Extending\RelationManagerExtension;

class ManageProductCollectionsExtension extends RelationManagerExtension
{
    #[\Override]
    public function extendTable(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('group.name')
                ->label('Grupo'),
        ]);
    }
}
