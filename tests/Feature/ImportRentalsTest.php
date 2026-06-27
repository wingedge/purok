<?php

namespace Tests\Feature;

use App\Actions\Imports\ImportRentals;
use App\Enums\UserRole;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportRentalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_active_rentals_and_decrements_inventory(): void
    {
        $inventory = $this->inventory(availableQuantity: 10);
        $path = $this->writeCsv([
            ['inventory_id', 'renter_name', 'renter_contact', 'quantity', 'rent_date', 'amount', 'status', 'return_date'],
            [(string) $inventory->id, 'Maria Santos', '09170000000', '2', '2026-06-15', '300.00', 'rented', ''],
        ]);

        $result = app(ImportRentals::class)->execute($path);

        $this->assertSame(1, $result->created);
        $this->assertSame(8, $inventory->refresh()->available_quantity);
        $this->assertDatabaseHas('rentals', [
            'inventory_id' => $inventory->id,
            'renter_name' => 'Maria Santos',
            'quantity' => 2,
            'status' => 'rented',
        ]);
        $this->assertDatabaseHas('incomes', [
            'amount' => 300.00,
            'date' => '2026-06-15 00:00:00',
        ]);
    }

    public function test_it_imports_returned_rentals_without_decrementing_inventory(): void
    {
        $inventory = $this->inventory(availableQuantity: 10);
        $path = $this->writeCsv([
            ['inventory_id', 'renter_name', 'renter_contact', 'quantity', 'rent_date', 'amount', 'status', 'return_date'],
            [(string) $inventory->id, 'Maria Santos', '09170000000', '2', '2026-06-15', '300.00', 'returned', '2026-06-16'],
        ]);

        $result = app(ImportRentals::class)->execute($path);

        $this->assertSame(1, $result->created);
        $this->assertSame(10, $inventory->refresh()->available_quantity);
        $this->assertDatabaseHas('rentals', [
            'inventory_id' => $inventory->id,
            'renter_name' => 'Maria Santos',
            'quantity' => 2,
            'status' => 'returned',
            'return_date' => '2026-06-16 00:00:00',
        ]);
    }

    public function test_it_reports_invalid_rows_without_importing_them(): void
    {
        $path = $this->writeCsv([
            ['inventory_id', 'renter_name', 'renter_contact', 'quantity', 'rent_date', 'amount', 'status', 'return_date'],
            ['999', '', '', '0', 'not-a-date', '-1', 'returned', ''],
        ]);

        $result = app(ImportRentals::class)->execute($path);

        $this->assertSame(0, $result->created);
        $this->assertSame(1, $result->failed());
        $this->assertSame(2, $result->failedRows[0]->rowNumber);
        $this->assertDatabaseCount('rentals', 0);
    }

    public function test_it_reports_insufficient_inventory_as_failed_row(): void
    {
        $inventory = $this->inventory(availableQuantity: 1);
        $path = $this->writeCsv([
            ['inventory_id', 'renter_name', 'renter_contact', 'quantity', 'rent_date', 'amount', 'status', 'return_date'],
            [(string) $inventory->id, 'Maria Santos', '09170000000', '2', '2026-06-15', '300.00', 'rented', ''],
        ]);

        $result = app(ImportRentals::class)->execute($path);

        $this->assertSame(0, $result->created);
        $this->assertSame(1, $result->failed());
        $this->assertSame(1, $inventory->refresh()->available_quantity);
    }

    public function test_it_skips_blank_rows(): void
    {
        $path = $this->writeCsv([
            ['inventory_id', 'renter_name', 'renter_contact', 'quantity', 'rent_date', 'amount', 'status', 'return_date'],
            ['', '', '', '', '', '', '', ''],
        ]);

        $result = app(ImportRentals::class)->execute($path);

        $this->assertSame(0, $result->created);
        $this->assertSame(1, $result->skipped);
        $this->assertDatabaseCount('rentals', 0);
    }

    public function test_import_route_imports_csv_for_staff(): void
    {
        $inventory = $this->inventory(availableQuantity: 10);
        $file = UploadedFile::fake()->createWithContent(
            'rentals.csv',
            "inventory_id,renter_name,renter_contact,quantity,rent_date,amount,status,return_date\n{$inventory->id},Maria Santos,09170000000,2,2026-06-15,300.00,rented,\n",
        );

        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->post(route('rentals.import'), [
                'csv_file' => $file,
            ])
            ->assertRedirect(route('rentals.index'))
            ->assertSessionHas('success');

        $this->assertSame(8, $inventory->refresh()->available_quantity);
        $this->assertDatabaseHas('rentals', [
            'inventory_id' => $inventory->id,
            'renter_name' => 'Maria Santos',
            'quantity' => 2,
        ]);
    }

    public function test_import_route_is_forbidden_for_treasurer(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'rentals.csv',
            "inventory_id,renter_name,renter_contact,quantity,rent_date,amount,status,return_date\n1,Maria Santos,09170000000,2,2026-06-15,300.00,rented,\n",
        );

        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->post(route('rentals.import'), [
                'csv_file' => $file,
            ])
            ->assertForbidden();
    }

    /**
     * @param array<int, array<int, string>> $rows
     */
    private function writeCsv(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'rentals-import-');
        $stream = fopen($path, 'w');

        foreach ($rows as $row) {
            fputcsv($stream, $row);
        }

        fclose($stream);

        return $path;
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

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
