<?php

namespace Tests\Feature;

use App\Actions\Imports\ImportInventories;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportInventoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_inventories(): void
    {
        $path = $this->writeCsv([
            ['item_name', 'total_quantity', 'available_quantity', 'rental_rate'],
            ['Chairs', '20', '15', '25.50'],
        ]);

        $result = app(ImportInventories::class)->execute($path);

        $this->assertSame(1, $result->created);
        $this->assertDatabaseHas('inventories', [
            'item_name' => 'Chairs',
            'total_quantity' => 20,
            'available_quantity' => 15,
            'rental_rate' => 25.50,
        ]);
    }

    public function test_it_defaults_available_quantity_and_rental_rate(): void
    {
        $path = $this->writeCsv([
            ['item_name', 'total_quantity', 'available_quantity', 'rental_rate'],
            ['Tables', '8', '', ''],
        ]);

        $result = app(ImportInventories::class)->execute($path);

        $this->assertSame(1, $result->created);
        $this->assertDatabaseHas('inventories', [
            'item_name' => 'Tables',
            'total_quantity' => 8,
            'available_quantity' => 8,
            'rental_rate' => 0,
        ]);
    }

    public function test_it_reports_invalid_rows_without_importing_them(): void
    {
        $path = $this->writeCsv([
            ['item_name', 'total_quantity', 'available_quantity', 'rental_rate'],
            ['', '-1', '2', '-5'],
        ]);

        $result = app(ImportInventories::class)->execute($path);

        $this->assertSame(0, $result->created);
        $this->assertSame(1, $result->failed());
        $this->assertSame(2, $result->failedRows[0]->rowNumber);
        $this->assertDatabaseCount('inventories', 0);
    }

    public function test_it_rejects_available_quantity_greater_than_total_quantity(): void
    {
        $path = $this->writeCsv([
            ['item_name', 'total_quantity', 'available_quantity', 'rental_rate'],
            ['Tents', '3', '4', '100'],
        ]);

        $result = app(ImportInventories::class)->execute($path);

        $this->assertSame(0, $result->created);
        $this->assertSame(1, $result->failed());
        $this->assertDatabaseCount('inventories', 0);
    }

    public function test_it_skips_blank_rows(): void
    {
        $path = $this->writeCsv([
            ['item_name', 'total_quantity', 'available_quantity', 'rental_rate'],
            ['', '', '', ''],
        ]);

        $result = app(ImportInventories::class)->execute($path);

        $this->assertSame(0, $result->created);
        $this->assertSame(1, $result->skipped);
        $this->assertDatabaseCount('inventories', 0);
    }

    /**
     * @param array<int, array<int, string>> $rows
     */
    private function writeCsv(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'inventories-import-');
        $stream = fopen($path, 'w');

        foreach ($rows as $row) {
            fputcsv($stream, $row);
        }

        fclose($stream);

        return $path;
    }
}
