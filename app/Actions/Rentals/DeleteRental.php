<?php

declare(strict_types=1);

namespace App\Actions\Rentals;

use App\Models\Rental;
use Illuminate\Support\Facades\DB;

final class DeleteRental
{
    public function execute(Rental $rental): void
    {
        DB::transaction(function () use ($rental): void {
            if ($rental->status === 'rented') {
                $rental->inventory()
                    ->lockForUpdate()
                    ->firstOrFail()
                    ->increment('available_quantity', $rental->quantity);
            }

            $rental->income?->delete();
            $rental->delete();
        });
    }
}
