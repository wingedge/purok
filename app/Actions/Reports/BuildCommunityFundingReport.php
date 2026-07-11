<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\Models\CommunityFundingDonation;
use App\Models\CommunityFundingEvent;
use Illuminate\Database\Eloquent\Collection;

final class BuildCommunityFundingReport
{
    /**
     * @return array{
     *     event: CommunityFundingEvent,
     *     donations: Collection<int, CommunityFundingDonation>,
     *     goalAmount: float,
     *     collectedAmount: float,
     *     remainingAmount: float,
     *     progressPercentage: float,
     *     donorCount: int
     * }
     */
    public function execute(int $eventId): array
    {
        $event = CommunityFundingEvent::query()->findOrFail($eventId);
        $donations = $event->donations()
            ->with('member')
            ->orderBy('received_at')
            ->orderBy('id')
            ->get();
        $goalAmount = (float) $event->goal_amount;
        $collectedAmount = (float) $donations->sum('amount');

        return [
            'event' => $event,
            'donations' => $donations,
            'goalAmount' => $goalAmount,
            'collectedAmount' => $collectedAmount,
            'remainingAmount' => max(0, $goalAmount - $collectedAmount),
            'progressPercentage' => $goalAmount > 0 ? round(min(100, ($collectedAmount / $goalAmount) * 100), 2) : 0.0,
            'donorCount' => $donations->pluck('member_id')->unique()->count(),
        ];
    }
}
