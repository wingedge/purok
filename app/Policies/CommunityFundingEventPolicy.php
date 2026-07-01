<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CommunityFundingEvent;
use App\Models\User;

final class CommunityFundingEventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-community-funding');
    }

    public function view(User $user, CommunityFundingEvent $event): bool
    {
        return $user->can('view-community-funding');
    }

    public function create(User $user): bool
    {
        return $user->can('manage-community-funding');
    }

    public function update(User $user, CommunityFundingEvent $event): bool
    {
        return $user->can('manage-community-funding');
    }

    public function delete(User $user, CommunityFundingEvent $event): bool
    {
        return $user->can('manage-community-funding');
    }
}
