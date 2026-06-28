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

class ModelPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_policy_preserves_existing_role_rules(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        $admin = $this->userWithRole(UserRole::Admin);
        $staff = $this->userWithRole(UserRole::Staff);
        $treasurer = $this->userWithRole(UserRole::Treasurer);
        $memberUser = $this->userWithRole(UserRole::Member);

        $this->assertTrue($admin->can('delete', $member));
        $this->assertTrue($staff->can('view', $member));
        $this->assertTrue($staff->can('create', Member::class));
        $this->assertTrue($staff->can('update', $member));
        $this->assertFalse($staff->can('delete', $member));
        $this->assertTrue($treasurer->can('view', $member));
        $this->assertFalse($treasurer->can('create', Member::class));
        $this->assertFalse($treasurer->can('update', $member));
        $this->assertFalse($memberUser->can('view', $member));
    }

    public function test_expense_policy_preserves_existing_role_rules(): void
    {
        $treasurer = $this->userWithRole(UserRole::Treasurer);
        $staff = $this->userWithRole(UserRole::Staff);
        $expense = Expense::create([
            'date' => '2026-06-15',
            'category' => 'Misc',
            'description' => 'Office supplies',
            'amount' => '125.50',
            'created_by' => $treasurer->id,
        ]);

        $this->assertTrue($treasurer->can('view', $expense));
        $this->assertTrue($treasurer->can('create', Expense::class));
        $this->assertTrue($treasurer->can('update', $expense));
        $this->assertTrue($treasurer->can('delete', $expense));
        $this->assertFalse($staff->can('view', $expense));
        $this->assertFalse($staff->can('create', Expense::class));
    }

    public function test_income_policy_preserves_existing_role_rules(): void
    {
        $treasurer = $this->userWithRole(UserRole::Treasurer);
        $staff = $this->userWithRole(UserRole::Staff);
        $income = Income::create([
            'date' => '2026-06-15',
            'source' => 'Donation / Fund Drive',
            'description' => 'Community donation',
            'amount' => '500',
        ]);

        $this->assertTrue($treasurer->can('view', $income));
        $this->assertTrue($treasurer->can('create', Income::class));
        $this->assertTrue($treasurer->can('update', $income));
        $this->assertTrue($treasurer->can('delete', $income));
        $this->assertFalse($staff->can('view', $income));
        $this->assertFalse($staff->can('create', Income::class));
    }

    public function test_inventory_policy_preserves_existing_role_rules(): void
    {
        $staff = $this->userWithRole(UserRole::Staff);
        $treasurer = $this->userWithRole(UserRole::Treasurer);
        $inventory = Inventory::create([
            'item_name' => 'Chairs',
            'total_quantity' => 10,
            'available_quantity' => 10,
            'rental_rate' => 5,
        ]);

        $this->assertTrue($staff->can('view', $inventory));
        $this->assertTrue($staff->can('create', Inventory::class));
        $this->assertTrue($staff->can('update', $inventory));
        $this->assertTrue($staff->can('delete', $inventory));
        $this->assertFalse($treasurer->can('view', $inventory));
        $this->assertFalse($treasurer->can('create', Inventory::class));
    }

    public function test_rental_policy_preserves_existing_role_rules(): void
    {
        $staff = $this->userWithRole(UserRole::Staff);
        $treasurer = $this->userWithRole(UserRole::Treasurer);
        $inventory = Inventory::create([
            'item_name' => 'Chairs',
            'total_quantity' => 10,
            'available_quantity' => 10,
            'rental_rate' => 5,
        ]);
        $rental = Rental::create([
            'inventory_id' => $inventory->id,
            'renter_name' => 'Juan Dela Cruz',
            'renter_contact' => '09171234567',
            'quantity' => 1,
            'rent_date' => '2026-06-15',
        ]);

        $this->assertTrue($staff->can('view', $rental));
        $this->assertTrue($staff->can('create', Rental::class));
        $this->assertTrue($staff->can('update', $rental));
        $this->assertTrue($staff->can('delete', $rental));
        $this->assertFalse($treasurer->can('view', $rental));
        $this->assertFalse($treasurer->can('create', Rental::class));
    }

    public function test_certificate_policy_preserves_existing_role_rules(): void
    {
        $staff = $this->userWithRole(UserRole::Staff);
        $treasurer = $this->userWithRole(UserRole::Treasurer);
        $member = Member::create(['name' => 'Maria Santos']);
        $certificate = PurokCertificate::create([
            'member_id' => $member->id,
            'request_date' => '2026-06-15',
            'purpose' => 'Scholarship',
        ]);

        $this->assertTrue($staff->can('view', $certificate));
        $this->assertTrue($staff->can('create', PurokCertificate::class));
        $this->assertTrue($staff->can('update', $certificate));
        $this->assertTrue($staff->can('delete', $certificate));
        $this->assertFalse($treasurer->can('view', $certificate));
        $this->assertFalse($treasurer->can('create', PurokCertificate::class));
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
