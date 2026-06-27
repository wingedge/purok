<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentFinanceResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_treasurer_can_access_filament_expenses_resource(): void
    {
        $treasurer = $this->userWithRole(UserRole::Treasurer);

        Expense::create([
            'date' => '2026-06-15',
            'category' => 'Community Services',
            'description' => 'Cleanup supplies',
            'amount' => 123.45,
            'created_by' => $treasurer->id,
        ]);

        $this->actingAs($treasurer)
            ->get('/admin/expenses')
            ->assertOk()
            ->assertSee('Cleanup supplies');
    }

    public function test_staff_cannot_access_filament_expenses_resource(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get('/admin/expenses')
            ->assertForbidden();
    }

    public function test_treasurer_can_access_filament_incomes_resource(): void
    {
        Income::create([
            'date' => '2026-06-15',
            'source' => 'Donation / Fund Drive',
            'description' => 'Community donation',
            'amount' => 250.50,
        ]);

        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get('/admin/incomes')
            ->assertOk()
            ->assertSee('Community donation');
    }

    public function test_staff_cannot_access_filament_incomes_resource(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get('/admin/incomes')
            ->assertForbidden();
    }

    public function test_admin_can_access_filament_finance_create_pages(): void
    {
        $admin = $this->userWithRole(UserRole::Admin);

        $this->actingAs($admin)
            ->get('/admin/expenses/create')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/incomes/create')
            ->assertOk();
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
