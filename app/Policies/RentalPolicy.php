<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Rental;
use App\Models\User;

final class RentalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage-rentals');
    }

    public function view(User $user, Rental $rental): bool
    {
        return $user->can('manage-rentals');
    }

    public function create(User $user): bool
    {
        return $user->can('manage-rentals');
    }

    public function update(User $user, Rental $rental): bool
    {
        return $user->can('manage-rentals');
    }

    public function delete(User $user, Rental $rental): bool
    {
        return $user->can('manage-rentals');
    }
}
