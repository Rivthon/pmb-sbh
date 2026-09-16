<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Support\AdminPermissions;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SyncAdminPermissions extends Command
{
    protected $signature = 'pmb:sync-admin-permissions
        {--dry-run : Tampilkan rencana perubahan tanpa menyimpan}
        {--audit : Tampilkan audit role dan permission existing}
        {--assign-super-admin= : Email admin yang akan diberi role Super Admin}
        {--sync-legacy : Sinkronkan role legacy ke mapping permission baru}';

    protected $description = 'Sinkronisasi role dan permission admin PMB secara aman dan idempotent.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($this->option('audit')) {
            $this->audit();
        }

        $this->info($dryRun ? 'DRY RUN: tidak ada data yang disimpan.' : 'Sinkronisasi role/permission dimulai.');

        $this->syncPermissions($dryRun);
        $this->syncRoles($dryRun, AdminPermissions::roleMap());

        if ($this->option('sync-legacy')) {
            $this->syncRoles($dryRun, AdminPermissions::legacyRoleMap(), legacy: true);
            $this->syncLegacyPermissionAssignments($dryRun);
        }

        if ($email = $this->option('assign-super-admin')) {
            $this->assignSuperAdmin($email, $dryRun);
        }

        if (!$dryRun) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        $this->info('Selesai.');

        return self::SUCCESS;
    }

    private function audit(): void
    {
        $this->line('');
        $this->info('Audit role existing');

        Role::withCount('permissions')
            ->where('guard_name', AdminPermissions::GUARD)
            ->orderBy('name')
            ->get()
            ->each(fn (Role $role) => $this->line("- {$role->name}: {$role->permissions_count} permission"));

        $this->line('');
        $this->info('Permission canonical missing');

        $existing = Permission::where('guard_name', AdminPermissions::GUARD)->pluck('name')->all();
        $missing = array_diff(AdminPermissions::all(), $existing);

        if (!$missing) {
            $this->line('Tidak ada permission canonical yang hilang.');
        }

        foreach ($missing as $permission) {
            $this->warn("- {$permission}");
        }

        $legacyPermissions = Permission::where('guard_name', AdminPermissions::GUARD)
            ->whereIn('name', array_keys(AdminPermissions::legacyPermissionMap()))
            ->pluck('name')
            ->all();

        if ($legacyPermissions) {
            $this->line('');
            $this->warn('Permission legacy masih ada dan tidak akan dihapus otomatis:');
            foreach ($legacyPermissions as $permission) {
                $this->line("- {$permission}");
            }
        }

        $this->line('');
    }

    private function syncPermissions(bool $dryRun): void
    {
        foreach (AdminPermissions::all() as $permission) {
            $this->upsertPermission($permission, $dryRun);
        }

        foreach (array_keys(AdminPermissions::legacyPermissionMap()) as $permission) {
            $this->upsertPermission($permission, $dryRun);
        }
    }

    private function upsertPermission(string $permission, bool $dryRun): void
    {
        if ($dryRun) {
            $exists = Permission::where('name', $permission)
                ->where('guard_name', AdminPermissions::GUARD)
                ->exists();
            $this->line(($exists ? 'exists' : 'create') . " permission: {$permission}");
            return;
        }

        Permission::firstOrCreate([
            'name' => $permission,
            'guard_name' => AdminPermissions::GUARD,
        ]);
    }

    private function syncRoles(bool $dryRun, array $roleMap, bool $legacy = false): void
    {
        foreach ($roleMap as $roleName => $permissions) {
            if ($dryRun) {
                $this->line(($legacy ? 'legacy ' : '') . "sync role: {$roleName} => " . count($permissions) . ' permission');
                continue;
            }

            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => AdminPermissions::GUARD,
            ])->syncPermissions($permissions);
        }
    }

    private function syncLegacyPermissionAssignments(bool $dryRun): void
    {
        $map = AdminPermissions::legacyPermissionMap();

        Role::where('guard_name', AdminPermissions::GUARD)
            ->with('permissions')
            ->get()
            ->each(function (Role $role) use ($map, $dryRun) {
                $newPermissions = [];

                foreach ($role->permissions as $permission) {
                    if (isset($map[$permission->name])) {
                        $newPermissions[] = $map[$permission->name];
                    }
                }

                if (!$newPermissions) {
                    return;
                }

                $merged = array_values(array_unique(array_merge(
                    $role->permissions->pluck('name')->all(),
                    $newPermissions
                )));

                if ($dryRun) {
                    $this->line("legacy permission map role {$role->name}: +" . implode(', +', $newPermissions));
                    return;
                }

                $role->syncPermissions($merged);
            });
    }

    private function assignSuperAdmin(string $email, bool $dryRun): void
    {
        $admin = Admin::where('email', $email)->first();

        if (!$admin) {
            $this->error("Admin dengan email {$email} tidak ditemukan.");
            return;
        }

        if ($dryRun) {
            $this->line("assign role Super Admin to {$email}");
            return;
        }

        $admin->assignRole(AdminPermissions::ROLE_SUPER_ADMIN);
        $this->info("Role Super Admin diberikan ke {$email}.");
    }
}
