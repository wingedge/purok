<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\CommunityFunding\CreateCommunityFundingEvent;
use App\Actions\CommunityFunding\DeleteCommunityFundingDonation;
use App\Actions\CommunityFunding\DeleteCommunityFundingEvent;
use App\Actions\CommunityFunding\RecordCommunityFundingDonation;
use App\Actions\CommunityFunding\UpdateCommunityFundingDonation;
use App\Actions\CommunityFunding\UpdateCommunityFundingEvent;
use App\Enums\UserRole;
use App\Models\CommunityFundingDonation;
use App\Models\CommunityFundingEvent;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityFundingTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_actions_create_update_and_delete_events(): void
    {
        $event = app(CreateCommunityFundingEvent::class)->execute([
            'name' => 'Covered Court Repair',
            'description' => 'Materials for court roof repair',
            'deadline' => '2026-08-15',
            'goal_amount' => '15000',
        ]);

        $this->assertDatabaseHas('community_funding_events', [
            'id' => $event->id,
            'name' => 'Covered Court Repair',
            'goal_amount' => '15000.00',
        ]);

        app(UpdateCommunityFundingEvent::class)->execute($event, [
            'name' => 'Covered Court Lighting',
            'description' => 'Lighting and wiring',
            'deadline' => null,
            'goal_amount' => '12000',
        ]);

        $this->assertDatabaseHas('community_funding_events', [
            'id' => $event->id,
            'name' => 'Covered Court Lighting',
            'description' => 'Lighting and wiring',
            'deadline' => null,
            'goal_amount' => '12000.00',
        ]);

        app(DeleteCommunityFundingEvent::class)->execute($event->refresh());

        $this->assertDatabaseMissing('community_funding_events', [
            'id' => $event->id,
        ]);
    }

    public function test_event_goal_amount_is_optional(): void
    {
        $event = app(CreateCommunityFundingEvent::class)->execute([
            'name' => 'Emergency Assistance',
            'description' => 'Open-ended community support',
        ]);

        $this->assertNull($event->goal_amount);
        $this->assertSame(0.0, $event->progress_percentage);
        $this->assertSame('Active', $event->status);
    }

    public function test_donation_actions_record_update_and_delete_donations(): void
    {
        $event = CommunityFundingEvent::create([
            'name' => 'Street Light Fund',
            'goal_amount' => '5000',
        ]);
        $member = Member::create(['name' => 'Maria Santos']);
        $otherMember = Member::create(['name' => 'Juan Santos']);

        $donation = app(RecordCommunityFundingDonation::class)->execute($event, $member, [
            'amount' => '250',
            'received_at' => '2026-06-20',
            'remarks' => 'Initial pledge',
        ]);

        $this->assertDatabaseHas('community_funding_donations', [
            'id' => $donation->id,
            'community_funding_event_id' => $event->id,
            'member_id' => $member->id,
            'amount' => '250.00',
        ]);

        app(UpdateCommunityFundingDonation::class)->execute($donation, $event, $otherMember, [
            'amount' => '500',
            'received_at' => '2026-06-21',
            'remarks' => 'Updated amount',
        ]);

        $this->assertDatabaseHas('community_funding_donations', [
            'id' => $donation->id,
            'member_id' => $otherMember->id,
            'amount' => '500.00',
            'remarks' => 'Updated amount',
        ]);

        app(DeleteCommunityFundingDonation::class)->execute($donation->refresh());

        $this->assertDatabaseMissing('community_funding_donations', [
            'id' => $donation->id,
        ]);
    }

    public function test_actual_amount_is_computed_from_donations(): void
    {
        $event = CommunityFundingEvent::create([
            'name' => 'Clinic Supplies',
            'goal_amount' => '1000',
        ]);
        $member = Member::create(['name' => 'Maria Santos']);

        CommunityFundingDonation::create([
            'community_funding_event_id' => $event->id,
            'member_id' => $member->id,
            'amount' => '250',
            'received_at' => '2026-06-01',
        ]);
        CommunityFundingDonation::create([
            'community_funding_event_id' => $event->id,
            'member_id' => $member->id,
            'amount' => '125.50',
            'received_at' => '2026-06-02',
        ]);

        $this->assertSame(375.50, $event->refresh()->actual_amount);
        $this->assertSame(37.55, $event->progress_percentage);
    }

    public function test_treasurer_can_access_filament_community_funding_resource(): void
    {
        CommunityFundingEvent::create([
            'name' => 'Drainage Repair Fund',
            'goal_amount' => '8000',
        ]);

        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get('/admin/community-funding-events')
            ->assertOk()
            ->assertSee('Drainage Repair Fund');
    }

    public function test_staff_cannot_access_filament_community_funding_resource(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get('/admin/community-funding-events')
            ->assertForbidden();
    }

    public function test_community_funding_policy_preserves_role_rules(): void
    {
        $event = CommunityFundingEvent::create([
            'name' => 'Water Pump Fund',
            'goal_amount' => '7000',
        ]);
        $member = Member::create(['name' => 'Maria Santos']);
        $donation = CommunityFundingDonation::create([
            'community_funding_event_id' => $event->id,
            'member_id' => $member->id,
            'amount' => '100',
            'received_at' => '2026-06-01',
        ]);
        $treasurer = $this->userWithRole(UserRole::Treasurer);
        $staff = $this->userWithRole(UserRole::Staff);
        $memberUser = $this->userWithRole(UserRole::Member);

        $this->assertTrue($treasurer->can('view', $event));
        $this->assertTrue($treasurer->can('create', CommunityFundingEvent::class));
        $this->assertTrue($treasurer->can('update', $event));
        $this->assertTrue($treasurer->can('delete', $event));
        $this->assertTrue($treasurer->can('view', $donation));
        $this->assertTrue($treasurer->can('create', CommunityFundingDonation::class));
        $this->assertFalse($staff->can('view', $event));
        $this->assertFalse($staff->can('create', CommunityFundingEvent::class));
        $this->assertFalse($memberUser->can('view', $event));
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
