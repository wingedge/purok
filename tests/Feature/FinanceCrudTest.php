<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_treasurer_can_create_update_and_delete_expenses_through_legacy_routes(): void
    {
        $treasurer = $this->userWithRole(UserRole::Treasurer);

        $this->actingAs($treasurer)
            ->post(route('expenses.store'), [
                'date' => '2026-06-15',
                'category' => 'Misc',
                'description' => 'Office supplies',
                'amount' => '125.50',
            ])
            ->assertRedirect(route('expenses.index'));

        $expense = Expense::where('description', 'Office supplies')->firstOrFail();

        $this->assertSame($treasurer->id, $expense->created_by);
        $this->assertSame('2026-06-15', $expense->date->toDateString());
        $this->assertSame('125.50', $expense->amount);

        $this->actingAs($treasurer)
            ->patch(route('expenses.update', $expense), [
                'date' => '2026-06-20',
                'category' => 'Community Services	Health Programs, Feeding, Cleanup Drives',
                'description' => 'Cleanup supplies',
                'amount' => '300',
            ])
            ->assertRedirect(route('expenses.index'));

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'category' => 'Community Services	Health Programs, Feeding, Cleanup Drives',
            'description' => 'Cleanup supplies',
            'amount' => '300.00',
            'created_by' => $treasurer->id,
        ]);
        $this->assertSame('2026-06-20', $expense->refresh()->date->toDateString());

        $this->actingAs($treasurer)
            ->delete(route('expenses.destroy', $expense))
            ->assertRedirect(route('expenses.index'));

        $this->assertDatabaseMissing('expenses', [
            'id' => $expense->id,
        ]);
    }

    public function test_staff_cannot_create_update_or_delete_expenses(): void
    {
        $creator = $this->userWithRole(UserRole::Treasurer);
        $expense = Expense::create([
            'date' => '2026-06-15',
            'category' => 'Misc',
            'description' => 'Office supplies',
            'amount' => '125.50',
            'created_by' => $creator->id,
        ]);
        $staff = $this->userWithRole(UserRole::Staff);

        $this->actingAs($staff)
            ->post(route('expenses.store'), [
                'date' => '2026-06-20',
                'category' => 'Misc',
                'description' => 'Blocked create',
                'amount' => '50',
            ])
            ->assertForbidden();

        $this->actingAs($staff)
            ->patch(route('expenses.update', $expense), [
                'date' => '2026-06-20',
                'category' => 'Misc',
                'description' => 'Blocked update',
                'amount' => '50',
            ])
            ->assertForbidden();

        $this->actingAs($staff)
            ->delete(route('expenses.destroy', $expense))
            ->assertForbidden();
    }

    public function test_treasurer_can_create_update_and_delete_incomes_through_legacy_routes(): void
    {
        $treasurer = $this->userWithRole(UserRole::Treasurer);

        $this->actingAs($treasurer)
            ->post(route('incomes.store'), [
                'date' => '2026-06-15',
                'source' => 'Donation / Fund Drive',
                'description' => 'Community donation',
                'amount' => '500',
            ])
            ->assertRedirect(route('incomes.index'));

        $income = Income::where('description', 'Community donation')->firstOrFail();

        $this->assertSame('2026-06-15', $income->date->toDateString());
        $this->assertSame('500.00', $income->amount);

        $this->actingAs($treasurer)
            ->patch(route('incomes.update', $income), [
                'date' => '2026-06-20',
                'source' => 'Government Aid',
                'description' => 'Aid release',
                'amount' => '750.25',
            ])
            ->assertRedirect(route('incomes.index'));

        $this->assertDatabaseHas('incomes', [
            'id' => $income->id,
            'source' => 'Government Aid',
            'description' => 'Aid release',
            'amount' => '750.25',
        ]);
        $this->assertSame('2026-06-20', $income->refresh()->date->toDateString());

        $this->actingAs($treasurer)
            ->delete(route('incomes.destroy', $income))
            ->assertRedirect(route('incomes.index'));

        $this->assertDatabaseMissing('incomes', [
            'id' => $income->id,
        ]);
    }

    public function test_staff_cannot_create_update_or_delete_incomes(): void
    {
        $income = Income::create([
            'date' => '2026-06-15',
            'source' => 'Donation / Fund Drive',
            'description' => 'Community donation',
            'amount' => '500',
        ]);
        $staff = $this->userWithRole(UserRole::Staff);

        $this->actingAs($staff)
            ->post(route('incomes.store'), [
                'date' => '2026-06-20',
                'source' => 'Government Aid',
                'description' => 'Blocked create',
                'amount' => '750',
            ])
            ->assertForbidden();

        $this->actingAs($staff)
            ->patch(route('incomes.update', $income), [
                'date' => '2026-06-20',
                'source' => 'Government Aid',
                'description' => 'Blocked update',
                'amount' => '750',
            ])
            ->assertForbidden();

        $this->actingAs($staff)
            ->delete(route('incomes.destroy', $income))
            ->assertForbidden();
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
