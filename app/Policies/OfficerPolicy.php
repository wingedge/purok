<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Officer;
use App\Models\User;

final class OfficerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage-members');
    }

    public function view(User $user, Officer $officer): bool
    {
        return $user->can('manage-members');
    }

    public function create(User $user): bool
    {
        return $user->can('manage-members');
    }

    public function update(User $user, Officer $officer): bool
    {
        return $user->can('manage-members');
    }

    public function delete(User $user, Officer $officer): bool
    {
        return $user->can('manage-members');
    }
}
