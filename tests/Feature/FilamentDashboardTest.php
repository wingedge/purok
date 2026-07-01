<?php

namespace Tests\Feature;

use App\Actions\Dashboard\BuildDashboardSummary;
use App\Enums\UserRole;
use App\Models\CommunityFundingDonation;
use App\Models\CommunityFundingEvent;
use App\Models\Contribution;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Member;
use App\Models\Rental;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_summary_action_calculates_current_totals(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        Income::create([
            'date' => '2026-06-15',
            'source' => 'Donation / Fund Drive',
            'description' => 'Donation',
            'amount' => 500,
        ]);
        Expense::create([
            'date' => '2026-06-16',
            'category' => 'Community Services',
            'description' => 'Supplies',
            'amount' => 150,
            'created_by' => User::factory()->create()->id,
        ]);
        Contribution::create([
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
            'amount' => 10,
        ]);
        $event = CommunityFundingEvent::create([
            'name' => 'Street Light Fund',
            'goal_amount' => 1000,
        ]);
        CommunityFundingDonation::create([
            'community_funding_event_id' => $event->id,
            'member_id' => $member->id,
            'amount' => 200,
            'received_at' => '2026-06-18',
        ]);
        $inventory = Inventory::create([
            'item_name' => 'Chairs',
            'total_quantity' => 10,
            'available_quantity' => 8,
            'rental_rate' => 50,
        ]);
        $rental = Rental::create([
            'inventory_id' => $inventory->id,
            'renter_name' => 'Juan Santos',
            'renter_contact' => '09170000000',
            'quantity' => 2,
            'rent_date' => '2026-06-15',
            'status' => 'rented',
        ]);
        $rental->forceFill([
            'created_at' => '2026-06-15 12:00:00',
            'updated_at' => '2026-06-15 12:00:00',
        ])->save();

        $summary = app(BuildDashboardSummary::class)->execute(2026, 6);

        $this->assertSame(1, $summary['totalMembers']);
        $this->assertSame(500.0, $summary['totalIncomes']);
        $this->assertSame(10.0, $summary['totalContributions']);
        $this->assertSame(200.0, $summary['totalCommunityFunding']);
        $this->assertSame(150.0, $summary['totalExpenses']);
        $this->assertSame(560.0, $summary['totalFunds']);
        $this->assertSame(1, $summary['contributorsCount']);
        $this->assertSame(1, $summary['totalRentals']);
    }

    public function test_filament_dashboard_shows_summary_widget_for_back_office_user(): void
    {
        Member::create(['name' => 'Maria Santos']);

        $this->actingAs($this->userWithRole(UserRole::Admin))
            ->get('/admin')
            ->assertOk()
            ->assertSee('Purok Summary')
            ->assertSee('Members');
    }

    public function test_member_role_cannot_access_filament_dashboard(): void
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
