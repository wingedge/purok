<?php

namespace Tests\Feature;

use App\Actions\Exports\ExportIncomes;
use App\Models\Income;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportIncomesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_exports_incomes_as_csv(): void
    {
        $income = Income::create([
            'date' => '2026-06-15',
            'source' => 'Donation / Fund Drive',
            'description' => 'Community donation',
            'amount' => 250.50,
        ]);

        $csv = app(ExportIncomes::class)->execute();
        $rows = $this->parseCsv($csv);

        $this->assertSame([
            'id',
            'date',
            'source',
            'description',
            'amount',
            'rental_id',
            'created_at',
            'updated_at',
        ], $rows[0]);

        $this->assertSame((string) $income->id, $rows[1][0]);
        $this->assertSame('2026-06-15', $rows[1][1]);
        $this->assertSame('Donation / Fund Drive', $rows[1][2]);
        $this->assertSame('Community donation', $rows[1][3]);
        $this->assertSame('250.50', $rows[1][4]);
        $this->assertSame('', $rows[1][5]);
    }

    /**
     * @return array<int, array<int, string|null>>
     */
    private function parseCsv(string $csv): array
    {
        return array_map('str_getcsv', array_filter(preg_split('/\r\n|\r|\n/', trim($csv)) ?: []));
    }

}
