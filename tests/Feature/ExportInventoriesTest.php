<?php

namespace Tests\Feature;

use App\Actions\Exports\ExportInventories;
use App\Models\Inventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportInventoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_exports_inventories_as_csv(): void
    {
        $inventory = Inventory::create([
            'item_name' => 'Chairs',
            'total_quantity' => 20,
            'available_quantity' => 15,
            'rental_rate' => 25.5,
        ]);

        $csv = app(ExportInventories::class)->execute();
        $rows = $this->parseCsv($csv);

        $this->assertSame([
            'id',
            'item_name',
            'total_quantity',
            'available_quantity',
            'rental_rate',
            'created_at',
            'updated_at',
        ], $rows[0]);

        $this->assertSame((string) $inventory->id, $rows[1][0]);
        $this->assertSame('Chairs', $rows[1][1]);
        $this->assertSame('20', $rows[1][2]);
        $this->assertSame('15', $rows[1][3]);
        $this->assertSame('25.50', $rows[1][4]);
    }

    /**
     * @return array<int, array<int, string|null>>
     */
    private function parseCsv(string $csv): array
    {
        return array_map('str_getcsv', array_filter(preg_split('/\r\n|\r|\n/', trim($csv)) ?: []));
    }
}
