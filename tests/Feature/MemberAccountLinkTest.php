<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberAccountLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_linked_to_a_member(): void
    {
        $member = Member::create([
            'name' => 'Maria Santos',
        ]);

        $user = User::factory()->create([
            'member_id' => $member->id,
        ]);

        $this->assertTrue($user->member->is($member));
        $this->assertTrue($member->user->is($user));
    }

    public function test_deleting_member_nulls_user_member_link(): void
    {
        $member = Member::create([
            'name' => 'Maria Santos',
        ]);

        $user = User::factory()->create([
            'member_id' => $member->id,
        ]);

        $member->delete();

        $this->assertNull($user->refresh()->member_id);
    }
}
