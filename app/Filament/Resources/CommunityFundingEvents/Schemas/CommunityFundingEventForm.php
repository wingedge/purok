<?php

declare(strict_types=1);

namespace App\Filament\Resources\CommunityFundingEvents\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommunityFundingEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Funding Event')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Description or Purpose')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        DatePicker::make('deadline'),
                        TextInput::make('goal_amount')
                            ->label('Goal Amount')
                            ->numeric()
                            ->minValue(0),
                    ]),
            ]);
    }
}
