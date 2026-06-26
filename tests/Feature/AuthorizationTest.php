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
            ->assertOk();

        $this->actingAs($treasurer)
            ->get(route('members.create'))
            ->assertForbidden();
    }

    public function test_only_admin_can_delete_members(): void
    {
        $member = Member::create(['name' => 'Test Member']);

        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->delete(route('members.destroy', $member))
            ->assertForbidden();

        $this->actingAs($this->userWithRole(UserRole::Admin))
            ->delete(route('members.destroy', $member))
            ->assertRedirect(route('members.index'));

        $this->assertDatabaseMissing('members', [
            'id' => $member->id,
        ]);
    }

    public function test_staff_cannot_manage_finances_but_treasurer_can(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get(route('expenses.create'))
            ->assertForbidden();

        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get(route('expenses.create'))
            ->assertOk();
    }

    public function test_staff_cannot_record_contributions_but_treasurer_can(): void
    {
        $member = Member::create(['name' => 'Contributor']);

        $payload = [
            'member_id' => $member->id,
            'week_start' => now()->startOfWeek()->toDateString(),
        ];

        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->postJson(route('contributions.store'), $payload)
            ->assertForbidden();

        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->postJson(route('contributions.store'), $payload)
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_staff_can_manage_logistics_but_treasurer_cannot(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get(route('inventories.create'))
            ->assertOk();

        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get(route('inventories.create'))
            ->assertForbidden();
    }

    public function test_staff_can_manage_certificates_but_treasurer_cannot(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get(route('purok_certificates.index'))
            ->assertOk();

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
            ->assertOk();
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
