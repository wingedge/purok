<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user): ?bool {
            return $user->role === UserRole::Admin->value ? true : null;
        });

        Gate::define('view-members', fn (User $user): bool => in_array($user->role, [
            UserRole::Treasurer->value,
            UserRole::Staff->value,
        ], true));

        Gate::define('manage-members', fn (User $user): bool => $user->role === UserRole::Staff->value);

        Gate::define('delete-members', fn (User $user): bool => false);

        Gate::define('manage-contributions', fn (User $user): bool => $user->role === UserRole::Treasurer->value);

        Gate::define('manage-finances', fn (User $user): bool => $user->role === UserRole::Treasurer->value);

        Gate::define('manage-inventory', fn (User $user): bool => $user->role === UserRole::Staff->value);

        Gate::define('manage-rentals', fn (User $user): bool => $user->role === UserRole::Staff->value);

        Gate::define('manage-certificates', fn (User $user): bool => $user->role === UserRole::Staff->value);

        Gate::define('view-cashflow-reports', fn (User $user): bool => $user->role === UserRole::Treasurer->value);

        Gate::define('view-contribution-reports', fn (User $user): bool => in_array($user->role, [
            UserRole::Treasurer->value,
            UserRole::Staff->value,
        ], true));
    }
}
