<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\Models\Expense;
use App\Models\Income;
use App\Services\CommunityFundingService;
use App\Services\ContributionService;

final class BuildCashFlowReport
{
    public function __construct(
        private readonly ContributionService $contributions,
        private readonly CommunityFundingService $communityFunding,
    ) {
    }

    /**
     * @return array{
     *     incomeTotal:float,
     *     contributionTotal:float,
     *     communityFundingTotal:float,
     *     communityFundingEventTotals:array<int, array{name:string, total:float}>,
     *     expenseTotal:float,
     *     totalInflow:float,
     *     netCashFlow:float
     * }
     */
    public function execute(int $year, ?int $month = null): array
    {
        $incomeTotal = (float) Income::query()
            ->whereYear('date', $year)
            ->when($month, fn ($query) => $query->whereMonth('date', $month))
            ->sum('amount');

        $contributionTotal = $this->contributions->totalForAccountingPeriod($year, $month);
        $communityFundingTotal = $this->communityFunding->totalForAccountingPeriod($year, $month);
        $communityFundingEventTotals = $this->communityFunding->eventTotalsForAccountingPeriod($year, $month);

        $expenseTotal = (float) Expense::query()
            ->whereYear('date', $year)
            ->when($month, fn ($query) => $query->whereMonth('date', $month))
            ->sum('amount');

        $totalInflow = $incomeTotal + $contributionTotal + $communityFundingTotal;

        return [
            'incomeTotal' => $incomeTotal,
            'contributionTotal' => $contributionTotal,
            'communityFundingTotal' => $communityFundingTotal,
            'communityFundingEventTotals' => $communityFundingEventTotals,
            'expenseTotal' => $expenseTotal,
            'totalInflow' => $totalInflow,
            'netCashFlow' => $totalInflow - $expenseTotal,
        ];
    }
}
