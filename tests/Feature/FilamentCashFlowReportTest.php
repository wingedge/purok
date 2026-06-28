<?php

namespace Tests\Feature;

use App\Actions\Reports\BuildCashFlowReport;
use App\Enums\UserRole;
use App\Models\Contribution;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentCashFlowReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_flow_report_action_calculates_totals_for_period(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        Income::create([
            'date' => '2026-06-15',
            'source' => 'Donation / Fund Drive',
            'description' => 'Donation',
            'amount' => 500,
        ]);
        Expense::create([
            'date' => '2026-06-16',
            'category' => 'Community Services',
            'description' => 'Supplies',
            'amount' => 125,
            'created_by' => User::factory()->create()->id,
        ]);
        Contribution::create([
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
            'amount' => 10,
        ]);

        $report = app(BuildCashFlowReport::class)->execute(2026, 6);

        $this->assertSame(500.0, $report['incomeTotal']);
        $this->assertSame(10.0, $report['contributionTotal']);
        $this->assertSame(125.0, $report['expenseTotal']);
        $this->assertSame(510.0, $report['totalInflow']);
        $this->assertSame(385.0, $report['netCashFlow']);
    }

    public function test_treasurer_can_view_filament_cash_flow_report(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get('/admin/reports/cash-flow?year=2026&month=6')
            ->assertOk()
            ->assertSee('Cash Flow')
            ->assertSee('Total Inflow')
            ->assertSee('Net Cash Flow');
    }

    public function test_staff_cannot_view_filament_cash_flow_report(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get('/admin/reports/cash-flow')
            ->assertForbidden();
    }

    public function test_old_cash_flow_report_redirects_to_filament_report(): void
    {
        Income::create([
            'date' => '2026-06-15',
            'source' => 'Donation / Fund Drive',
            'description' => 'Donation',
            'amount' => 500,
        ]);

        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get('/reports/cashflow?year=2026&month=6')
            ->assertRedirect('/admin/reports/cash-flow?year=2026&month=6');
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
