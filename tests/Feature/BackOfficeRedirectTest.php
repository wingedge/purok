<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Inventory;
use App\Models\Member;
use App\Models\PurokCertificate;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackOfficeRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_back_office_index_pages_redirect_to_filament(): void
    {
        $admin = $this->userWithRole(UserRole::Admin);

        $this->actingAs($admin)->get(route('members.index'))->assertRedirect('/admin/members');
        $this->actingAs($admin)->get(route('expenses.index'))->assertRedirect('/admin/expenses');
        $this->actingAs($admin)->get(route('incomes.index'))->assertRedirect('/admin/incomes');
        $this->actingAs($admin)->get(route('inventories.index'))->assertRedirect('/admin/inventories');
        $this->actingAs($admin)->get(route('rentals.index'))->assertRedirect('/admin/rentals');
        $this->actingAs($admin)->get(route('contributions.index'))->assertRedirect('/admin/contribution-grid');
        $this->actingAs($admin)->get(route('purok_certificates.index'))->assertRedirect('/admin/purok-certificates');
        $this->actingAs($admin)->get(route('reports.index'))->assertRedirect('/admin/reports');
    }

    public function test_old_back_office_create_pages_redirect_to_filament(): void
    {
        $admin = $this->userWithRole(UserRole::Admin);

        $this->actingAs($admin)->get(route('members.create'))->assertRedirect('/admin/members/create');
        $this->actingAs($admin)->get(route('expenses.create'))->assertRedirect('/admin/expenses/create');
        $this->actingAs($admin)->get(route('incomes.create'))->assertRedirect('/admin/incomes/create');
        $this->actingAs($admin)->get(route('inventories.create'))->assertRedirect('/admin/inventories/create');
        $this->actingAs($admin)->get(route('rentals.create'))->assertRedirect('/admin/rentals/create');
        $this->actingAs($admin)->get(route('purok_certificates.create'))->assertRedirect('/admin/purok-certificates/create');
    }

    public function test_old_back_office_edit_pages_redirect_to_filament(): void
    {
        $admin = $this->userWithRole(UserRole::Admin);
        $creator = User::factory()->create();
        $member = Member::create(['name' => 'Maria Santos']);
        $expense = Expense::create([
            'date' => '2026-06-15',
            'category' => 'Community Services',
            'description' => 'Supplies',
            'amount' => 100,
            'created_by' => $creator->id,
        ]);
        $income = Income::create([
            'date' => '2026-06-15',
            'source' => 'Donation / Fund Drive',
            'description' => 'Donation',
            'amount' => 100,
        ]);
        $inventory = Inventory::create([
            'item_name' => 'Chairs',
            'total_quantity' => 10,
            'available_quantity' => 10,
            'rental_rate' => 5,
        ]);
        $rental = Rental::create([
            'inventory_id' => $inventory->id,
            'renter_name' => 'Juan',
            'renter_contact' => '0917',
            'quantity' => 1,
            'rent_date' => '2026-06-15',
        ]);
        $certificate = PurokCertificate::create([
            'member_id' => $member->id,
            'request_date' => '2026-06-15',
            'purpose' => 'Employment',
        ]);

        $this->actingAs($admin)->get(route('members.edit', $member))->assertRedirect("/admin/members/{$member->id}/edit");
        $this->actingAs($admin)->get(route('expenses.edit', $expense))->assertRedirect("/admin/expenses/{$expense->id}/edit");
        $this->actingAs($admin)->get(route('incomes.edit', $income))->assertRedirect("/admin/incomes/{$income->id}/edit");
        $this->actingAs($admin)->get(route('inventories.edit', $inventory))->assertRedirect("/admin/inventories/{$inventory->id}/edit");
        $this->actingAs($admin)->get(route('rentals.edit', $rental))->assertRedirect("/admin/rentals/{$rental->id}/edit");
        $this->actingAs($admin)->get(route('purok_certificates.edit', $certificate))->assertRedirect("/admin/purok-certificates/{$certificate->id}/edit");
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
