<?php

declare(strict_types=1);

namespace App\Actions\Contributions;

use App\Models\Contribution;
use App\Models\Member;
use App\Services\ContributionService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RecordContributionPayment
{
    public function __construct(
        private readonly ContributionService $contributions,
        private readonly RecordContribution $recordContribution,
    ) {}

    /**
     * @return array{next_week: ?Carbon, remaining_balance: float, remaining_weeks: int}
     */
    public function summary(Member $member, int $year): array
    {
        $weeklyAmountInCents = (int) round($this->contributions->amountFor($member) * 100);

        if ($weeklyAmountInCents <= 0) {
            throw ValidationException::withMessages([
                'data.member_id' => 'This member does not have a required weekly contribution.',
            ]);
        }

        $unpaidWeeks = $this->unpaidWeeks($member, $year);

        return [
            'next_week' => $unpaidWeeks->first(),
            'remaining_balance' => ($unpaidWeeks->count() * $weeklyAmountInCents) / 100,
            'remaining_weeks' => $unpaidWeeks->count(),
        ];
    }

    /**
     * @return Collection<int, Contribution>
     */
    public function execute(Member $member, int $year, float $paymentAmount, ?string $remarks = null): Collection
    {
        $weeklyAmountInCents = (int) round($this->contributions->amountFor($member) * 100);
        $paymentAmountInCents = (int) round($paymentAmount * 100);

        if ($paymentAmountInCents <= 0 || $weeklyAmountInCents <= 0 || $paymentAmountInCents % $weeklyAmountInCents !== 0) {
            throw ValidationException::withMessages([
                'data.payment_amount' => sprintf(
                    'The payment amount must be a positive multiple of PHP %.2f.',
                    $weeklyAmountInCents / 100,
                ),
            ]);
        }

        $summary = $this->summary($member, $year);

        if ($paymentAmountInCents > (int) round($summary['remaining_balance'] * 100)) {
            throw ValidationException::withMessages([
                'data.payment_amount' => sprintf(
                    'The payment cannot exceed the remaining balance of PHP %.2f.',
                    $summary['remaining_balance'],
                ),
            ]);
        }

        $weekCount = intdiv($paymentAmountInCents, $weeklyAmountInCents);
        $weekDates = $this->unpaidWeeks($member, $year)
            ->take($weekCount)
            ->map(fn (Carbon $week): string => $week->toDateString());

        return DB::transaction(fn (): Collection => $weekDates->map(
            fn (string $weekDate): Contribution => $this->recordContribution->execute($member, $weekDate, $remarks),
        ));
    }

    /**
     * @return Collection<int, Carbon>
     */
    private function unpaidWeeks(Member $member, int $year): Collection
    {
        $start = Carbon::create($year, 1, 1)->startOfYear();
        $weeks = $this->contributions->sundaysBetween($start, $start->copy()->endOfYear());
        $paidWeekDates = $member->contributions()
            ->whereIn('week_start', $weeks->map->toDateString())
            ->pluck('week_start')
            ->mapWithKeys(fn ($date): array => [Carbon::parse($date)->toDateString() => true]);

        return $weeks
            ->reject(fn (Carbon $week): bool => $paidWeekDates->has($week->toDateString()))
            ->values();
    }
}
