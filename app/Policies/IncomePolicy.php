<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Income;
use App\Models\User;

final class IncomePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage-finances');
    }

    public function view(User $user, Income $income): bool
    {
        return $user->can('manage-finances');
    }

    public function create(User $user): bool
    {
        return $user->can('manage-finances');
    }

    public function update(User $user, Income $income): bool
    {
        return $user->can('manage-finances');
    }

    public function delete(User $user, Income $income): bool
    {
        return $user->can('manage-finances');
    }
}
