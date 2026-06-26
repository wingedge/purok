<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Contribution;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ContributionService
{
    public function amountFor(Member $member): float
    {
        return $member->indigent ? 0.00 : 10.00;
    }

    /**
     * @return Collection<int, Carbon>
     */
    public function sundaysBetween(Carbon $start, Carbon $end): Collection
    {
        $weeks = collect();
        $cursor = $start->copy()->startOfWeek(Carbon::SUNDAY);

        if ($cursor->lt($start)) {
            $cursor->addWeek();
        }

        while ($cursor->lte($end)) {
            $weeks->push($cursor->copy());
            $cursor->addWeek();
        }

        return $weeks;
    }

    /**
     * @return Builder<Contribution>
     */
    public function queryForAccountingPeriod(int $year, ?int $month = null): Builder
    {
        return Contribution::query()
            ->whereYear('week_start', $year)
            ->when($month, fn (Builder $query): Builder => $query->whereMonth('week_start', $month));
    }

    public function totalForAccountingPeriod(int $year, ?int $month = null): float
    {
        return (float) $this->queryForAccountingPeriod($year, $month)->sum('amount');
    }

    public function contributorCountForAccountingPeriod(int $year, ?int $month = null): int
    {
        return $this->queryForAccountingPeriod($year, $month)
            ->distinct('member_id')
            ->count('member_id');
    }

    public function recentRecordedTotal(int $days = 7): float
    {
        return (float) Contribution::query()
            ->where('created_at', '>=', now()->subDays($days))
            ->sum('amount');
    }
}
