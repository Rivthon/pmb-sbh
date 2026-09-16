<?php

namespace App\Providers;

use App\Support\AdminPermissions;
use App\Models\Admin;
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
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function ($user) {
            if ($user instanceof Admin && $user->hasRole(AdminPermissions::ROLE_SUPER_ADMIN)) {
                return true;
            }

            return null;
        });

        foreach (AdminPermissions::all() as $permission) {
            Gate::define($permission, fn ($admin) => method_exists($admin, 'hasPermissionTo') && $admin->hasPermissionTo($permission));
        }
    }
}
