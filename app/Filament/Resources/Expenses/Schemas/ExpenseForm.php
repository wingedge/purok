<?php

namespace App\Filament\Resources\Expenses\Schemas;

use App\Support\Finance\ExpenseCategories;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Expense Information')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('date')
                            ->required(),
                        TextInput::make('amount')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Select::make('category')
                            ->options(ExpenseCategories::options())
                            ->searchable()
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
