<?php

declare(strict_types=1);

namespace App\Actions\Members;

use App\Enums\UserRole;
use App\Models\Member;
use App\Models\User;

class CreateMemberPortalAccount
{
    /**
     * @param array{name: string, email: string, password?: string|null} $data
     */
    public function execute(Member $member, array $data): User
    {
        $user = $member->user;

        if ($user === null) {
            return User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => UserRole::Member->value,
                'member_id' => $member->id,
            ]);
        }

        $attributes = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => UserRole::Member->value,
            'member_id' => $member->id,
        ];

        if (! empty($data['password'])) {
            $attributes['password'] = $data['password'];
        }

        $user->update($attributes);

        return $user->refresh();
    }
}
