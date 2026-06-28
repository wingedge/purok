<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Member;
use App\Models\User;

final class MemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-members');
    }

    public function view(User $user, Member $member): bool
    {
        return $user->can('view-members');
    }

    public function create(User $user): bool
    {
        return $user->can('manage-members');
    }

    public function update(User $user, Member $member): bool
    {
        return $user->can('manage-members');
    }

    public function delete(User $user, Member $member): bool
    {
        return $user->can('delete-members');
    }
}
