<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Contribution;
use App\Models\Dependent;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
                'month' => 6,
            ]))
            ->assertOk()
            ->assertSee('Contribution Status')
            ->assertSee('Year Total')
            ->assertSee('20.00')
            ->assertSee('Unpaid Weeks')
            ->assertSee('Paid')
            ->assertSee('Unpaid')
            ->assertDontSee('99.00')
            ->assertDontSee('Other Member');
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
}
