<?php

namespace App\Filament\Resources\Expenses\Schemas;

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
                            ->options(self::categories())
                            ->searchable()
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * @return array<string, string>
     */
    private static function categories(): array
    {
        return collect([
            'Operating Expenses	Supplies, Utility Bills, Communication',
            'Community Services	Health Programs, Feeding, Cleanup Drives',
            'Social Benefits, Burial Aid, Emergency Medical Assistance',
            'Activities, Peace & Security	Patrol Supplies, Volunteers Meals',
            'Special Projects, Fiesta/Christmas Events, Minor Repairs',
            'Misc',
        ])->mapWithKeys(fn (string $category): array => [$category => $category])->all();
    }
}
