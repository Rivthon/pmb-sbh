<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminPermissions;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->with('permissions')->paginate(10);
        $canonicalRoles = AdminPermissions::roles();

        return view('admin.roles.index', compact('roles', 'canonicalRoles'));
    }

    public function create()
    {
        $permissionModules = $this->permissionModules();
        return view('admin.roles.create', compact('permissionModules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        // pastikan guard_name sesuai dengan guard permission
        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => AdminPermissions::GUARD,
        ]);

        // kalau permissions dikirim berupa ID, ubah jadi nama permission
        if (!empty($validated['permissions'])) {
            $permissions = Permission::where('guard_name', AdminPermissions::GUARD)
                ->whereIn('id', $validated['permissions'])
                ->pluck('name')
                ->toArray();
            $role->syncPermissions($permissions);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role berhasil dibuat.');
    }

    public function edit(Role $role)
    {
        if ($role->name === AdminPermissions::ROLE_SUPER_ADMIN) {
            return redirect()->route('admin.roles.index')->with('toastError', 'Role Super Admin tidak dapat diedit untuk mencegah hilangnya hak akses penting.');
        }

        $permissionModules = $this->permissionModules();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'permissionModules', 'rolePermissions'));
    }


    public function update(Request $request, Role $role)
    {
        if ($role->name === AdminPermissions::ROLE_SUPER_ADMIN) {
            return redirect()->route('admin.roles.index')->with('toastError', 'Role Super Admin tidak dapat dimodifikasi.');
        }

        $validated = $request->validate([
            'name'        => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ], [
            'name.required' => 'Nama role wajib diisi.',
            'name.unique' => 'Nama role sudah digunakan.',
        ]);

        $role->update(['name' => $validated['name']]);

        // Convert permission IDs to names
        $permissionNames = Permission::where('guard_name', AdminPermissions::GUARD)
            ->whereIn('id', $validated['permissions'] ?? [])
            ->pluck('name')
            ->toArray();

        $role->syncPermissions($permissionNames);

        return redirect()->route('admin.roles.index')->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === AdminPermissions::ROLE_SUPER_ADMIN) {
            return redirect()->route('admin.roles.index')->with('toastError', 'Role Super Admin tidak boleh dihapus.');
        }

        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', 'Role berhasil dihapus.');
    }

    private function permissionModules(): array
    {
        $permissions = Permission::where('guard_name', AdminPermissions::GUARD)
            ->orderBy('name')
            ->get()
            ->keyBy('name');

        return collect(AdminPermissions::modules())
            ->map(fn (array $modulePermissions) => collect($modulePermissions)
                ->map(fn (string $permission) => $permissions->get($permission))
                ->filter()
                ->values())
            ->filter(fn ($modulePermissions) => $modulePermissions->isNotEmpty())
            ->all();
    }
}
