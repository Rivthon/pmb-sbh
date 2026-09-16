<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Support\AdminPermissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (AdminPermissions::all() as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => AdminPermissions::GUARD,
            ]);
        }

        foreach (AdminPermissions::legacyPermissionMap() as $legacy => $replacement) {
            Permission::firstOrCreate([
                'name' => $legacy,
                'guard_name' => AdminPermissions::GUARD,
            ]);
            Permission::firstOrCreate([
                'name' => $replacement,
                'guard_name' => AdminPermissions::GUARD,
            ]);
        }

        foreach (AdminPermissions::roleMap() as $roleName => $permissions) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => AdminPermissions::GUARD,
            ])->syncPermissions($permissions);
        }

        foreach (AdminPermissions::legacyRoleMap() as $roleName => $permissions) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => AdminPermissions::GUARD,
            ])->syncPermissions($permissions);
        }

        $admin = Admin::first();

        if ($admin && !$admin->hasRole(AdminPermissions::ROLE_SUPER_ADMIN)) {
            $admin->assignRole(AdminPermissions::ROLE_SUPER_ADMIN);
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
