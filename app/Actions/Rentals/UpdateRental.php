<?php

declare(strict_types=1);

namespace App\Actions\Rentals;

use App\Models\Income;
use App\Models\Rental;
use App\Support\Finance\IncomeSources;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class UpdateRental
{
    /**
     * @param array{
     *     renter_name:string,
     *     renter_contact:string,
     *     quantity:int|string,
     *     status:string,
     *     amount:int|float|string,
     *     rent_date:string
     * } $data
     */
    public function execute(Rental $rental, array $data): Rental
    {
        return DB::transaction(function () use ($rental, $data): Rental {
            $inventory = $rental->inventory()
                ->lockForUpdate()
                ->firstOrFail();

            $quantity = (int) $data['quantity'];

            if ($rental->status === 'rented' && $data['status'] === 'rented') {
                $diff = $quantity - $rental->quantity;

                if ($diff > 0 && $inventory->available_quantity < $diff) {
                    throw new RuntimeException('Not enough stock to increase quantity.');
                }

                if ($diff > 0) {
                    $inventory->decrement('available_quantity', $diff);
                } elseif ($diff < 0) {
                    $inventory->increment('available_quantity', abs($diff));
                }
            }

            if ($rental->status === 'rented' && $data['status'] === 'returned') {
                $inventory->increment('available_quantity', $rental->quantity);
                $rental->return_date = now();
            }

            Income::updateOrCreate(
                ['rental_id' => $rental->id],
                [
                    'date' => $data['rent_date'],
                    'source' => IncomeSources::rental(),
                    'description' => "Rental: {$data['renter_name']} - {$inventory->item_name} x {$quantity}",
                    'amount' => $data['amount'],
                ],
            );

            $rental->update([
                'renter_name' => $data['renter_name'],
                'renter_contact' => $data['renter_contact'],
                'quantity' => $quantity,
                'status' => $data['status'],
                'rent_date' => $data['rent_date'],
                'return_date' => $rental->return_date,
            ]);

            return $rental->refresh();
        });
    }
}
