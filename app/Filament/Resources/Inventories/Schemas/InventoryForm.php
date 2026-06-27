<?php

namespace App\Filament\Resources\Inventories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InventoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Inventory Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('item_name')
                            ->label('Item Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('total_quantity')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->required(),
                        TextInput::make('available_quantity')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->required(),
                        TextInput::make('rental_rate')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                    ]),
            ]);
    }
}
