<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Pages\DataExchange;
use App\Models\CommunityFundingDonation;
use App\Models\CommunityFundingEvent;
use App\Models\Contribution;
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
            ->assertSee('Contributions')
            ->assertSee('Community Funding Events')
            ->assertSee('Community Funding Donations')
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

    public function test_treasurer_can_import_contributions_from_filament_page(): void
    {
        $member = Member::create([
            'name' => 'Maria Santos',
            'indigent' => false,
        ]);

        Livewire::actingAs($this->userWithRole(UserRole::Treasurer))
            ->test(DataExchange::class)
            ->set('contributionsCsv', UploadedFile::fake()->createWithContent(
                'contributions.csv',
                "member_id,member_name,week_start,remarks\n{$member->id},,2026-06-07,June collection\n",
            ))
            ->call('importContributions')
            ->assertHasNoErrors()
            ->assertSet('lastImportSummary', 'Created: 1. Updated: 0. Skipped: 0. Failed: 0.');

        $this->assertDatabaseHas('contributions', [
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
            'remarks' => 'June collection',
        ]);
    }

    public function test_treasurer_can_import_community_funding_events_from_filament_page(): void
    {
        Livewire::actingAs($this->userWithRole(UserRole::Treasurer))
            ->test(DataExchange::class)
            ->set('communityFundingEventsCsv', UploadedFile::fake()->createWithContent(
                'community-funding-events.csv',
                "name,description,deadline,goal_amount\nStreet Light Fund,Barangay street lights,2026-08-01,5000\nEmergency Fund,Open-ended support,,\n",
            ))
            ->call('importCommunityFundingEvents')
            ->assertHasNoErrors()
            ->assertSet('lastImportSummary', 'Created: 2. Updated: 0. Skipped: 0. Failed: 0.');

        $this->assertDatabaseHas('community_funding_events', [
            'name' => 'Street Light Fund',
            'goal_amount' => '5000.00',
        ]);
        $this->assertDatabaseHas('community_funding_events', [
            'name' => 'Emergency Fund',
            'goal_amount' => null,
        ]);
    }

    public function test_treasurer_can_import_community_funding_donations_from_filament_page(): void
    {
        $event = CommunityFundingEvent::create([
            'name' => 'Street Light Fund',
            'goal_amount' => 5000,
        ]);
        $member = Member::create([
            'name' => 'Maria Santos',
        ]);

        Livewire::actingAs($this->userWithRole(UserRole::Treasurer))
            ->test(DataExchange::class)
            ->set('communityFundingDonationsCsv', UploadedFile::fake()->createWithContent(
                'community-funding-donations.csv',
                "community_funding_event_id,community_funding_event_name,member_id,member_name,amount,received_at,remarks\n{$event->id},,{$member->id},,250,2026-06-15,First donation\n",
            ))
            ->call('importCommunityFundingDonations')
            ->assertHasNoErrors()
            ->assertSet('lastImportSummary', 'Created: 1. Updated: 0. Skipped: 0. Failed: 0.');

        $this->assertDatabaseHas('community_funding_donations', [
            'community_funding_event_id' => $event->id,
            'member_id' => $member->id,
            'amount' => '250.00',
            'remarks' => 'First donation',
        ]);
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

    public function test_treasurer_can_export_contributions_from_filament_page(): void
    {
        $member = Member::create([
            'name' => 'Exported Member',
            'indigent' => false,
        ]);
        Contribution::create([
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
            'amount' => 10,
        ]);

        Livewire::actingAs($this->userWithRole(UserRole::Treasurer))
            ->test(DataExchange::class)
            ->call('exportContributions')
            ->assertFileDownloaded('contributions-'.now()->format('Y-m-d').'.csv');
    }

    public function test_treasurer_can_export_community_funding_from_filament_page(): void
    {
        $event = CommunityFundingEvent::create([
            'name' => 'Exported Funding',
            'goal_amount' => 5000,
        ]);
        $member = Member::create(['name' => 'Exported Member']);
        CommunityFundingDonation::create([
            'community_funding_event_id' => $event->id,
            'member_id' => $member->id,
            'amount' => 250,
            'received_at' => '2026-06-15',
        ]);

        Livewire::actingAs($this->userWithRole(UserRole::Treasurer))
            ->test(DataExchange::class)
            ->call('exportCommunityFundingEvents')
            ->assertFileDownloaded('community-funding-events-'.now()->format('Y-m-d').'.csv');

        Livewire::actingAs($this->userWithRole(UserRole::Treasurer))
            ->test(DataExchange::class)
            ->call('exportCommunityFundingDonations')
            ->assertFileDownloaded('community-funding-donations-'.now()->format('Y-m-d').'.csv');
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
