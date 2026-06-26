<?php

namespace Tests\Feature;

use App\Actions\Imports\ImportMembers;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportMembersTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_members_and_dependents_from_csv(): void
    {
        $path = $this->csvPath([
            ['name', 'phone', 'email', 'birthday', 'indigent', 'dependent_names', 'dependent_relationships'],
            ['Maria Santos', '09171234567', 'maria@example.com', '1990-01-15', 'yes', 'Ana Santos|Jose Santos', 'Daughter|Son'],
            ['Pedro Cruz', '', '', '', 'no', '', ''],
        ]);

        $result = app(ImportMembers::class)->execute($path);

        $this->assertSame(2, $result->created);
        $this->assertSame(0, $result->skipped);
        $this->assertSame(0, $result->failed());

        $this->assertDatabaseHas('members', [
            'name' => 'Maria Santos',
            'phone' => '09171234567',
            'email' => 'maria@example.com',
            'indigent' => true,
        ]);

        $this->assertDatabaseHas('members', [
            'name' => 'Pedro Cruz',
            'indigent' => false,
        ]);

        $this->assertDatabaseHas('dependents', [
            'name' => 'Ana Santos',
            'relationship' => 'Daughter',
        ]);

        $this->assertDatabaseHas('dependents', [
            'name' => 'Jose Santos',
            'relationship' => 'Son',
        ]);
    }

    public function test_it_skips_blank_rows(): void
    {
        $path = $this->csvPath([
            ['name', 'phone', 'email', 'birthday', 'indigent', 'dependent_names'],
            [''],
            ['Maria Santos', '', '', '', 'false', ''],
        ]);

        $result = app(ImportMembers::class)->execute($path);

        $this->assertSame(1, $result->created);
        $this->assertSame(1, $result->skipped);
        $this->assertSame(0, $result->failed());
    }

    public function test_it_reports_invalid_rows_without_importing_them(): void
    {
        $path = $this->csvPath([
            ['name', 'phone', 'email', 'birthday', 'indigent', 'dependent_names'],
            ['', '', 'bad-email', '', 'yes', ''],
            ['Valid Member', '', 'valid@example.com', '', 'no', ''],
        ]);

        $result = app(ImportMembers::class)->execute($path);

        $this->assertSame(1, $result->created);
        $this->assertSame(0, $result->skipped);
        $this->assertSame(1, $result->failed());
        $this->assertSame(2, $result->failedRows[0]->rowNumber);
        $this->assertArrayHasKey('name', $result->failedRows[0]->errors);
        $this->assertArrayHasKey('email', $result->failedRows[0]->errors);

        $this->assertDatabaseMissing('members', [
            'email' => 'bad-email',
        ]);
    }

    public function test_it_reports_rows_with_mismatched_column_counts(): void
    {
        $path = $this->csvPath([
            ['name', 'phone'],
            ['Maria Santos', '09171234567', 'unexpected value'],
        ]);

        $result = app(ImportMembers::class)->execute($path);

        $this->assertSame(0, $result->created);
        $this->assertSame(1, $result->failed());
        $this->assertSame(2, $result->failedRows[0]->rowNumber);
        $this->assertArrayHasKey('row', $result->failedRows[0]->errors);
    }

    /**
     * @param array<int, array<int, string>> $rows
     */
    private function csvPath(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'purok-members-');
        $handle = fopen($path, 'w');

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        return $path;
    }
}
