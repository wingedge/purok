<?php

namespace App\Filament\Resources\Incomes\Schemas;

use App\Support\Finance\IncomeSources;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IncomeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Income Information')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('date')
                            ->required(),
                        TextInput::make('amount')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Select::make('source')
                            ->options(IncomeSources::options())
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
