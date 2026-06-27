<?php

namespace Tests\Feature;

use App\Actions\Contributions\RecordContribution;
use App\Enums\UserRole;
use App\Models\Contribution;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentContributionResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_treasurer_can_access_filament_contribution_resource(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        Contribution::create([
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
            'amount' => 10,
        ]);

        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get('/admin/contributions')
            ->assertOk()
            ->assertSee('Maria Santos');
    }

    public function test_staff_cannot_access_filament_contribution_resource(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get('/admin/contributions')
            ->assertForbidden();
    }

    public function test_admin_can_access_filament_contribution_create_page(): void
    {
        Member::create(['name' => 'Maria Santos']);

        $this->actingAs($this->userWithRole(UserRole::Admin))
            ->get('/admin/contributions/create')
            ->assertOk();
    }

    public function test_treasurer_can_access_filament_contribution_edit_page(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        $contribution = Contribution::create([
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
            'amount' => 10,
        ]);

        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get("/admin/contributions/{$contribution->id}/edit")
            ->assertOk()
            ->assertSee('Maria Santos');
    }

    public function test_record_contribution_action_uses_service_amount_rule(): void
    {
        $regularMember = Member::create([
            'name' => 'Regular Member',
            'indigent' => false,
        ]);
        $indigentMember = Member::create([
            'name' => 'Indigent Member',
            'indigent' => true,
        ]);

        $regularContribution = app(RecordContribution::class)->execute($regularMember, '2026-06-07');
        $indigentContribution = app(RecordContribution::class)->execute($indigentMember, '2026-06-07');

        $this->assertSame(10.0, (float) $regularContribution->amount);
        $this->assertSame(0.0, (float) $indigentContribution->amount);
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
