<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Members\CreateMember;
use App\Actions\Members\DeleteMember;
use App\Actions\Members\UpdateMember;
use App\Enums\UserRole;
use App\Models\Dependent;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_member_action_creates_member_with_dependents(): void
    {
        app(CreateMember::class)->execute([
            'name' => 'Maria Santos',
            'phone' => '09171234567',
            'email' => 'maria@example.com',
            'birthday' => '1990-05-10',
            'indigent' => true,
            'dependents' => [
                ['name' => 'Juan Santos', 'relationship' => 'Child'],
                ['name' => '', 'relationship' => 'Ignored'],
            ],
        ]);

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

    public function test_update_member_action_updates_member_and_replaces_dependents(): void
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

        app(UpdateMember::class)->execute($member, [
            'name' => 'Maria Cruz',
            'phone' => '09998887777',
            'email' => 'maria.cruz@example.com',
            'birthday' => '1991-06-11',
            'indigent' => false,
            'dependents' => [
                ['name' => 'Ana Cruz', 'relationship' => 'Daughter'],
            ],
        ]);

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

    public function test_old_member_show_route_redirects_to_filament_members(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        Dependent::create([
            'member_id' => $member->id,
            'name' => 'Juan Santos',
            'relationship' => 'Child',
        ]);

        $this->actingAs($this->userWithRole(UserRole::Treasurer))
            ->get(route('members.show', $member))
            ->assertRedirect('/admin/members');
    }

    public function test_delete_member_action_deletes_member_and_dependents(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        Dependent::create([
            'member_id' => $member->id,
            'name' => 'Juan Santos',
            'relationship' => 'Child',
        ]);

        app(DeleteMember::class)->execute($member);

        $this->assertDatabaseMissing('members', [
            'id' => $member->id,
        ]);
        $this->assertDatabaseMissing('dependents', [
            'member_id' => $member->id,
        ]);
    }

    public function test_old_member_mutation_routes_are_not_available(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);

        $this->actingAs($this->userWithRole(UserRole::Admin))
            ->post('/members', [
                'name' => 'Ana Cruz',
            ])
            ->assertMethodNotAllowed();

        $this->actingAs($this->userWithRole(UserRole::Admin))
            ->patch("/members/{$member->id}", [
                'name' => 'Maria Cruz',
            ])
            ->assertMethodNotAllowed();
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
