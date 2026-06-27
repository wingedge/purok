<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Income;
use App\Models\Inventory;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentLogisticsResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_access_filament_inventory_resource(): void
    {
        Inventory::create([
            'item_name' => 'Chairs',
            'total_quantity' => 10,
            'available_quantity' => 8,
            'rental_rate' => 50,
        ]);

        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get('/admin/inventories')
            ->assertOk()
            ->assertSee('Chairs');
    }

    public function test_treasurer_cannot_access_filament_inventory_resource(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get('/admin/inventories')
            ->assertForbidden();
    }

    public function test_staff_can_access_filament_rentals_resource(): void
    {
        $inventory = $this->inventory();
        $rental = $this->rental($inventory);

        Income::create([
            'date' => '2026-06-15',
            'source' => 'Rentals - Chairs / Table rental',
            'description' => 'Rental income',
            'amount' => 300,
            'rental_id' => $rental->id,
        ]);

        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get('/admin/rentals')
            ->assertOk()
            ->assertSee('Maria Santos');
    }

    public function test_treasurer_cannot_access_filament_rentals_resource(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get('/admin/rentals')
            ->assertForbidden();
    }

    public function test_admin_can_access_filament_logistics_create_pages(): void
    {
        $admin = $this->userWithRole(UserRole::Admin);
        $this->inventory();

        $this->actingAs($admin)
            ->get('/admin/inventories/create')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/rentals/create')
            ->assertOk();
    }

    public function test_staff_can_access_filament_rental_edit_page(): void
    {
        $inventory = $this->inventory();
        $rental = $this->rental($inventory);

        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get("/admin/rentals/{$rental->id}/edit")
            ->assertOk()
            ->assertSee('Maria Santos');
    }

    private function inventory(): Inventory
    {
        return Inventory::create([
            'item_name' => 'Chairs',
            'total_quantity' => 10,
            'available_quantity' => 8,
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
