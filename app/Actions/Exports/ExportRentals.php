<?php

declare(strict_types=1);

namespace App\Actions\Exports;

use App\Models\Rental;

final class ExportRentals
{
    /**
     * @return array<int, string>
     */
    public function headers(): array
    {
        return [
            'id',
            'inventory_id',
            'inventory_item_name',
            'renter_name',
            'renter_contact',
            'quantity',
            'rent_date',
            'return_date',
            'status',
            'amount',
            'income_id',
            'created_at',
            'updated_at',
        ];
    }

    public function execute(): string
    {
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $this->headers());

        Rental::query()
            ->with(['inventory', 'income'])
            ->orderBy('rent_date')
            ->orderBy('id')
            ->chunk(500, function ($rentals) use ($stream): void {
                foreach ($rentals as $rental) {
                    fputcsv($stream, $this->row($rental));
                }
            });

        rewind($stream);

        return stream_get_contents($stream) ?: '';
    }

    /**
     * @return array<int, string|int|null>
     */
    private function row(Rental $rental): array
    {
        return [
            $rental->id,
            $rental->inventory_id,
            $rental->inventory?->item_name,
            $rental->renter_name,
            $rental->renter_contact,
            $rental->quantity,
            $rental->rent_date?->format('Y-m-d'),
            $rental->return_date?->format('Y-m-d'),
            $rental->status,
            number_format((float) ($rental->income?->amount ?? 0), 2, '.', ''),
            $rental->income?->id,
            $rental->created_at?->format('Y-m-d H:i:s'),
            $rental->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
