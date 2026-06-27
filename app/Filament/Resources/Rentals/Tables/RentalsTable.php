<?php

namespace App\Filament\Resources\Rentals\Tables;

use App\Actions\Rentals\ReturnRental;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RentalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('inventory.item_name')
                    ->label('Item')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('renter_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->sortable(),
                TextColumn::make('income.amount')
                    ->label('Amount')
                    ->money('PHP')
                    ->sortable(),
                TextColumn::make('rent_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('return_date')
                    ->date()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'returned' ? 'success' : 'warning')
                    ->sortable(),
            ])
            ->defaultSort('rent_date', 'desc')
            ->recordActions([
                Action::make('return')
                    ->label('Mark Returned')
                    ->visible(fn ($record): bool => $record->status === 'rented')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        app(ReturnRental::class)->execute($record);

                        Notification::make()
                            ->title('Rental marked as returned')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ]);
    }
}
