<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

final class BuildRentalReport
{
    /**
     * @return array{
     *     start: Carbon,
     *     end: Carbon,
     *     status: string|null,
     *     rentals: Collection<int, Rental>,
     *     rentalCount: int,
     *     totalQuantity: int,
     *     activeCount: int,
     *     totalIncome: float
     * }
     */
    public function execute(int $year, ?int $month = null, ?string $status = null): array
    {
        $year = max(1, $year);
        $month = $month !== null ? max(1, min(12, $month)) : null;
        $status = in_array($status, ['rented', 'returned'], true) ? $status : null;
        $start = Carbon::create($year, $month ?? 1, 1)->startOf($month === null ? 'year' : 'month');
        $end = $start->copy()->endOf($month === null ? 'year' : 'month');

        $rentals = Rental::query()
            ->with(['inventory', 'income'])
            ->whereBetween('rent_date', [$start->toDateString(), $end->toDateString()])
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->orderBy('rent_date')
            ->orderBy('id')
            ->get();

        return [
            'start' => $start,
            'end' => $end,
            'status' => $status,
            'rentals' => $rentals,
            'rentalCount' => $rentals->count(),
            'totalQuantity' => (int) $rentals->sum('quantity'),
            'activeCount' => $rentals->where('status', 'rented')->count(),
            'totalIncome' => (float) $rentals->sum(fn (Rental $rental): float => (float) ($rental->income?->amount ?? 0)),
        ];
    }
}
