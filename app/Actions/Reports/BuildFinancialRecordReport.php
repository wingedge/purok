<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\Enums\FinancialReportType;
use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

final class BuildFinancialRecordReport
{
    /**
     * @return array{start: Carbon, end: Carbon, records: Collection<int, Expense|Income>, total: float}
     */
    public function execute(FinancialReportType $type, int $year, ?int $month = null): array
    {
        $year = max(1, $year);
        $month = $month !== null ? max(1, min(12, $month)) : null;
        $start = Carbon::create($year, $month ?? 1, 1)->startOf($month === null ? 'year' : 'month');
        $end = $start->copy()->endOf($month === null ? 'year' : 'month');
        $model = $type === FinancialReportType::Expense ? Expense::class : Income::class;

        /** @var Collection<int, Expense|Income> $records */
        $records = $model::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        return [
            'start' => $start,
            'end' => $end,
            'records' => $records,
            'total' => (float) $records->sum('amount'),
        ];
    }
}
