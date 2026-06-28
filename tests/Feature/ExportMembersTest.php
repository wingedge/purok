<?php

namespace Tests\Feature;

use App\Actions\Exports\ExportMembers;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportMembersTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_exports_members_and_dependents_as_csv(): void
    {
        $member = Member::create([
            'name' => 'Maria Santos',
            'phone' => '09171234567',
            'email' => 'maria@example.com',
            'birthday' => '1990-01-15',
            'indigent' => true,
        ]);

        $member->dependents()->createMany([
            ['name' => 'Ana Santos', 'relationship' => 'Daughter'],
            ['name' => 'Jose Santos', 'relationship' => 'Son'],
        ]);

        $csv = app(ExportMembers::class)->execute();
        $rows = $this->parseCsv($csv);

        $this->assertSame([
            'id',
            'name',
            'phone',
            'email',
            'birthday',
            'indigent',
            'dependent_names',
            'dependent_relationships',
            'created_at',
            'updated_at',
        ], $rows[0]);

        $this->assertSame((string) $member->id, $rows[1][0]);
        $this->assertSame('Maria Santos', $rows[1][1]);
        $this->assertSame('09171234567', $rows[1][2]);
        $this->assertSame('maria@example.com', $rows[1][3]);
        $this->assertSame('1990-01-15', $rows[1][4]);
        $this->assertSame('yes', $rows[1][5]);
        $this->assertSame('Ana Santos|Jose Santos', $rows[1][6]);
        $this->assertSame('Daughter|Son', $rows[1][7]);
    }

    /**
     * @return array<int, array<int, string|null>>
     */
    private function parseCsv(string $csv): array
    {
        return array_map('str_getcsv', array_filter(preg_split('/\r\n|\r|\n/', trim($csv)) ?: []));
    }

}
