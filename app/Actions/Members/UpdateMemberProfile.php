<?php

declare(strict_types=1);

namespace App\Actions\Members;

use App\Models\Member;

class UpdateMemberProfile
{
    /**
     * @param array{phone?: string|null, email?: string|null, birthday?: string|null} $data
     */
    public function execute(Member $member, array $data): Member
    {
        $member->update([
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'birthday' => $data['birthday'] ?? null,
        ]);

        return $member->refresh();
    }
}
