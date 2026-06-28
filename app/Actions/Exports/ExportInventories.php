<?php

declare(strict_types=1);

namespace App\Actions\Exports;

use App\Models\Inventory;

final class ExportInventories
{
    /**
     * @return array<int, string>
     */
    public function headers(): array
    {
        return [
            'id',
            'item_name',
            'total_quantity',
            'available_quantity',
            'rental_rate',
            'created_at',
            'updated_at',
        ];
    }

    public function execute(): string
    {
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $this->headers());

        Inventory::query()
            ->orderBy('item_name')
            ->orderBy('id')
            ->chunk(500, function ($inventories) use ($stream): void {
                foreach ($inventories as $inventory) {
                    fputcsv($stream, $this->row($inventory));
                }
            });

        rewind($stream);

        return stream_get_contents($stream) ?: '';
    }

    /**
     * @return array<int, string|int|null>
     */
    private function row(Inventory $inventory): array
    {
        return [
            $inventory->id,
            $inventory->item_name,
            $inventory->total_quantity,
            $inventory->available_quantity,
            number_format((float) $inventory->rental_rate, 2, '.', ''),
            $inventory->created_at?->format('Y-m-d H:i:s'),
            $inventory->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
