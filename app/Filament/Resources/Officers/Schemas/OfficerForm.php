<?php

declare(strict_types=1);

namespace App\Filament\Resources\Officers\Schemas;

use App\Models\Member;
use App\Support\Community\OfficerPositions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OfficerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Officer Information')
                    ->columns(2)
                    ->schema([
                        Select::make('member_id')
                            ->label('Member')
                            ->relationship('member', 'name')
                            ->getSearchResultsUsing(fn (string $search): array => Member::query()
                                ->where('name', 'like', "%{$search}%")
                                ->orderBy('name')
                                ->limit(20)
                                ->pluck('name', 'id')
                                ->all())
                            ->getOptionLabelUsing(fn ($value): ?string => Member::query()->find($value)?->name)
                            ->searchable()
                            ->required(),
                        Select::make('position')
                            ->options(OfficerPositions::options())
                            ->searchable()
                            ->required(),
                        DatePicker::make('term_start')
                            ->label('Term start'),
                        DatePicker::make('term_end')
                            ->label('Term end')
                            ->afterOrEqual('term_start'),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Textarea::make('notes')
                            ->maxLength(65535)
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
