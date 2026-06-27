<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Contribution;
use App\Models\Dependent;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MemberPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_linked_member_can_view_their_profile(): void
    {
        $member = Member::create([
            'name' => 'Maria Santos',
            'phone' => '09170000000',
            'email' => 'maria@example.com',
        ]);

        $user = User::factory()->create([
            'role' => UserRole::Member->value,
            'member_id' => $member->id,
            'email' => 'old-login@example.com',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('member.portal.show'))
            ->assertOk()
            ->assertSee('Maria Santos')
            ->assertSee('09170000000');
    }

    public function test_member_can_update_allowed_profile_fields_and_dependents(): void
    {
        $member = Member::create([
            'name' => 'Maria Santos',
            'phone' => '09170000000',
            'email' => 'maria@example.com',
            'birthday' => '1990-01-01',
            'indigent' => true,
        ]);

        Dependent::create([
            'member_id' => $member->id,
            'name' => 'Old Dependent',
            'relationship' => 'Child',
        ]);

        $user = User::factory()->create([
            'role' => UserRole::Member->value,
            'member_id' => $member->id,
        ]);

        $this->actingAs($user)
            ->patch(route('member.portal.update'), [
                'name' => 'Changed Name',
                'phone' => '09990000000',
                'email' => 'updated@example.com',
                'birthday' => '1991-02-03',
                'indigent' => false,
                'dependents' => [
                    [
                        'name' => 'Ana Santos',
                        'relationship' => 'Daughter',
                    ],
                    [
                        'name' => '',
                        'relationship' => 'Ignored',
                    ],
                ],
            ])
            ->assertRedirect(route('member.portal.show'));

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'name' => 'Maria Santos',
            'phone' => '09990000000',
            'email' => 'updated@example.com',
            'indigent' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'updated@example.com',
            'email_verified_at' => null,
        ]);

        $this->assertDatabaseMissing('dependents', [
            'member_id' => $member->id,
            'name' => 'Old Dependent',
        ]);

        $this->assertDatabaseHas('dependents', [
            'member_id' => $member->id,
            'name' => 'Ana Santos',
            'relationship' => 'Daughter',
        ]);
    }

    public function test_unlinked_member_account_can_view_setup_message_but_not_update(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Member->value,
            'member_id' => null,
        ]);

        $this->actingAs($user)
            ->get(route('member.portal.show'))
            ->assertOk()
            ->assertSee('Account not linked');

        $this->actingAs($user)
            ->patch(route('member.portal.update'), [
                'phone' => '09990000000',
            ])
            ->assertForbidden();
    }

    public function test_back_office_user_cannot_access_member_portal(): void
    {
        $staff = User::factory()->create([
            'role' => UserRole::Staff->value,
        ]);

        $this->actingAs($staff)
            ->get(route('member.portal.show'))
            ->assertForbidden();
    }

    public function test_member_user_cannot_access_back_office_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Member->value,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    public function test_member_can_view_only_their_own_contribution_status(): void
    {
        $member = Member::create([
            'name' => 'Maria Santos',
            'indigent' => false,
        ]);

        $otherMember = Member::create([
            'name' => 'Other Member',
            'indigent' => false,
        ]);

        Contribution::create([
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
            'amount' => 10,
        ]);

        Contribution::create([
            'member_id' => $member->id,
            'week_start' => '2026-06-14',
            'amount' => 10,
        ]);

        Contribution::create([
            'member_id' => $member->id,
            'week_start' => '2026-01-04',
            'amount' => 10,
        ]);

        Contribution::create([
            'member_id' => $otherMember->id,
            'week_start' => '2026-06-07',
            'amount' => 99,
        ]);

        $user = User::factory()->create([
            'role' => UserRole::Member->value,
            'member_id' => $member->id,
        ]);

        $this->actingAs($user)
            ->get(route('member.portal.show', [
                'year' => 2026,
            ]))
            ->assertOk()
            ->assertSee('Contribution Status')
            ->assertSee('Year Total')
            ->assertSee('30.00')
            ->assertSee('Unpaid Weeks')
            ->assertSee('Paid')
            ->assertSee('Unpaid')
            ->assertSee('Jan 04, 2026')
            ->assertDontSee('99.00')
            ->assertDontSee('Other Member');
    }

    public function test_member_can_filter_contribution_history_by_month(): void
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
            'week_start' => '2026-01-04',
            'amount' => 10,
        ]);

        $user = User::factory()->create([
            'role' => UserRole::Member->value,
            'member_id' => $member->id,
        ]);

        $this->actingAs($user)
            ->get(route('member.portal.show', [
                'year' => 2026,
                'month' => 6,
            ]))
            ->assertOk()
            ->assertSee('June 2026')
            ->assertSee('Jun 07, 2026')
            ->assertDontSee('Jan 04, 2026');
    }

    public function test_indigent_member_contribution_status_has_no_required_balance(): void
    {
        $member = Member::create([
            'name' => 'Maria Santos',
            'indigent' => true,
        ]);

        $user = User::factory()->create([
            'role' => UserRole::Member->value,
            'member_id' => $member->id,
        ]);

        $this->actingAs($user)
            ->get(route('member.portal.show', [
                'year' => 2026,
                'month' => 6,
            ]))
            ->assertOk()
            ->assertSee('No weekly contribution is currently required')
            ->assertSee('Not Required')
            ->assertSee('0.00');
    }

    public function test_member_login_redirects_to_member_portal(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Member->value,
            'password' => bcrypt('password'),
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('member.portal.show'));
    }

    public function test_member_login_ignores_back_office_intended_url(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Member->value,
            'password' => bcrypt('password'),
        ]);

        $this->withSession(['url.intended' => route('dashboard')])
            ->post(route('login'), [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('member.portal.show'));
    }

    public function test_member_can_change_password_from_portal_account(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Member->value,
            'password' => bcrypt('old-password'),
        ]);

        $this->actingAs($user)
            ->put(route('password.update'), [
                'current_password' => 'old-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_member_can_request_password_reset_with_synced_email(): void
    {
        Notification::fake();

        $member = Member::create([
            'name' => 'Maria Santos',
            'email' => 'old@example.com',
        ]);

        $user = User::factory()->create([
            'role' => UserRole::Member->value,
            'member_id' => $member->id,
            'email' => 'old@example.com',
        ]);

        $this->actingAs($user)
            ->patch(route('member.portal.update'), [
                'phone' => '09990000000',
                'email' => 'synced@example.com',
                'birthday' => null,
                'dependents' => [],
            ])
            ->assertRedirect(route('member.portal.show'));

        auth()->logout();

        $this->post(route('password.email'), [
            'email' => 'synced@example.com',
        ])->assertSessionHasNoErrors();

        Notification::assertSentTo($user->refresh(), ResetPassword::class);
    }
}
