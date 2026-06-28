<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Certificates\ListPurokCertificates;
use App\Enums\UserRole;
use App\Models\Dependent;
use App\Models\Member;
use App\Models\PurokCertificate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurokCertificateWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_create_certificate_log_through_legacy_route(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);

        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->post(route('purok_certificates.store'), [
                'member_id' => $member->id,
                'request_date' => '2026-06-15',
                'purpose' => 'Scholarship',
            ])
            ->assertRedirect(route('purok_certificates.index'));

        $this->assertDatabaseHas('purok_certificates', [
            'member_id' => $member->id,
            'request_date' => '2026-06-15',
            'purpose' => 'Scholarship',
        ]);
    }

    public function test_staff_can_update_certificate_log_through_legacy_route(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        $certificate = PurokCertificate::create([
            'member_id' => $member->id,
            'request_date' => '2026-06-15',
            'purpose' => 'Scholarship',
        ]);

        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->patch(route('purok_certificates.update', $certificate), [
                'member_id' => $member->id,
                'request_date' => '2026-06-20',
                'purpose' => 'Employment',
            ])
            ->assertRedirect(route('purok_certificates.index'));

        $this->assertDatabaseHas('purok_certificates', [
            'id' => $certificate->id,
            'request_date' => '2026-06-20',
            'purpose' => 'Employment',
        ]);
    }

    public function test_staff_can_delete_certificate_log_through_legacy_route(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        $certificate = PurokCertificate::create([
            'member_id' => $member->id,
            'request_date' => '2026-06-15',
            'purpose' => 'Scholarship',
        ]);

        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->delete(route('purok_certificates.destroy', $certificate))
            ->assertRedirect(route('purok_certificates.index'));

        $this->assertDatabaseMissing('purok_certificates', [
            'id' => $certificate->id,
        ]);
    }

    public function test_treasurer_cannot_create_update_or_delete_certificate_logs(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        $certificate = PurokCertificate::create([
            'member_id' => $member->id,
            'request_date' => '2026-06-15',
            'purpose' => 'Scholarship',
        ]);
        $treasurer = $this->userWithRole(UserRole::Treasurer);

        $this->actingAs($treasurer)
            ->post(route('purok_certificates.store'), [
                'member_id' => $member->id,
                'request_date' => '2026-06-20',
                'purpose' => 'Employment',
            ])
            ->assertForbidden();

        $this->actingAs($treasurer)
            ->patch(route('purok_certificates.update', $certificate), [
                'member_id' => $member->id,
                'request_date' => '2026-06-20',
                'purpose' => 'Employment',
            ])
            ->assertForbidden();

        $this->actingAs($treasurer)
            ->delete(route('purok_certificates.destroy', $certificate))
            ->assertForbidden();
    }

    public function test_certificate_member_search_matches_members_and_dependents(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        Dependent::create([
            'member_id' => $member->id,
            'name' => 'Juan Santos',
            'relationship' => 'Child',
        ]);

        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->getJson(route('members.search', ['q' => 'Juan']))
            ->assertOk()
            ->assertJsonFragment([
                'id' => $member->id,
                'name' => 'Maria Santos',
                'deps' => 'Juan Santos',
            ]);

        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->getJson(route('members.search', ['q' => 'J']))
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_certificate_list_action_filters_by_member_or_dependent_name(): void
    {
        $matchingMember = Member::create(['name' => 'Maria Santos']);
        Dependent::create([
            'member_id' => $matchingMember->id,
            'name' => 'Juan Santos',
            'relationship' => 'Child',
        ]);
        $otherMember = Member::create(['name' => 'Ana Cruz']);

        $matchingCertificate = PurokCertificate::create([
            'member_id' => $matchingMember->id,
            'request_date' => '2026-06-15',
            'purpose' => 'Scholarship',
        ]);
        PurokCertificate::create([
            'member_id' => $otherMember->id,
            'request_date' => '2026-06-16',
            'purpose' => 'Employment',
        ]);

        $results = app(ListPurokCertificates::class)->execute('Juan');

        $this->assertCount(1, $results->items());
        $this->assertTrue($results->first()->is($matchingCertificate));
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
