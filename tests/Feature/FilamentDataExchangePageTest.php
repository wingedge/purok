<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Pages\DataExchange;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentDataExchangePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_view_member_and_rental_exchange_options(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get('/admin/data-exchange')
            ->assertOk()
            ->assertSee('Members And Dependents')
            ->assertSee('Inventory')
            ->assertSee('Rentals')
            ->assertDontSee('Expenses')
            ->assertDontSee('Incomes');
    }

    public function test_treasurer_can_view_finance_exchange_options(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get('/admin/data-exchange')
            ->assertOk()
            ->assertSee('Expenses')
            ->assertSee('Incomes')
            ->assertDontSee('Members And Dependents')
            ->assertDontSee('Inventory')
            ->assertDontSee('Rentals');
    }

    public function test_member_cannot_view_filament_data_exchange_page(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Member))
            ->get('/admin/data-exchange')
            ->assertForbidden();
    }

    public function test_staff_can_import_members_from_filament_page(): void
    {
        Livewire::actingAs($this->userWithRole(UserRole::Staff))
            ->test(DataExchange::class)
            ->set('membersCsv', UploadedFile::fake()->createWithContent(
                'members.csv',
                "name,phone,email,birthday,indigent,dependent_names,dependent_relationships\nMaria Santos,0917,maria@example.com,1990-01-01,no,Juan Santos|Ana Santos,Son|Daughter\n",
            ))
            ->call('importMembers')
            ->assertHasNoErrors()
            ->assertSet('lastImportSummary', 'Created: 1. Updated: 0. Skipped: 0. Failed: 0.');

        $this->assertDatabaseHas('members', [
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
        ]);
    }

    public function test_treasurer_can_import_expenses_from_filament_page(): void
    {
        $treasurer = $this->userWithRole(UserRole::Treasurer);

        Livewire::actingAs($treasurer)
            ->test(DataExchange::class)
            ->set('expensesCsv', UploadedFile::fake()->createWithContent(
                'expenses.csv',
                "date,category,description,amount\n2026-06-01,Utilities,Water bill,250.50\n",
            ))
            ->call('importExpenses')
            ->assertHasNoErrors()
            ->assertSet('lastImportSummary', 'Created: 1. Updated: 0. Skipped: 0. Failed: 0.');

        $this->assertDatabaseHas('expenses', [
            'category' => 'Utilities',
            'created_by' => $treasurer->id,
        ]);
    }

    public function test_staff_cannot_import_expenses_from_filament_page(): void
    {
        Livewire::actingAs($this->userWithRole(UserRole::Staff))
            ->test(DataExchange::class)
            ->set('expensesCsv', UploadedFile::fake()->createWithContent(
                'expenses.csv',
                "date,category,description,amount\n2026-06-01,Utilities,Water bill,250.50\n",
            ))
            ->call('importExpenses')
            ->assertForbidden();
    }

    public function test_staff_can_import_inventories_from_filament_page(): void
    {
        Livewire::actingAs($this->userWithRole(UserRole::Staff))
            ->test(DataExchange::class)
            ->set('inventoriesCsv', UploadedFile::fake()->createWithContent(
                'inventories.csv',
                "item_name,total_quantity,available_quantity,rental_rate\nChairs,20,15,25.50\n",
            ))
            ->call('importInventories')
            ->assertHasNoErrors()
            ->assertSet('lastImportSummary', 'Created: 1. Updated: 0. Skipped: 0. Failed: 0.');

        $this->assertDatabaseHas('inventories', [
            'item_name' => 'Chairs',
            'total_quantity' => 20,
            'available_quantity' => 15,
        ]);
    }

    public function test_filament_page_can_export_allowed_csv(): void
    {
        $treasurer = $this->userWithRole(UserRole::Treasurer);

        Expense::create([
            'date' => '2026-06-15',
            'category' => 'Utilities',
            'description' => 'Water bill',
            'amount' => 250.50,
            'created_by' => $treasurer->id,
        ]);

        Livewire::actingAs($treasurer)
            ->test(DataExchange::class)
            ->call('exportExpenses')
            ->assertFileDownloaded('expenses-'.now()->format('Y-m-d').'.csv');
    }

    public function test_staff_can_export_members_from_filament_page(): void
    {
        Member::create(['name' => 'Exported Member']);

        Livewire::actingAs($this->userWithRole(UserRole::Staff))
            ->test(DataExchange::class)
            ->call('exportMembers')
            ->assertFileDownloaded('members-'.now()->format('Y-m-d').'.csv');
    }

    public function test_staff_can_export_inventories_from_filament_page(): void
    {
        Inventory::create([
            'item_name' => 'Exported Chairs',
            'total_quantity' => 20,
            'available_quantity' => 15,
            'rental_rate' => 25.50,
        ]);

        Livewire::actingAs($this->userWithRole(UserRole::Staff))
            ->test(DataExchange::class)
            ->call('exportInventories')
            ->assertFileDownloaded('inventories-'.now()->format('Y-m-d').'.csv');
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
