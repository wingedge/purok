<?php

namespace App\Filament\Resources\PurokCertificates\Schemas;

use App\Models\Member;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PurokCertificateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Certificate Log')
                    ->schema([
                        Select::make('member_id')
                            ->label('Member or Household')
                            ->relationship('member', 'name')
                            ->getSearchResultsUsing(fn (string $search): array => Member::query()
                                ->where('name', 'like', "%{$search}%")
                                ->orWhereHas('dependents', fn (Builder $query): Builder => $query->where('name', 'like', "%{$search}%"))
                                ->with('dependents')
                                ->limit(20)
                                ->get()
                                ->mapWithKeys(fn (Member $member): array => [
                                    $member->id => $member->dependents->isEmpty()
                                        ? $member->name
                                        : $member->name.' - Dependents: '.$member->dependents->pluck('name')->implode(', '),
                                ])
                                ->all())
                            ->getOptionLabelUsing(fn ($value): ?string => Member::query()->find($value)?->name)
                            ->searchable()
                            ->required(),
                        DatePicker::make('request_date')
                            ->required()
                            ->default(now()),
                        Textarea::make('purpose')
                            ->required()
                            ->maxLength(1000)
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
