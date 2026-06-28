<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Contribution;
use App\Models\User;

final class ContributionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage-contributions');
    }

    public function view(User $user, Contribution $contribution): bool
    {
        return $user->can('manage-contributions');
    }

    public function create(User $user): bool
    {
        return $user->can('manage-contributions');
    }

    public function update(User $user, Contribution $contribution): bool
    {
        return $user->can('manage-contributions');
    }

    public function delete(User $user, Contribution $contribution): bool
    {
        return $user->can('manage-contributions');
    }
}
