<?php

namespace App\Filament\Resources\Rentals\Schemas;

use App\Models\Inventory;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RentalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rental Information')
                    ->columns(2)
                    ->schema([
                        Select::make('inventory_id')
                            ->label('Item')
                            ->options(fn (): array => Inventory::query()
                                ->orderBy('item_name')
                                ->get()
                                ->mapWithKeys(fn (Inventory $inventory): array => [
                                    $inventory->id => "{$inventory->item_name} ({$inventory->available_quantity} available)",
                                ])
                                ->all())
                            ->searchable()
                            ->required()
                            ->disabled(fn (?string $operation): bool => $operation === 'edit')
                            ->dehydrated(fn (?string $operation): bool => $operation !== 'edit')
                            ->columnSpanFull(),
                        TextInput::make('renter_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('renter_contact')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('quantity')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->required(),
                        TextInput::make('amount')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->default(fn ($record): ?string => $record?->income?->amount),
                        DatePicker::make('rent_date')
                            ->required(),
                        Select::make('status')
                            ->options([
                                'rented' => 'Rented',
                                'returned' => 'Returned',
                            ])
                            ->default('rented')
                            ->required()
                            ->visible(fn (?string $operation): bool => $operation === 'edit'),
                    ]),
            ]);
    }
}
