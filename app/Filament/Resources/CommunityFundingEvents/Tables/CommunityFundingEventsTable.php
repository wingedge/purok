<?php

declare(strict_types=1);

namespace App\Filament\Resources\CommunityFundingEvents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CommunityFundingEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('deadline')
                    ->date()
                    ->sortable(),
                TextColumn::make('goal_amount')
                    ->label('Goal')
                    ->money('PHP')
                    ->sortable(),
                TextColumn::make('actual_amount')
                    ->label('Actual')
                    ->money('PHP')
                    ->state(fn ($record): float => $record->actual_amount),
                TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->suffix('%')
                    ->state(fn ($record): string => number_format($record->progress_percentage, 2)),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('deadline', 'asc')
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
