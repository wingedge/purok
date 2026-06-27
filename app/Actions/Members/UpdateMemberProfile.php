<?php

declare(strict_types=1);

namespace App\Actions\Members;

use App\Models\Member;
use App\Models\User;

class UpdateMemberProfile
{
    /**
     * @param array{phone?: string|null, email?: string|null, birthday?: string|null} $data
     */
    public function execute(Member $member, User $user, array $data): Member
    {
        $member->update([
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'birthday' => $data['birthday'] ?? null,
        ]);

        if ($user->member_id === $member->id && array_key_exists('email', $data)) {
            $user->email = $data['email'];

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();
        }

        return $member->refresh();
    }
}
