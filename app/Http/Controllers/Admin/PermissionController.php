<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminPermissions;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Permission::with('roles');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhereHas('roles', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                });
        }

        $permissions = $query->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.permissions.index', compact('permissions'));
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        Permission::firstOrCreate([
            'name' => $validated['name'],
            'guard_name' => AdminPermissions::GUARD,
        ]);

        return redirect()->route('admin.permissions.index')->with('success', 'Permission berhasil dibuat.');
    }

    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update(['name' => $validated['name']]);

        return redirect()->route('admin.permissions.index')->with('success', 'Permission berhasil diperbarui.');
    }

    public function destroy(Permission $permission)
    {
        if (in_array($permission->name, AdminPermissions::all(), true)) {
            return redirect()->route('admin.permissions.index')->with('toastError', 'Permission canonical tidak boleh dihapus dari UI.');
        }

        $permission->delete();
        return redirect()->route('admin.permissions.index')->with('success', 'Permission berhasil dihapus.');
    }
}
