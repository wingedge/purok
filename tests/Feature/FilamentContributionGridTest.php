<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Pages\ContributionGrid;
use App\Models\Contribution;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentContributionGridTest extends TestCase
{
    use RefreshDatabase;

    public function test_treasurer_can_view_filament_contribution_grid(): void
    {
        Member::create([
            'name' => 'Maria Santos',
            'indigent' => false,
        ]);

        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get('/admin/contribution-grid?year=2026&month=6')
            ->assertOk()
            ->assertSee('Contribution Grid')
            ->assertSee('Maria Santos')
            ->assertSee('Jun 07');
    }

    public function test_staff_cannot_view_filament_contribution_grid(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get('/admin/contribution-grid')
            ->assertForbidden();
    }

    public function test_grid_excludes_indigent_members(): void
    {
        Member::create([
            'name' => 'Regular Member',
            'indigent' => false,
        ]);
        Member::create([
            'name' => 'Indigent Member',
            'indigent' => true,
        ]);

        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get('/admin/contribution-grid')
            ->assertOk()
            ->assertSee('Regular Member')
            ->assertDontSee('Indigent Member');
    }

    public function test_grid_can_record_and_remove_contribution(): void
    {
        $member = Member::create([
            'name' => 'Maria Santos',
            'indigent' => false,
        ]);

        $this->actingAs($this->userWithRole(UserRole::Treasurer));

        Livewire::test(ContributionGrid::class)
            ->call('toggleContribution', $member->id, '2026-06-07');

        $this->assertDatabaseHas('contributions', [
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
            'amount' => 10,
        ]);

        Livewire::test(ContributionGrid::class)
            ->call('toggleContribution', $member->id, '2026-06-07');

        $this->assertDatabaseMissing('contributions', [
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
        ]);
    }

    public function test_grid_shows_year_total(): void
    {
        $member = Member::create([
            'name' => 'Maria Santos',
            'indigent' => false,
        ]);
        Contribution::create([
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
            'amount' => 10,
        ]);
        Contribution::create([
            'member_id' => $member->id,
            'week_start' => '2026-07-05',
            'amount' => 10,
        ]);

        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get('/admin/contribution-grid?year=2026&month=6')
            ->assertOk()
            ->assertSee('PHP 20.00');
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
