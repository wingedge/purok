<?php

declare(strict_types=1);

namespace App\Actions\Members;

use App\Models\Member;
use App\Services\ContributionService;
use Carbon\Carbon;

class BuildMemberContributionStatus
{
    public function __construct(
        private readonly ContributionService $contributions,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(Member $member, int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $weeks = $this->contributions->sundaysBetween($start, $end);
        $weekDates = $weeks->map->toDateString();
        $requiredWeeklyAmount = $this->contributions->amountFor($member);

        $monthlyContributions = $member->contributions()
            ->whereIn('week_start', $weekDates)
            ->orderBy('week_start')
            ->get()
            ->keyBy(fn ($contribution): string => $contribution->week_start->toDateString());

        $weeklyStatus = $weeks->map(function (Carbon $week) use ($monthlyContributions): array {
            $contribution = $monthlyContributions->get($week->toDateString());

            return [
                'week_start' => $week,
                'contribution' => $contribution,
                'is_paid' => $contribution !== null,
            ];
        });

        $paidWeeks = $monthlyContributions->count();
        $requiredWeeks = $requiredWeeklyAmount > 0 ? $weeks->count() : 0;
        $monthlyPaidTotal = (float) $monthlyContributions->sum('amount');
        $monthlyExpectedTotal = $requiredWeeks * $requiredWeeklyAmount;

        return [
            'selected_year' => $year,
            'selected_month' => $month,
            'required_weekly_amount' => $requiredWeeklyAmount,
            'weekly_status' => $weeklyStatus,
            'paid_weeks' => $paidWeeks,
            'required_weeks' => $requiredWeeks,
            'unpaid_weeks' => max(0, $requiredWeeks - $paidWeeks),
            'monthly_paid_total' => $monthlyPaidTotal,
            'monthly_expected_total' => $monthlyExpectedTotal,
            'monthly_balance' => max(0, $monthlyExpectedTotal - $monthlyPaidTotal),
            'year_total' => (float) $member->contributions()
                ->whereYear('week_start', $year)
                ->sum('amount'),
            'recent_contributions' => $member->contributions()
                ->latest('week_start')
                ->limit(5)
                ->get(),
        ];
    }
}
