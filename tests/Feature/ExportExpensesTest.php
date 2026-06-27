<?php

namespace Tests\Feature;

use App\Actions\Exports\ExportExpenses;
use App\Enums\UserRole;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportExpensesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_exports_expenses_as_csv(): void
    {
        $creator = User::factory()->create([
            'name' => 'Treasurer User',
        ]);

        $expense = Expense::create([
            'date' => '2026-06-15',
            'category' => 'Community Services',
            'description' => 'Cleanup supplies',
            'amount' => 123.45,
            'created_by' => $creator->id,
        ]);

        $csv = app(ExportExpenses::class)->execute();
        $rows = $this->parseCsv($csv);

        $this->assertSame([
            'id',
            'date',
            'category',
            'description',
            'amount',
            'created_by',
            'created_by_name',
            'created_at',
            'updated_at',
        ], $rows[0]);

        $this->assertSame((string) $expense->id, $rows[1][0]);
        $this->assertSame('2026-06-15', $rows[1][1]);
        $this->assertSame('Community Services', $rows[1][2]);
        $this->assertSame('Cleanup supplies', $rows[1][3]);
        $this->assertSame('123.45', $rows[1][4]);
        $this->assertSame((string) $creator->id, $rows[1][5]);
        $this->assertSame('Treasurer User', $rows[1][6]);
    }

    public function test_export_route_downloads_csv_for_treasurer(): void
    {
        $creator = $this->userWithRole(UserRole::Treasurer);

        Expense::create([
            'date' => '2026-06-15',
            'category' => 'Community Services',
            'description' => 'Cleanup supplies',
            'amount' => 123.45,
            'created_by' => $creator->id,
        ]);

        $response = $this->actingAs($creator)
            ->get(route('expenses.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('expenses-', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('Cleanup supplies', $response->streamedContent());
    }

    public function test_export_route_is_forbidden_for_staff(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get(route('expenses.export'))
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
