<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Inventory;
use App\Models\User;

final class InventoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage-inventory');
    }

    public function view(User $user, Inventory $inventory): bool
    {
        return $user->can('manage-inventory');
    }

    public function create(User $user): bool
    {
        return $user->can('manage-inventory');
    }

    public function update(User $user, Inventory $inventory): bool
    {
        return $user->can('manage-inventory');
    }

    public function delete(User $user, Inventory $inventory): bool
    {
        return $user->can('manage-inventory');
    }
}
