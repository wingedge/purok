<?php

namespace App\Filament\Resources\Incomes\Schemas;

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
                            ->options(self::sources())
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
    private static function sources(): array
    {
        return collect([
            'Rentals - Chairs / Table rental',
            'Donation / Fund Drive',
            'Commission / Incentive',
            'Government Aid',
            'Penalties',
            'Misc',
            'Cash on Hand',
        ])->mapWithKeys(fn (string $source): array => [$source => $source])->all();
    }
}
