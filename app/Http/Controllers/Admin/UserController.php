<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Jurusan;
use App\Support\AdminPermissions;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = Admin::with('roles');

        // Search by name/email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $roleName = $request->role;
            $query->whereHas('roles', function ($q) use ($roleName) {
                $q->where('name', $roleName);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'aktif') {
                $query->where('is_active', true);
            } elseif ($status === 'nonaktif') {
                $query->where('is_active', false);
            }
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        $jurusans = Jurusan::interviewAccountOptions();
        return view('admin.users.create', compact('roles', 'jurusans'));
    }

    public function store(Request $request)
    {
        $interviewerJurusanIds = Jurusan::interviewAccountOptions()->pluck('id')->all();

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:admins,email',
            'password' => 'required|min:6|confirmed',
            'roles'    => 'array',
            'jurusan_id' => [
                Rule::requiredIf(fn (): bool => $this->hasInterviewerRole($request->input('roles', []))),
                'nullable',
                'integer',
                'exists:jurusan,id',
                Rule::in($interviewerJurusanIds),
            ],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = Admin::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'is_active' => true,
            'jurusan_id' => $this->hasInterviewerRole($validated['roles'] ?? []) ? $validated['jurusan_id'] : null,
        ]);

        if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(Admin $user)
    {
        // Admin biasa tidak bisa edit Super Admin
        if ($user->hasRole(AdminPermissions::ROLE_SUPER_ADMIN) && !auth('admin')->user()->hasRole(AdminPermissions::ROLE_SUPER_ADMIN)) {
            return redirect()->route('admin.users.index')->with('toastError', 'Hanya Super Admin yang diizinkan mengedit akun Super Admin.');
        }

        $roles = Role::all();
        $jurusans = Jurusan::interviewAccountOptions();
        $userRoles = $user->roles->pluck('name')->toArray();

        return view('admin.users.edit', compact('user', 'roles', 'userRoles', 'jurusans'));
    }

    public function update(Request $request, Admin $user)
    {
        // Admin biasa tidak bisa edit Super Admin
        if ($user->hasRole(AdminPermissions::ROLE_SUPER_ADMIN) && !auth('admin')->user()->hasRole(AdminPermissions::ROLE_SUPER_ADMIN)) {
            return redirect()->route('admin.users.index')->with('toastError', 'Hanya Super Admin yang diizinkan mengubah akun Super Admin.');
        }

        $interviewerJurusanIds = Jurusan::interviewAccountOptions()->pluck('id')->all();

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:admins,email,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
            'roles'    => 'array',
            'jurusan_id' => [
                Rule::requiredIf(fn (): bool => $this->hasInterviewerRole($request->input('roles', []))),
                'nullable',
                'integer',
                'exists:jurusan,id',
                Rule::in($interviewerJurusanIds),
            ],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user->update([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password,
            'jurusan_id' => $this->hasInterviewerRole($validated['roles'] ?? []) ? $validated['jurusan_id'] : null,
        ]);

        // Guard against removing Super Admin from themselves or others unless current user is Super Admin
        if ($user->hasRole(AdminPermissions::ROLE_SUPER_ADMIN) && !in_array(AdminPermissions::ROLE_SUPER_ADMIN, $validated['roles'] ?? [], true)) {
            if (!auth('admin')->user()->hasRole(AdminPermissions::ROLE_SUPER_ADMIN)) {
                return redirect()->route('admin.users.index')->with('toastError', 'Anda tidak dapat menghapus status Super Admin.');
            }
        }

        $user->syncRoles($validated['roles'] ?? []);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(Admin $user)
    {
        // 1. Tidak bisa hapus akun sendiri
        if ($user->id === auth('admin')->id()) {
            return redirect()->route('admin.users.index')->with('toastError', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // 2. Admin biasa tidak bisa hapus Super Admin
        if ($user->hasRole(AdminPermissions::ROLE_SUPER_ADMIN) && !auth('admin')->user()->hasRole(AdminPermissions::ROLE_SUPER_ADMIN)) {
            return redirect()->route('admin.users.index')->with('toastError', 'Hanya Super Admin yang diizinkan menghapus akun Super Admin.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }

    public function toggleActive(Admin $user)
    {
        // 1. Tidak bisa menonaktifkan akun sendiri
        if ($user->id === auth('admin')->id()) {
            return redirect()->route('admin.users.index')->with('toastError', 'Anda tidak dapat mengubah status aktif akun Anda sendiri.');
        }

        // 2. Akun Super Admin tidak boleh dinonaktifkan
        if ($user->hasRole(AdminPermissions::ROLE_SUPER_ADMIN)) {
            return redirect()->route('admin.users.index')->with('toastError', 'Akun Super Admin tidak dapat dinonaktifkan.');
        }

        // 3. Admin biasa tidak boleh menonaktifkan Super Admin
        if ($user->hasRole(AdminPermissions::ROLE_SUPER_ADMIN) && !auth('admin')->user()->hasRole(AdminPermissions::ROLE_SUPER_ADMIN)) {
            return redirect()->route('admin.users.index')->with('toastError', 'Hanya Super Admin yang diizinkan menonaktifkan akun Super Admin.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusStr = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('admin.users.index')->with('success', "Akun {$user->name} berhasil {$statusStr}.");
    }

    public function resetPassword(Request $request, Admin $user)
    {
        // 1. Admin biasa tidak bisa mereset password Super Admin
        if ($user->hasRole(AdminPermissions::ROLE_SUPER_ADMIN) && !auth('admin')->user()->hasRole(AdminPermissions::ROLE_SUPER_ADMIN)) {
            return redirect()->route('admin.users.index')->with('toastError', 'Hanya Super Admin yang diizinkan mereset password akun Super Admin.');
        }

        $validated = $request->validate([
            'password' => 'required|min:6|confirmed',
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('admin.users.index')->with('success', "Password untuk user {$user->name} berhasil direset.");
    }

    private function hasInterviewerRole(array $roles): bool
    {
        return collect($roles)->contains(fn (string $role): bool => in_array($role, [
            AdminPermissions::ROLE_DOSEN,
            AdminPermissions::LEGACY_ROLE_DOSEN_PEWAWANCARA,
        ], true));
    }
}
