<?php

declare(strict_types=1);

namespace App\Actions\Rentals;

use App\Models\Income;
use App\Models\Inventory;
use App\Models\Rental;
use App\Support\Finance\IncomeSources;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class CreateRental
{
    /**
     * @param array{
     *     inventory_id:int|string,
     *     renter_name:string,
     *     renter_contact:string,
     *     quantity:int|string,
     *     rent_date:string,
     *     amount:int|float|string
     * } $data
     */
    public function execute(array $data): Rental
    {
        return DB::transaction(function () use ($data): Rental {
            $inventory = Inventory::query()
                ->lockForUpdate()
                ->findOrFail($data['inventory_id']);

            $quantity = (int) $data['quantity'];

            if ($inventory->available_quantity < $quantity) {
                throw new RuntimeException('Not enough available inventory.');
            }

            $rental = Rental::create([
                'inventory_id' => $inventory->id,
                'renter_name' => $data['renter_name'],
                'renter_contact' => $data['renter_contact'],
                'quantity' => $quantity,
                'rent_date' => $data['rent_date'],
            ]);

            Income::create([
                'date' => $data['rent_date'],
                'source' => IncomeSources::rental(),
                'description' => "Rental for {$data['renter_name']} ({$inventory->item_name} x {$quantity})",
                'amount' => $data['amount'],
                'rental_id' => $rental->id,
            ]);

            $inventory->decrement('available_quantity', $quantity);

            return $rental->refresh();
        });
    }
}
