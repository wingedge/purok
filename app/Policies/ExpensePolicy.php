<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

final class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage-finances');
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->can('manage-finances');
    }

    public function create(User $user): bool
    {
        return $user->can('manage-finances');
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->can('manage-finances');
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->can('manage-finances');
    }
}
