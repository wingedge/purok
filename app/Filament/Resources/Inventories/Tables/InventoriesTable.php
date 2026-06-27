<?php

namespace App\Filament\Resources\Inventories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InventoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('item_name')
                    ->label('Item')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_quantity')
                    ->label('Total')
                    ->sortable(),
                TextColumn::make('available_quantity')
                    ->label('Available')
                    ->sortable(),
                TextColumn::make('rented_count')
                    ->label('Rented')
                    ->state(fn ($record): int => max(0, $record->total_quantity - $record->available_quantity)),
                TextColumn::make('rental_rate')
                    ->money('PHP')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('item_name')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
