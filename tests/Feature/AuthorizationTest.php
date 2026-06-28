<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_manage_members_but_treasurer_cannot(): void
    {
        $staff = $this->userWithRole(UserRole::Staff);
        $treasurer = $this->userWithRole(UserRole::Treasurer);

        $this->actingAs($staff)
            ->get(route('members.create'))
            ->assertRedirect('/admin/members/create');

        $this->actingAs($treasurer)
            ->get(route('members.create'))
            ->assertForbidden();
    }

    public function test_only_admin_can_delete_members(): void
    {
        $member = Member::create(['name' => 'Test Member']);

        $this->actingAs($this->userWithRole(UserRole::Admin))
            ->delete("/members/{$member->id}")
            ->assertMethodNotAllowed();
    }

    public function test_staff_cannot_manage_finances_but_treasurer_can(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get(route('expenses.create'))
            ->assertForbidden();

        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get(route('expenses.create'))
            ->assertRedirect('/admin/expenses/create');
    }

    public function test_old_contribution_mutation_endpoint_is_not_available(): void
    {
        $member = Member::create(['name' => 'Contributor']);

        $payload = [
            'member_id' => $member->id,
            'week_start' => now()->startOfWeek()->toDateString(),
        ];

        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->postJson('/contributions', $payload)
            ->assertMethodNotAllowed();
    }

    public function test_staff_can_manage_logistics_but_treasurer_cannot(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get(route('inventories.create'))
            ->assertRedirect('/admin/inventories/create');

        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get(route('inventories.create'))
            ->assertForbidden();
    }

    public function test_staff_can_manage_certificates_but_treasurer_cannot(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get(route('purok_certificates.index'))
            ->assertRedirect('/admin/purok-certificates');

        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get(route('purok_certificates.index'))
            ->assertForbidden();
    }

    public function test_cashflow_report_is_limited_to_admin_and_treasurer(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get(route('reports.cashflow'))
            ->assertForbidden();

        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get(route('reports.cashflow'))
            ->assertRedirect('/admin/reports/cash-flow');
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
