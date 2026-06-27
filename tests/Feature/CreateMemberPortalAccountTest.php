<?php

namespace Tests\Feature;

use App\Actions\Members\CreateMemberPortalAccount;
use App\Enums\UserRole;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateMemberPortalAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_member_portal_account(): void
    {
        $member = Member::create([
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
        ]);

        $user = app(CreateMemberPortalAccount::class)->execute($member, [
            'name' => 'Maria Santos',
            'email' => 'maria-login@example.com',
            'password' => 'temporary-password',
        ]);

        $this->assertTrue($user->isMember());
        $this->assertTrue($user->member->is($member));
        $this->assertTrue(Hash::check('temporary-password', $user->password));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Maria Santos',
            'email' => 'maria-login@example.com',
            'role' => UserRole::Member->value,
            'member_id' => $member->id,
        ]);
    }

    public function test_it_updates_an_existing_member_portal_account_without_requiring_a_new_password(): void
    {
        $member = Member::create([
            'name' => 'Maria Santos',
        ]);

        $existingUser = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'password' => 'existing-password',
            'role' => UserRole::Member->value,
            'member_id' => $member->id,
        ]);

        $updatedUser = app(CreateMemberPortalAccount::class)->execute($member, [
            'name' => 'Maria Portal',
            'email' => 'new@example.com',
            'password' => null,
        ]);

        $this->assertTrue($updatedUser->is($existingUser));
        $this->assertTrue(Hash::check('existing-password', $updatedUser->password));

        $this->assertDatabaseHas('users', [
            'id' => $existingUser->id,
            'name' => 'Maria Portal',
            'email' => 'new@example.com',
            'role' => UserRole::Member->value,
            'member_id' => $member->id,
        ]);
    }
}
