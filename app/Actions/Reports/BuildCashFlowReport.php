<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\Models\Expense;
use App\Models\Income;
use App\Services\ContributionService;

final class BuildCashFlowReport
{
    public function __construct(
        private readonly ContributionService $contributions,
    ) {
    }

    /**
     * @return array<string, float>
     */
    public function execute(int $year, ?int $month = null): array
    {
        $incomeTotal = (float) Income::query()
            ->whereYear('date', $year)
            ->when($month, fn ($query) => $query->whereMonth('date', $month))
            ->sum('amount');

        $contributionTotal = $this->contributions->totalForAccountingPeriod($year, $month);

        $expenseTotal = (float) Expense::query()
            ->whereYear('date', $year)
            ->when($month, fn ($query) => $query->whereMonth('date', $month))
            ->sum('amount');

        $totalInflow = $incomeTotal + $contributionTotal;

        return [
            'incomeTotal' => $incomeTotal,
            'contributionTotal' => $contributionTotal,
            'expenseTotal' => $expenseTotal,
            'totalInflow' => $totalInflow,
            'netCashFlow' => $totalInflow - $expenseTotal,
        ];
    }
}
