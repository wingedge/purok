<?php

namespace Tests\Feature;

use App\Actions\Rentals\CreateRental;
use App\Actions\Rentals\DeleteRental;
use App\Actions\Rentals\ReturnRental;
use App\Actions\Rentals\UpdateRental;
use App\Models\Income;
use App\Models\Inventory;
use App\Models\Rental;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class RentalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_rental_decrements_inventory_and_creates_income(): void
    {
        $inventory = $this->inventory(availableQuantity: 10);

        $rental = app(CreateRental::class)->execute([
            'inventory_id' => $inventory->id,
            'renter_name' => 'Maria Santos',
            'renter_contact' => '09171234567',
            'quantity' => 3,
            'rent_date' => '2026-06-01',
            'amount' => 150,
        ]);

        $this->assertSame(7, $inventory->refresh()->available_quantity);
        $this->assertDatabaseHas('rentals', [
            'id' => $rental->id,
            'inventory_id' => $inventory->id,
            'renter_name' => 'Maria Santos',
            'quantity' => 3,
            'status' => 'rented',
        ]);
        $this->assertDatabaseHas('incomes', [
            'rental_id' => $rental->id,
            'amount' => 150,
            'date' => '2026-06-01 00:00:00',
        ]);
    }

    public function test_create_rental_rejects_insufficient_inventory(): void
    {
        $inventory = $this->inventory(availableQuantity: 1);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough available inventory.');

        app(CreateRental::class)->execute([
            'inventory_id' => $inventory->id,
            'renter_name' => 'Maria Santos',
            'renter_contact' => '09171234567',
            'quantity' => 2,
            'rent_date' => '2026-06-01',
            'amount' => 100,
        ]);
    }

    public function test_update_rental_syncs_inventory_and_income(): void
    {
        $inventory = $this->inventory(availableQuantity: 8);
        $rental = $this->rental($inventory, quantity: 2);
        Income::create([
            'date' => '2026-06-01',
            'source' => 'Rentals - Chairs / Table rental',
            'description' => 'Original rental',
            'amount' => 100,
            'rental_id' => $rental->id,
        ]);

        $updated = app(UpdateRental::class)->execute($rental, [
            'renter_name' => 'Updated Renter',
            'renter_contact' => '09999999999',
            'quantity' => 5,
            'status' => 'rented',
            'amount' => 250,
            'rent_date' => '2026-06-02',
        ]);

        $this->assertSame(5, $inventory->refresh()->available_quantity);
        $this->assertSame(5, $updated->quantity);
        $this->assertSame('Updated Renter', $updated->renter_name);
        $this->assertDatabaseHas('incomes', [
            'rental_id' => $rental->id,
            'amount' => 250,
            'date' => '2026-06-02 00:00:00',
        ]);
    }

    public function test_return_rental_restores_inventory_once(): void
    {
        $inventory = $this->inventory(availableQuantity: 7);
        $rental = $this->rental($inventory, quantity: 3);

        $returned = app(ReturnRental::class)->execute($rental);

        $this->assertSame(10, $inventory->refresh()->available_quantity);
        $this->assertSame('returned', $returned->status);
        $this->assertNotNull($returned->return_date);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('This item has already been returned.');

        app(ReturnRental::class)->execute($returned);
    }

    public function test_delete_active_rental_restores_inventory_and_deletes_income(): void
    {
        $inventory = $this->inventory(availableQuantity: 6);
        $rental = $this->rental($inventory, quantity: 4);
        $income = Income::create([
            'date' => '2026-06-01',
            'source' => 'Rentals - Chairs / Table rental',
            'description' => 'Rental income',
            'amount' => 200,
            'rental_id' => $rental->id,
        ]);

        app(DeleteRental::class)->execute($rental);

        $this->assertSame(10, $inventory->refresh()->available_quantity);
        $this->assertDatabaseMissing('rentals', ['id' => $rental->id]);
        $this->assertDatabaseMissing('incomes', ['id' => $income->id]);
    }

    public function test_delete_returned_rental_does_not_restore_inventory_again(): void
    {
        $inventory = $this->inventory(availableQuantity: 10);
        $rental = $this->rental($inventory, quantity: 4, status: 'returned');

        app(DeleteRental::class)->execute($rental);

        $this->assertSame(10, $inventory->refresh()->available_quantity);
        $this->assertDatabaseMissing('rentals', ['id' => $rental->id]);
    }

    private function inventory(int $availableQuantity): Inventory
    {
        return Inventory::create([
            'item_name' => 'Chairs',
            'total_quantity' => 10,
            'available_quantity' => $availableQuantity,
            'rental_rate' => 50,
        ]);
    }

    private function rental(Inventory $inventory, int $quantity, string $status = 'rented'): Rental
    {
        return Rental::create([
            'inventory_id' => $inventory->id,
            'renter_name' => 'Original Renter',
            'renter_contact' => '09170000000',
            'quantity' => $quantity,
            'rent_date' => '2026-06-01',
            'status' => $status,
            'return_date' => $status === 'returned' ? '2026-06-02' : null,
        ]);
    }
}
