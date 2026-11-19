<?php

declare(strict_types=1);

namespace App\Filament\Resources\Contributions\Schemas;

use App\Actions\Contributions\RecordContributionPayment;
use App\Models\Member;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ContributionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contribution')
                    ->columns(2)
                    ->schema([
                        Select::make('member_id')
                            ->label('Member')
                            ->options(fn (string $operation): array => Member::query()
                                ->when($operation === 'create', fn ($query) => $query->where('indigent', false))
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->live()
                            ->required(),
                        DatePicker::make('week_start')
                            ->label('Week Start')
                            ->required()
                            ->visibleOn('edit'),
                        Select::make('contribution_year')
                            ->label('Contribution Year')
                            ->options(fn (): array => collect(range(now()->year + 1, now()->year - 5))
                                ->mapWithKeys(fn (int $year): array => [$year => $year])
                                ->all())
                            ->default(fn (): int => now()->year)
                            ->live()
                            ->required()
                            ->visibleOn('create'),
                        TextEntry::make('next_paid_week')
                            ->label('Next Unpaid Sunday')
                            ->state(fn (Get $get): string => self::paymentSummary($get)['next_week'] ?? 'Select a member and contribution year.')
                            ->visibleOn('create'),
                        TextEntry::make('remaining_balance')
                            ->label('Remaining Balance')
                            ->state(fn (Get $get): string => self::paymentSummary($get)['balance'] ?? 'Select a member and contribution year.')
                            ->visibleOn('create'),
                        TextInput::make('payment_amount')
                            ->label('Payment Amount')
                            ->prefix('PHP')
                            ->helperText('PHP 100.00 records 10 consecutive paid weeks at PHP 10.00 per week.')
                            ->default(10)
                            ->numeric()
                            ->minValue(10)
                            ->multipleOf(10)
                            ->step(10)
                            ->required()
                            ->visibleOn('create'),
                        Textarea::make('remarks')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * @return array{next_week: string, balance: string}|null
     */
    private static function paymentSummary(Get $get): ?array
    {
        $memberId = $get('member_id');
        $year = $get('contribution_year');

        if (blank($memberId) || blank($year)) {
            return null;
        }

        $member = Member::query()->where('indigent', false)->find($memberId);

        if ($member === null) {
            return null;
        }

        $summary = app(RecordContributionPayment::class)->summary($member, (int) $year);

        return [
            'next_week' => $summary['next_week']?->format('M d, Y') ?? "Fully paid for {$year}",
            'balance' => sprintf('PHP %.2f (%d weeks)', $summary['remaining_balance'], $summary['remaining_weeks']),
        ];
    }
}
