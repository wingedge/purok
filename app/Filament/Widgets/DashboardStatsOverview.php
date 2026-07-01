<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Actions\Dashboard\BuildDashboardSummary;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected ?string $heading = 'Purok Summary';

    protected ?string $description = 'Current year totals.';

    protected function getStats(): array
    {
        $summary = app(BuildDashboardSummary::class)->execute(now()->year);

        return [
            Stat::make('Members', number_format($summary['totalMembers']))
                ->color('gray'),
            Stat::make('Current Funds', $this->money($summary['totalFunds']))
                ->color($summary['totalFunds'] >= 0 ? 'success' : 'danger'),
            Stat::make('Total Inflow', $this->money(
                $summary['totalIncomes'] + $summary['totalContributions'] + $summary['totalCommunityFunding']
            ))
                ->description('For '.now()->year)
                ->color('success'),
            Stat::make('Expenses', $this->money($summary['totalExpenses']))
                ->description('For '.now()->year)
                ->color('danger'),
            Stat::make('Contributors', number_format($summary['contributorsCount']))
                ->description('For '.now()->year)
                ->color('info'),
            Stat::make('Rentals', number_format($summary['totalRentals']))
                ->description('For '.now()->year)
                ->color('warning'),
            Stat::make('Recent Contributions', $this->money($summary['recentContributions']))
                ->description('Last 7 days')
                ->color('success'),
        ];
    }

    private function money(float|int $amount): string
    {
        return 'PHP '.number_format((float) $amount, 2);
    }
}
