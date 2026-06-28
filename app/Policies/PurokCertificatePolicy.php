<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PurokCertificate;
use App\Models\User;

final class PurokCertificatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage-certificates');
    }

    public function view(User $user, PurokCertificate $purokCertificate): bool
    {
        return $user->can('manage-certificates');
    }

    public function create(User $user): bool
    {
        return $user->can('manage-certificates');
    }

    public function update(User $user, PurokCertificate $purokCertificate): bool
    {
        return $user->can('manage-certificates');
    }

    public function delete(User $user, PurokCertificate $purokCertificate): bool
    {
        return $user->can('manage-certificates');
    }
}
