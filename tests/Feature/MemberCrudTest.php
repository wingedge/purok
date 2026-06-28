<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Dependent;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_create_member_with_dependents_through_legacy_route(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->post(route('members.store'), [
                'name' => 'Maria Santos',
                'phone' => '09171234567',
                'email' => 'maria@example.com',
                'birthday' => '1990-05-10',
                'indigent' => '1',
                'dependents' => [
                    ['name' => 'Juan Santos', 'relationship' => 'Child'],
                    ['name' => '', 'relationship' => 'Ignored'],
                ],
            ])
            ->assertRedirect(route('members.index'));

        $this->assertDatabaseHas('members', [
            'name' => 'Maria Santos',
            'phone' => '09171234567',
            'email' => 'maria@example.com',
            'indigent' => true,
        ]);

        $member = Member::where('email', 'maria@example.com')->firstOrFail();
        $this->assertSame('1990-05-10', $member->birthday->toDateString());

        $this->assertDatabaseHas('dependents', [
            'member_id' => $member->id,
            'name' => 'Juan Santos',
            'relationship' => 'Child',
        ]);
        $this->assertSame(1, $member->dependents()->count());
    }

    public function test_staff_can_update_member_and_replace_dependents_through_legacy_route(): void
    {
        $member = Member::create([
            'name' => 'Maria Santos',
            'phone' => '09171234567',
            'email' => 'maria@example.com',
            'birthday' => '1990-05-10',
            'indigent' => true,
        ]);
        Dependent::create([
            'member_id' => $member->id,
            'name' => 'Old Dependent',
            'relationship' => 'Child',
        ]);

        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->patch(route('members.update', $member), [
                'name' => 'Maria Cruz',
                'phone' => '09998887777',
                'email' => 'maria.cruz@example.com',
                'birthday' => '1991-06-11',
                'dependents' => [
                    ['name' => 'Ana Cruz', 'relationship' => 'Daughter'],
                ],
            ])
            ->assertRedirect(route('members.index'));

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'name' => 'Maria Cruz',
            'phone' => '09998887777',
            'email' => 'maria.cruz@example.com',
            'indigent' => false,
        ]);
        $this->assertSame('1991-06-11', $member->refresh()->birthday->toDateString());
        $this->assertDatabaseMissing('dependents', [
            'member_id' => $member->id,
            'name' => 'Old Dependent',
        ]);
        $this->assertDatabaseHas('dependents', [
            'member_id' => $member->id,
            'name' => 'Ana Cruz',
            'relationship' => 'Daughter',
        ]);
    }

    public function test_authorized_users_can_view_member_details_with_dependents(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        Dependent::create([
            'member_id' => $member->id,
            'name' => 'Juan Santos',
            'relationship' => 'Child',
        ]);

        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get(route('members.show', $member))
            ->assertOk()
            ->assertSee('Maria Santos')
            ->assertSee('Juan Santos')
            ->assertSee('Child');
    }

    public function test_only_admin_can_delete_member_through_legacy_route(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        Dependent::create([
            'member_id' => $member->id,
            'name' => 'Juan Santos',
            'relationship' => 'Child',
        ]);

        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->delete(route('members.destroy', $member))
            ->assertForbidden();

        $this->actingAs($this->userWithRole(UserRole::Admin))
            ->delete(route('members.destroy', $member))
            ->assertRedirect(route('members.index'));

        $this->assertDatabaseMissing('members', [
            'id' => $member->id,
        ]);
        $this->assertDatabaseMissing('dependents', [
            'member_id' => $member->id,
        ]);
    }

    public function test_treasurer_cannot_create_or_update_members(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        $treasurer = $this->userWithRole(UserRole::Treasurer);

        $this->actingAs($treasurer)
            ->post(route('members.store'), [
                'name' => 'Ana Cruz',
            ])
            ->assertForbidden();

        $this->actingAs($treasurer)
            ->patch(route('members.update', $member), [
                'name' => 'Maria Cruz',
            ])
            ->assertForbidden();
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
