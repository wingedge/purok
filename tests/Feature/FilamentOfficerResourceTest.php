<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Member;
use App\Models\Officer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentOfficerResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_access_filament_officer_resource(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        Officer::create([
            'member_id' => $member->id,
            'position' => 'President',
            'term_start' => '2026-01-01',
            'is_active' => true,
        ]);

        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get('/admin/officers')
            ->assertOk()
            ->assertSee('Maria Santos')
            ->assertSee('President');
    }

    public function test_treasurer_cannot_access_filament_officer_resource(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get('/admin/officers')
            ->assertForbidden();
    }

    public function test_admin_can_access_filament_officer_create_page(): void
    {
        Member::create(['name' => 'Maria Santos']);

        $this->actingAs($this->userWithRole(UserRole::Admin))
            ->get('/admin/officers/create')
            ->assertOk();
    }

    public function test_staff_can_access_filament_officer_edit_page(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        $officer = Officer::create([
            'member_id' => $member->id,
            'position' => 'Secretary',
            'term_start' => '2026-01-01',
            'is_active' => true,
        ]);

        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get("/admin/officers/{$officer->id}/edit")
            ->assertOk()
            ->assertSee('Secretary');
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
