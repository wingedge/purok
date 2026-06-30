<?php

namespace Tests\Feature;

use App\Actions\Imports\ImportContributions;
use App\Models\Contribution;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportContributionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_contributions_from_csv(): void
    {
        $member = Member::create([
            'name' => 'Maria Santos',
            'indigent' => false,
        ]);
        $path = $this->writeCsv([
            ['member_id', 'member_name', 'week_start', 'remarks'],
            [(string) $member->id, '', '2026-06-07', 'June collection'],
        ]);

        $result = app(ImportContributions::class)->execute($path);

        $this->assertSame(1, $result->created);
        $this->assertSame(0, $result->failed());
        $this->assertDatabaseHas('contributions', [
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
            'amount' => 10,
            'remarks' => 'June collection',
        ]);
    }

    public function test_it_can_import_contributions_by_member_name(): void
    {
        Member::create([
            'name' => 'Maria Santos',
            'indigent' => false,
        ]);
        $path = $this->writeCsv([
            ['member_id', 'member_name', 'week_start', 'remarks'],
            ['', 'Maria Santos', '2026-06-07', 'By name'],
        ]);

        $result = app(ImportContributions::class)->execute($path);

        $this->assertSame(1, $result->created);
        $this->assertDatabaseHas('contributions', [
            'week_start' => '2026-06-07',
            'remarks' => 'By name',
        ]);
    }

    public function test_it_updates_existing_contributions_from_csv(): void
    {
        $member = Member::create([
            'name' => 'Maria Santos',
            'indigent' => false,
        ]);
        Contribution::create([
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
            'amount' => 10,
            'remarks' => 'Old',
        ]);
        $path = $this->writeCsv([
            ['member_id', 'member_name', 'week_start', 'remarks'],
            [(string) $member->id, '', '2026-06-07', 'Updated'],
        ]);

        $result = app(ImportContributions::class)->execute($path);

        $this->assertSame(0, $result->created);
        $this->assertSame(1, $result->updated);
        $this->assertDatabaseCount('contributions', 1);
        $this->assertDatabaseHas('contributions', [
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
            'remarks' => 'Updated',
        ]);
    }

    public function test_it_reports_invalid_contribution_rows(): void
    {
        $path = $this->writeCsv([
            ['member_id', 'member_name', 'week_start', 'remarks'],
            ['', '', 'not-a-date', 'Invalid'],
        ]);

        $result = app(ImportContributions::class)->execute($path);

        $this->assertSame(0, $result->created);
        $this->assertSame(1, $result->failed());
        $this->assertSame(2, $result->failedRows[0]->rowNumber);
        $this->assertDatabaseCount('contributions', 0);
    }

    /**
     * @param array<int, array<int, string>> $rows
     */
    private function writeCsv(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'contributions-import-');
        $stream = fopen($path, 'w');

        foreach ($rows as $row) {
            fputcsv($stream, $row);
        }

        fclose($stream);

        return $path;
    }
}
