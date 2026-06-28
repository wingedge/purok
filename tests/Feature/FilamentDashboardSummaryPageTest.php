<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Contribution;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Inventory;
use App\Models\Member;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentDashboardSummaryPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_view_filterable_filament_dashboard_summary(): void
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
        $inventory = Inventory::create([
            'item_name' => 'Chairs',
            'total_quantity' => 10,
            'available_quantity' => 8,
            'rental_rate' => 50,
        ]);
        Rental::create([
            'inventory_id' => $inventory->id,
            'renter_name' => 'Juan Santos',
            'renter_contact' => '09170000000',
            'quantity' => 2,
            'rent_date' => '2026-06-15',
            'status' => 'rented',
            'created_at' => '2026-06-15 12:00:00',
            'updated_at' => '2026-06-15 12:00:00',
        ]);

        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get('/admin/dashboard-summary?year=2026&month=6')
            ->assertOk()
            ->assertSee('Dashboard Summary')
            ->assertSee('June 2026')
            ->assertSee('PHP 360.00')
            ->assertSee('Members Contributed')
            ->assertSee('Total Rentals');
    }

    public function test_member_cannot_view_filterable_filament_dashboard_summary(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Member))
            ->get('/admin/dashboard-summary')
            ->assertForbidden();
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
