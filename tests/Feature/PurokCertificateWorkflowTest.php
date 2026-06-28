<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Certificates\ListPurokCertificates;
use App\Actions\Certificates\CreatePurokCertificate;
use App\Actions\Certificates\DeletePurokCertificate;
use App\Actions\Certificates\SearchCertificateMembers;
use App\Actions\Certificates\UpdatePurokCertificate;
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

    public function test_create_certificate_action_creates_certificate_log(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);

        app(CreatePurokCertificate::class)->execute([
            'member_id' => $member->id,
            'request_date' => '2026-06-15',
            'purpose' => 'Scholarship',
        ]);

        $this->assertDatabaseHas('purok_certificates', [
            'member_id' => $member->id,
            'request_date' => '2026-06-15',
            'purpose' => 'Scholarship',
        ]);
    }

    public function test_update_certificate_action_updates_certificate_log(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        $certificate = PurokCertificate::create([
            'member_id' => $member->id,
            'request_date' => '2026-06-15',
            'purpose' => 'Scholarship',
        ]);

        app(UpdatePurokCertificate::class)->execute($certificate, [
            'member_id' => $member->id,
            'request_date' => '2026-06-20',
            'purpose' => 'Employment',
        ]);

        $this->assertDatabaseHas('purok_certificates', [
            'id' => $certificate->id,
            'request_date' => '2026-06-20',
            'purpose' => 'Employment',
        ]);
    }

    public function test_delete_certificate_action_deletes_certificate_log(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        $certificate = PurokCertificate::create([
            'member_id' => $member->id,
            'request_date' => '2026-06-15',
            'purpose' => 'Scholarship',
        ]);

        app(DeletePurokCertificate::class)->execute($certificate);

        $this->assertDatabaseMissing('purok_certificates', [
            'id' => $certificate->id,
        ]);
    }

    public function test_old_certificate_mutation_routes_are_not_available(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        $certificate = PurokCertificate::create([
            'member_id' => $member->id,
            'request_date' => '2026-06-15',
            'purpose' => 'Scholarship',
        ]);
        $response = $this->actingAs($this->userWithRole(UserRole::Staff))
            ->post('/purok_certificates', [
                'member_id' => $member->id,
                'request_date' => '2026-06-20',
                'purpose' => 'Employment',
            ]);
        $this->assertContains($response->getStatusCode(), [404, 405]);

        $response = $this->actingAs($this->userWithRole(UserRole::Staff))
            ->patch("/purok_certificates/{$certificate->id}", [
                'member_id' => $member->id,
                'request_date' => '2026-06-20',
                'purpose' => 'Employment',
            ]);
        $this->assertContains($response->getStatusCode(), [404, 405]);

        $response = $this->actingAs($this->userWithRole(UserRole::Staff))
            ->delete("/purok_certificates/{$certificate->id}");
        $this->assertContains($response->getStatusCode(), [404, 405]);
    }

    public function test_certificate_member_search_matches_members_and_dependents(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        Dependent::create([
            'member_id' => $member->id,
            'name' => 'Juan Santos',
            'relationship' => 'Child',
        ]);

        $results = app(SearchCertificateMembers::class)->execute('Juan');
        $emptyResults = app(SearchCertificateMembers::class)->execute('J');

        $this->assertSame([
            'id' => $member->id,
            'name' => 'Maria Santos',
            'deps' => 'Juan Santos',
        ], $results->first());
        $this->assertTrue($emptyResults->isEmpty());
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
