<?php

declare(strict_types=1);

namespace App\Actions\Rentals;

use App\Models\Rental;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class ReturnRental
{
    public function execute(Rental $rental): Rental
    {
        if ($rental->status === 'returned') {
            throw new RuntimeException('This item has already been returned.');
        }

        return DB::transaction(function () use ($rental): Rental {
            $inventory = $rental->inventory()
                ->lockForUpdate()
                ->firstOrFail();

            $inventory->increment('available_quantity', $rental->quantity);

            $rental->update([
                'status' => 'returned',
                'return_date' => now(),
            ]);

            return $rental->refresh();
        });
    }
}
