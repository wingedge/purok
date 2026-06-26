<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentMemberResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_filament_members_resource(): void
    {
        Member::create(['name' => 'Maria Santos']);

        $this->actingAs($this->userWithRole(UserRole::Admin))
            ->get('/admin/members')
            ->assertOk()
            ->assertSee('Maria Santos');
    }

    public function test_staff_can_access_filament_members_create_page(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get('/admin/members/create')
            ->assertOk();
    }

    public function test_member_role_cannot_access_filament_admin_panel(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Member))
            ->get('/admin')
            ->assertForbidden();
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
