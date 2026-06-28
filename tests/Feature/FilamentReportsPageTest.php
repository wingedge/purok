<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentReportsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_treasurer_can_view_filament_reports_landing_page(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get('/admin/reports')
            ->assertOk()
            ->assertSee('Reports')
            ->assertSee('Cash Flow Statement')
            ->assertSee('Member Contributions');
    }

    public function test_staff_sees_only_allowed_reports_on_filament_reports_landing_page(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get('/admin/reports')
            ->assertOk()
            ->assertSee('Member Contributions')
            ->assertDontSee('Cash Flow Statement');
    }

    public function test_member_cannot_view_filament_reports_landing_page(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Member))
            ->get('/admin/reports')
            ->assertForbidden();
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
