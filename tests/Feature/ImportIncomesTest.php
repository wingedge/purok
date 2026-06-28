<?php

namespace Tests\Feature;

use App\Actions\Imports\ImportIncomes;
use App\Models\Income;
use App\Models\Inventory;
use App\Models\Rental;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportIncomesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_incomes_from_csv(): void
    {
        $path = $this->writeCsv([
            ['date', 'source', 'description', 'amount', 'rental_id'],
            ['2026-06-15', 'Donation / Fund Drive', 'Community donation', '250.50', ''],
        ]);

        $result = app(ImportIncomes::class)->execute($path);

        $this->assertSame(1, $result->created);
        $this->assertSame(0, $result->failed());
        $this->assertDatabaseHas('incomes', [
            'date' => '2026-06-15 00:00:00',
            'source' => 'Donation / Fund Drive',
            'description' => 'Community donation',
            'amount' => 250.50,
            'rental_id' => null,
        ]);
    }

    public function test_it_imports_income_with_existing_rental_id(): void
    {
        $rental = $this->createRental();
        $path = $this->writeCsv([
            ['date', 'source', 'description', 'amount', 'rental_id'],
            ['2026-06-15', 'Rentals - Chairs / Table rental', 'Rental income', '300.00', (string) $rental->id],
        ]);

        $result = app(ImportIncomes::class)->execute($path);

        $this->assertSame(1, $result->created);
        $this->assertDatabaseHas('incomes', [
            'date' => '2026-06-15 00:00:00',
            'source' => 'Rentals - Chairs / Table rental',
            'description' => 'Rental income',
            'amount' => 300.00,
            'rental_id' => $rental->id,
        ]);
    }

    public function test_it_reports_invalid_rows_without_importing_them(): void
    {
        $path = $this->writeCsv([
            ['date', 'source', 'description', 'amount', 'rental_id'],
            ['not-a-date', '', 'Invalid row', '-5', '999'],
        ]);

        $result = app(ImportIncomes::class)->execute($path);

        $this->assertSame(0, $result->created);
        $this->assertSame(1, $result->failed());
        $this->assertSame(2, $result->failedRows[0]->rowNumber);
        $this->assertDatabaseCount('incomes', 0);
    }

    public function test_it_skips_blank_rows(): void
    {
        $path = $this->writeCsv([
            ['date', 'source', 'description', 'amount', 'rental_id'],
            ['', '', '', '', ''],
        ]);

        $result = app(ImportIncomes::class)->execute($path);

        $this->assertSame(0, $result->created);
        $this->assertSame(1, $result->skipped);
        $this->assertDatabaseCount('incomes', 0);
    }

    /**
     * @param array<int, array<int, string>> $rows
     */
    private function writeCsv(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'incomes-import-');
        $stream = fopen($path, 'w');

        foreach ($rows as $row) {
            fputcsv($stream, $row);
        }

        fclose($stream);

        return $path;
    }

    private function createRental(): Rental
    {
        $inventory = Inventory::create([
            'item_name' => 'Chairs',
            'total_quantity' => 10,
            'available_quantity' => 10,
            'rental_rate' => 15,
        ]);

        return Rental::create([
            'inventory_id' => $inventory->id,
            'renter_name' => 'Juan Santos',
            'renter_contact' => '09170000000',
            'quantity' => 2,
            'rent_date' => '2026-06-15',
            'status' => 'rented',
        ]);
    }

}
