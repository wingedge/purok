<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\Models\Member;
use App\Services\ContributionService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class BuildContributionReport
{
    public function __construct(
        private readonly ContributionService $contributions,
    ) {
    }

    /**
     * @return array{
     *     year: int,
     *     start: Carbon,
     *     end: Carbon,
     *     weeks: Collection<int, Carbon>,
     *     members: Collection<int, Member>,
     *     memberTotals: array<int, float>,
     *     reportTotal: float
     * }
     */
    public function execute(int $year, ?int $startMonth = null, ?int $endMonth = null, ?string $month = null): array
    {
        [$start, $end] = $this->dateRange($year, $startMonth, $endMonth, $month);
        $weeks = $this->contributions->sundaysBetween($start, $end);
        $weekDates = $weeks->map->toDateString();

        $members = Member::query()
            ->where('indigent', false)
            ->with(['contributions' => function ($query) use ($weekDates): void {
                $query->whereIn('week_start', $weekDates);
            }])
            ->orderBy('name')
            ->get();

        $memberTotals = [];
        $reportTotal = 0.0;

        foreach ($members as $member) {
            $total = (float) $member->contributions->sum('amount');
            $memberTotals[$member->id] = $total;
            $reportTotal += $total;
        }

        return [
            'year' => $start->year,
            'start' => $start,
            'end' => $end,
            'weeks' => $weeks,
            'members' => $members,
            'memberTotals' => $memberTotals,
            'reportTotal' => $reportTotal,
        ];
    }

    /**
     * @return array{Carbon, Carbon}
     */
    private function dateRange(int $year, ?int $startMonth, ?int $endMonth, ?string $month): array
    {
        if ($startMonth !== null && $endMonth !== null) {
            $startMonth = max(1, min(12, $startMonth));
            $endMonth = max(1, min(12, $endMonth));

            if ($startMonth > $endMonth) {
                [$startMonth, $endMonth] = [$endMonth, $startMonth];
            }

            return [
                Carbon::create($year, $startMonth, 1)->startOfMonth(),
                Carbon::create($year, $endMonth, 1)->endOfMonth(),
            ];
        }

        if ($month !== null && $month !== '') {
            $start = Carbon::parse($month)->startOfMonth();

            return [$start, $start->copy()->endOfMonth()];
        }

        return [
            Carbon::create($year, 1, 1)->startOfYear(),
            Carbon::create($year, 12, 31)->endOfYear(),
        ];
    }
}
