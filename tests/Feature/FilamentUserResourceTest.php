<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Member;
use App\Models\Officer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentUserResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_user_resource_and_see_linked_officer(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        Officer::create([
            'member_id' => $member->id,
            'position' => 'President',
            'term_start' => '2026-01-01',
            'is_active' => true,
        ]);
        User::factory()->create([
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'role' => UserRole::Staff->value,
            'member_id' => $member->id,
        ]);

        $this->actingAs($this->userWithRole(UserRole::Admin))
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('maria@example.com')
            ->assertSee('President');
    }

    public function test_staff_cannot_access_user_resource(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_admin_can_access_user_create_page(): void
    {
        Member::create(['name' => 'Maria Santos']);

        $this->actingAs($this->userWithRole(UserRole::Admin))
            ->get('/admin/users/create')
            ->assertOk()
            ->assertSee('Linked member or officer');
    }

    public function test_admin_can_access_user_edit_page(): void
    {
        $user = $this->userWithRole(UserRole::Treasurer);

        $this->actingAs($this->userWithRole(UserRole::Admin))
            ->get("/admin/users/{$user->id}/edit")
            ->assertOk()
            ->assertSee($user->email);
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
