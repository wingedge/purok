<?php

namespace Tests\Feature;

use App\Actions\Exports\ExportIncomes;
use App\Enums\UserRole;
use App\Models\Income;
use App\Models\User;
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

    public function test_export_route_downloads_csv_for_treasurer(): void
    {
        Income::create([
            'date' => '2026-06-15',
            'source' => 'Donation / Fund Drive',
            'description' => 'Community donation',
            'amount' => 250.50,
        ]);

        $response = $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get(route('incomes.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('incomes-', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('Community donation', $response->streamedContent());
    }

    public function test_export_route_is_forbidden_for_staff(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get(route('incomes.export'))
            ->assertForbidden();
    }

    /**
     * @return array<int, array<int, string|null>>
     */
    private function parseCsv(string $csv): array
    {
        return array_map('str_getcsv', array_filter(preg_split('/\r\n|\r|\n/', trim($csv)) ?: []));
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
