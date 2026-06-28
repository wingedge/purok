<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Inventory;
use App\Models\Member;
use App\Models\PurokCertificate;
use App\Models\Rental;
use App\Models\User;
use App\Policies\ExpensePolicy;
use App\Policies\IncomePolicy;
use App\Policies\InventoryPolicy;
use App\Policies\MemberPolicy;
use App\Policies\PurokCertificatePolicy;
use App\Policies\RentalPolicy;
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
        Member::class => MemberPolicy::class,
        Expense::class => ExpensePolicy::class,
        Income::class => IncomePolicy::class,
        Inventory::class => InventoryPolicy::class,
        Rental::class => RentalPolicy::class,
        PurokCertificate::class => PurokCertificatePolicy::class,
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

        Gate::define('view-dashboard', fn (User $user): bool => in_array($user->role, [
            UserRole::Treasurer->value,
            UserRole::Staff->value,
        ], true));

        Gate::define('manage-users', fn (User $user): bool => false);

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
