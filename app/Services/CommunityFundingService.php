<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CommunityFundingDonation;
use App\Models\CommunityFundingEvent;
use Illuminate\Database\Eloquent\Builder;

final class CommunityFundingService
{
    /**
     * @return Builder<CommunityFundingDonation>
     */
    public function queryForAccountingPeriod(int $year, ?int $month = null): Builder
    {
        return CommunityFundingDonation::query()
            ->whereYear('received_at', $year)
            ->when($month, fn (Builder $query): Builder => $query->whereMonth('received_at', $month));
    }

    public function totalForAccountingPeriod(int $year, ?int $month = null): float
    {
        return (float) $this->queryForAccountingPeriod($year, $month)->sum('amount');
    }

    /**
     * @return array<int, array{name:string, total:float}>
     */
    public function eventTotalsForAccountingPeriod(int $year, ?int $month = null): array
    {
        return CommunityFundingEvent::query()
            ->whereHas('donations', fn (Builder $query): Builder => $query
                ->whereYear('received_at', $year)
                ->when($month, fn (Builder $query): Builder => $query->whereMonth('received_at', $month)))
            ->withSum([
                'donations as period_donations_total' => fn (Builder $query): Builder => $query
                    ->whereYear('received_at', $year)
                    ->when($month, fn (Builder $query): Builder => $query->whereMonth('received_at', $month)),
            ], 'amount')
            ->orderBy('name')
            ->get()
            ->map(fn (CommunityFundingEvent $event): array => [
                'name' => $event->name,
                'total' => (float) $event->period_donations_total,
            ])
            ->all();
    }
}
