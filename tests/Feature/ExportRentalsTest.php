<?php

namespace Tests\Feature;

use App\Actions\Exports\ExportRentals;
use App\Enums\UserRole;
use App\Models\Income;
use App\Models\Inventory;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportRentalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_exports_rentals_as_csv(): void
    {
        $inventory = $this->inventory();
        $rental = $this->rental($inventory);

        $income = Income::create([
            'date' => '2026-06-15',
            'source' => 'Rentals - Chairs / Table rental',
            'description' => 'Rental income',
            'amount' => 300,
            'rental_id' => $rental->id,
        ]);

        $csv = app(ExportRentals::class)->execute();
        $rows = $this->parseCsv($csv);

        $this->assertSame([
            'id',
            'inventory_id',
            'inventory_item_name',
            'renter_name',
            'renter_contact',
            'quantity',
            'rent_date',
            'return_date',
            'status',
            'amount',
            'income_id',
            'created_at',
            'updated_at',
        ], $rows[0]);

        $this->assertSame((string) $rental->id, $rows[1][0]);
        $this->assertSame((string) $inventory->id, $rows[1][1]);
        $this->assertSame('Chairs', $rows[1][2]);
        $this->assertSame('Maria Santos', $rows[1][3]);
        $this->assertSame('300.00', $rows[1][9]);
        $this->assertSame((string) $income->id, $rows[1][10]);
    }

    public function test_export_route_downloads_csv_for_staff(): void
    {
        $inventory = $this->inventory();
        $this->rental($inventory);

        $response = $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get(route('rentals.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('rentals-', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('Maria Santos', $response->streamedContent());
    }

    public function test_export_route_is_forbidden_for_treasurer(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get(route('rentals.export'))
            ->assertForbidden();
    }

    /**
     * @return array<int, array<int, string|null>>
     */
    private function parseCsv(string $csv): array
    {
        return array_map('str_getcsv', array_filter(preg_split('/\r\n|\r|\n/', trim($csv)) ?: []));
    }

    private function inventory(): Inventory
    {
        return Inventory::create([
            'item_name' => 'Chairs',
            'total_quantity' => 10,
            'available_quantity' => 10,
            'rental_rate' => 50,
        ]);
    }

    private function rental(Inventory $inventory): Rental
    {
        return Rental::create([
            'inventory_id' => $inventory->id,
            'renter_name' => 'Maria Santos',
            'renter_contact' => '09170000000',
            'quantity' => 2,
            'rent_date' => '2026-06-15',
            'status' => 'rented',
        ]);
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
