<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Expenses\CreateExpense;
use App\Actions\Expenses\DeleteExpense;
use App\Actions\Expenses\UpdateExpense;
use App\Actions\Incomes\CreateIncome;
use App\Actions\Incomes\DeleteIncome;
use App\Actions\Incomes\UpdateIncome;
use App\Enums\UserRole;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_actions_create_update_and_delete_expenses(): void
    {
        $treasurer = $this->userWithRole(UserRole::Treasurer);

        app(CreateExpense::class)->execute([
            'date' => '2026-06-15',
            'category' => 'Misc',
            'description' => 'Office supplies',
            'amount' => '125.50',
        ], $treasurer);

        $expense = Expense::where('description', 'Office supplies')->firstOrFail();

        $this->assertSame($treasurer->id, $expense->created_by);
        $this->assertSame('2026-06-15', $expense->date->toDateString());
        $this->assertSame('125.50', $expense->amount);

        app(UpdateExpense::class)->execute($expense, [
            'date' => '2026-06-20',
            'category' => 'Community Services	Health Programs, Feeding, Cleanup Drives',
            'description' => 'Cleanup supplies',
            'amount' => '300',
        ]);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'category' => 'Community Services	Health Programs, Feeding, Cleanup Drives',
            'description' => 'Cleanup supplies',
            'amount' => '300.00',
            'created_by' => $treasurer->id,
        ]);
        $this->assertSame('2026-06-20', $expense->refresh()->date->toDateString());

        app(DeleteExpense::class)->execute($expense);

        $this->assertDatabaseMissing('expenses', [
            'id' => $expense->id,
        ]);
    }

    public function test_old_expense_mutation_routes_are_not_available(): void
    {
        $creator = $this->userWithRole(UserRole::Treasurer);
        $expense = Expense::create([
            'date' => '2026-06-15',
            'category' => 'Misc',
            'description' => 'Office supplies',
            'amount' => '125.50',
            'created_by' => $creator->id,
        ]);
        $response = $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->post('/expenses', [
                'date' => '2026-06-20',
                'category' => 'Misc',
                'description' => 'Blocked create',
                'amount' => '50',
            ]);
        $this->assertContains($response->getStatusCode(), [404, 405]);

        $response = $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->patch("/expenses/{$expense->id}", [
                'date' => '2026-06-20',
                'category' => 'Misc',
                'description' => 'Blocked update',
                'amount' => '50',
            ]);
        $this->assertContains($response->getStatusCode(), [404, 405]);

        $response = $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->delete("/expenses/{$expense->id}");
        $this->assertContains($response->getStatusCode(), [404, 405]);
    }

    public function test_income_actions_create_update_and_delete_incomes(): void
    {
        app(CreateIncome::class)->execute([
            'date' => '2026-06-15',
            'source' => 'Donation / Fund Drive',
            'description' => 'Community donation',
            'amount' => '500',
        ]);

        $income = Income::where('description', 'Community donation')->firstOrFail();

        $this->assertSame('2026-06-15', $income->date->toDateString());
        $this->assertSame('500.00', $income->amount);

        app(UpdateIncome::class)->execute($income, [
            'date' => '2026-06-20',
            'source' => 'Government Aid',
            'description' => 'Aid release',
            'amount' => '750.25',
        ]);

        $this->assertDatabaseHas('incomes', [
            'id' => $income->id,
            'source' => 'Government Aid',
            'description' => 'Aid release',
            'amount' => '750.25',
        ]);
        $this->assertSame('2026-06-20', $income->refresh()->date->toDateString());

        app(DeleteIncome::class)->execute($income);

        $this->assertDatabaseMissing('incomes', [
            'id' => $income->id,
        ]);
    }

    public function test_old_income_mutation_routes_are_not_available(): void
    {
        $income = Income::create([
            'date' => '2026-06-15',
            'source' => 'Donation / Fund Drive',
            'description' => 'Community donation',
            'amount' => '500',
        ]);
        $response = $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->post('/incomes', [
                'date' => '2026-06-20',
                'source' => 'Government Aid',
                'description' => 'Blocked create',
                'amount' => '750',
            ]);
        $this->assertContains($response->getStatusCode(), [404, 405]);

        $response = $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->patch("/incomes/{$income->id}", [
                'date' => '2026-06-20',
                'source' => 'Government Aid',
                'description' => 'Blocked update',
                'amount' => '750',
            ]);
        $this->assertContains($response->getStatusCode(), [404, 405]);

        $response = $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->delete("/incomes/{$income->id}");
        $this->assertContains($response->getStatusCode(), [404, 405]);
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
