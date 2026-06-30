<?php

namespace Tests\Feature;

use App\Actions\Exports\ExportContributions;
use App\Models\Contribution;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportContributionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_exports_contributions_as_csv(): void
    {
        $member = Member::create([
            'name' => 'Maria Santos',
            'indigent' => false,
        ]);

        $contribution = Contribution::create([
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
            'amount' => 10,
            'remarks' => 'June collection',
        ]);

        $csv = app(ExportContributions::class)->execute();
        $rows = $this->parseCsv($csv);

        $this->assertSame([
            'id',
            'member_id',
            'member_name',
            'week_start',
            'amount',
            'remarks',
            'created_at',
            'updated_at',
        ], $rows[0]);

        $this->assertSame((string) $contribution->id, $rows[1][0]);
        $this->assertSame((string) $member->id, $rows[1][1]);
        $this->assertSame('Maria Santos', $rows[1][2]);
        $this->assertSame('2026-06-07', $rows[1][3]);
        $this->assertSame('10.00', $rows[1][4]);
        $this->assertSame('June collection', $rows[1][5]);
    }

    /**
     * @return array<int, array<int, string|null>>
     */
    private function parseCsv(string $csv): array
    {
        return array_map('str_getcsv', array_filter(preg_split('/\r\n|\r|\n/', trim($csv)) ?: []));
    }
}
