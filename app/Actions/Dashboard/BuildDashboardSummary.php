<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Models\Expense;
use App\Models\Income;
use App\Models\Member;
use App\Models\Rental;
use App\Services\ContributionService;

final class BuildDashboardSummary
{
    public function __construct(
        private readonly ContributionService $contributions,
    ) {
    }

    /**
     * @return array<string, float|int>
     */
    public function execute(int $year, ?int $month = null): array
    {
        $totalMembers = Member::count();

        $totalIncomes = (float) Income::query()
            ->whereYear('date', $year)
            ->when($month, fn ($query) => $query->whereMonth('date', $month))
            ->sum('amount');

        $totalContributions = $this->contributions->totalForAccountingPeriod($year, $month);
        $thisYearContributions = $this->contributions->totalForAccountingPeriod($year);
        $recentContributions = $this->contributions->recentRecordedTotal();

        $totalExpenses = (float) Expense::query()
            ->whereYear('date', $year)
            ->when($month, fn ($query) => $query->whereMonth('date', $month))
            ->sum('amount');

        $contributorsCount = $this->contributions->contributorCountForAccountingPeriod($year, $month);

        $totalRentals = Rental::query()
            ->whereYear('created_at', $year)
            ->when($month, fn ($query) => $query->whereMonth('created_at', $month))
            ->count();

        return [
            'totalMembers' => $totalMembers,
            'totalIncomes' => $totalIncomes,
            'totalContributions' => $totalContributions,
            'thisYearContributions' => $thisYearContributions,
            'recentContributions' => $recentContributions,
            'totalExpenses' => $totalExpenses,
            'contributorsCount' => $contributorsCount,
            'totalRentals' => $totalRentals,
            'totalFunds' => ($totalIncomes + $totalContributions) - $totalExpenses,
        ];
    }
}
