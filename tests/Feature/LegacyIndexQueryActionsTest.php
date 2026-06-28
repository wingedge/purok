<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Expenses\ListExpenses;
use App\Actions\Incomes\ListIncomes;
use App\Actions\Members\ListMembers;
use App\Models\Dependent;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyIndexQueryActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_list_action_filters_by_name_and_counts_dependents(): void
    {
        $matchingMember = Member::create(['name' => 'Maria Santos']);
        Dependent::create([
            'member_id' => $matchingMember->id,
            'name' => 'Juan Santos',
            'relationship' => 'Child',
        ]);
        Member::create(['name' => 'Ana Cruz']);

        $members = app(ListMembers::class)->execute('Maria');

        $this->assertCount(1, $members->items());
        $this->assertTrue($members->first()->is($matchingMember));
        $this->assertSame(1, $members->first()->dependents_count);
    }

    public function test_expense_list_action_orders_newest_first_and_loads_creator(): void
    {
        $creator = User::factory()->create();
        $olderExpense = Expense::create([
            'date' => '2026-06-01',
            'category' => 'Misc',
            'description' => 'Older expense',
            'amount' => '100',
            'created_by' => $creator->id,
        ]);
        $newerExpense = Expense::create([
            'date' => '2026-06-15',
            'category' => 'Misc',
            'description' => 'Newer expense',
            'amount' => '200',
            'created_by' => $creator->id,
        ]);

        $expenses = app(ListExpenses::class)->execute();

        $this->assertTrue($expenses->first()->is($newerExpense));
        $this->assertTrue($expenses->last()->is($olderExpense));
        $this->assertTrue($expenses->first()->relationLoaded('creator'));
    }

    public function test_income_list_action_orders_newest_first(): void
    {
        $olderIncome = Income::create([
            'date' => '2026-06-01',
            'source' => 'Donation / Fund Drive',
            'description' => 'Older income',
            'amount' => '100',
        ]);
        $newerIncome = Income::create([
            'date' => '2026-06-15',
            'source' => 'Government Aid',
            'description' => 'Newer income',
            'amount' => '200',
        ]);

        $incomes = app(ListIncomes::class)->execute();

        $this->assertTrue($incomes->first()->is($newerIncome));
        $this->assertTrue($incomes->last()->is($olderIncome));
    }
}
