<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Member;
use App\Models\PurokCertificate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentCertificateResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_access_filament_certificate_resource(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        PurokCertificate::create([
            'member_id' => $member->id,
            'request_date' => '2026-06-15',
            'purpose' => 'Scholarship',
        ]);

        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get('/admin/purok-certificates')
            ->assertOk()
            ->assertSee('Maria Santos')
            ->assertSee('Scholarship');
    }

    public function test_treasurer_cannot_access_filament_certificate_resource(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get('/admin/purok-certificates')
            ->assertForbidden();
    }

    public function test_admin_can_access_filament_certificate_create_page(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Admin))
            ->get('/admin/purok-certificates/create')
            ->assertOk();
    }

    public function test_staff_can_access_filament_certificate_edit_page(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        $certificate = PurokCertificate::create([
            'member_id' => $member->id,
            'request_date' => '2026-06-15',
            'purpose' => 'Scholarship',
        ]);

        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get("/admin/purok-certificates/{$certificate->id}/edit")
            ->assertOk()
            ->assertSee('Scholarship');
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
