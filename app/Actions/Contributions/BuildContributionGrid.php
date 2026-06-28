<?php

declare(strict_types=1);

namespace App\Actions\Contributions;

use App\Models\Member;
use App\Services\ContributionService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class BuildContributionGrid
{
    public function __construct(
        private readonly ContributionService $contributions,
    ) {
    }

    /**
     * @return array{
     *     weeks: Collection<int, Carbon>,
     *     members: Collection<int, Member>
     * }
     */
    public function execute(string $viewType, int $year, int $month, ?string $search = null): array
    {
        [$start, $end] = $this->dateRange($viewType, $year, $month);
        $weeks = $this->contributions->sundaysBetween($start, $end);
        $weekDates = $weeks->map->toDateString();

        $members = Member::query()
            ->where('indigent', false)
            ->when($search, function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->with(['contributions' => function ($query) use ($weekDates): void {
                $query->whereIn('week_start', $weekDates);
            }])
            ->withSum(['contributions as year_total' => function ($query) use ($year): void {
                $query->whereYear('week_start', $year);
            }], 'amount')
            ->orderBy('name')
            ->get();

        return [
            'weeks' => $weeks,
            'members' => $members,
        ];
    }

    /**
     * @return array{Carbon, Carbon}
     */
    private function dateRange(string $viewType, int $year, int $month): array
    {
        if ($viewType === 'year') {
            $start = Carbon::create($year, 1, 1)->startOfYear();

            return [$start, $start->copy()->endOfYear()];
        }

        $start = Carbon::create($year, $month, 1)->startOfMonth();

        return [$start, $start->copy()->endOfMonth()];
    }
}
