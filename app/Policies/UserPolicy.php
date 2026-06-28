<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage-users');
    }

    public function view(User $user, User $targetUser): bool
    {
        return $user->can('manage-users');
    }

    public function create(User $user): bool
    {
        return $user->can('manage-users');
    }

    public function update(User $user, User $targetUser): bool
    {
        return $user->can('manage-users');
    }

    public function delete(User $user, User $targetUser): bool
    {
        return $user->can('manage-users')
            && $targetUser->getKey() !== $user->getKey();
    }
}
