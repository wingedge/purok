<?php

namespace Tests\Feature;

use App\Actions\Contributions\DeleteContribution;
use App\Enums\UserRole;
use App\Models\Contribution;
use App\Models\Member;
use App\Models\User;
use App\Services\ContributionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContributionRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_contribution_amount_depends_on_member_indigent_status(): void
    {
        $service = app(ContributionService::class);

        $regularMember = Member::create([
            'name' => 'Regular Member',
            'indigent' => false,
        ]);

        $indigentMember = Member::create([
            'name' => 'Indigent Member',
            'indigent' => true,
        ]);

        $this->assertSame(10.00, $service->amountFor($regularMember));
        $this->assertSame(0.00, $service->amountFor($indigentMember));
    }

    public function test_recording_contribution_uses_service_amount_rule(): void
    {
        $member = Member::create([
            'name' => 'Contributor',
            'indigent' => false,
        ]);

        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->postJson(route('contributions.store'), [
                'member_id' => $member->id,
                'week_start' => '2026-06-07',
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'amount' => 10,
            ]);

        $this->assertDatabaseHas('contributions', [
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
            'amount' => 10,
        ]);
    }

    public function test_delete_contribution_action_returns_deleted_amount(): void
    {
        $member = Member::create([
            'name' => 'Contributor',
            'indigent' => false,
        ]);

        Contribution::create([
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
            'amount' => 10,
        ]);

        $amount = app(DeleteContribution::class)->execute($member->id, '2026-06-07');

        $this->assertSame(10.0, $amount);
        $this->assertDatabaseMissing('contributions', [
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
        ]);
    }

    public function test_delete_contribution_route_uses_delete_action(): void
    {
        $member = Member::create([
            'name' => 'Contributor',
            'indigent' => false,
        ]);

        Contribution::create([
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
            'amount' => 10,
        ]);

        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->deleteJson(route('contributions.destroy'), [
                'member_id' => $member->id,
                'week_start' => '2026-06-07',
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'amount' => 10,
            ]);

        $this->assertDatabaseMissing('contributions', [
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
        ]);
    }

    public function test_delete_contribution_route_returns_not_found_for_missing_record(): void
    {
        $member = Member::create([
            'name' => 'Contributor',
            'indigent' => false,
        ]);

        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->deleteJson(route('contributions.destroy'), [
                'member_id' => $member->id,
                'week_start' => '2026-06-07',
            ])
            ->assertNotFound()
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_dashboard_contribution_totals_use_week_start_for_accounting_period(): void
    {
        $member = Member::create(['name' => 'Contributor']);
        Contribution::create([
            'member_id' => $member->id,
            'week_start' => '2025-12-28',
            'amount' => 10,
            'created_at' => '2026-01-15 12:00:00',
            'updated_at' => '2026-01-15 12:00:00',
        ]);

        $response = $this->actingAs($this->userWithRole(UserRole::Admin))
            ->get(route('dashboard', ['year' => 2025]));

        $response->assertOk();
        $response->assertViewHas('totalContributions', 10.0);
        $response->assertViewHas('thisYearContributions', 10.0);
        $response->assertViewHas('contributorsCount', 1);
    }

    public function test_cashflow_contribution_total_uses_week_start_for_accounting_period(): void
    {
        $member = Member::create(['name' => 'Contributor']);
        Contribution::create([
            'member_id' => $member->id,
            'week_start' => '2025-02-02',
            'amount' => 10,
            'created_at' => '2026-01-15 12:00:00',
            'updated_at' => '2026-01-15 12:00:00',
        ]);

        $response = $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get(route('reports.cashflow', ['year' => 2025, 'month' => 2]));

        $response->assertOk();
        $response->assertViewHas('contributionTotal', 10.0);
        $response->assertViewHas('netCashFlow', 10.0);
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
