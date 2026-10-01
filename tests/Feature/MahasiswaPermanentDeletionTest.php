<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Support\AdminPermissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MahasiswaPermanentDeletionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_deleting_student_permanently_releases_email_for_registration(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permission = Permission::firstOrCreate([
            'name' => AdminPermissions::PMB_DELETE,
            'guard_name' => AdminPermissions::GUARD,
        ]);

        $admin = Admin::create([
            'name' => 'Admin Hapus Mahasiswa',
            'email' => 'admin-delete-' . uniqid() . '@example.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $admin->givePermissionTo($permission);

        $email = 'daftar-ulang-' . uniqid() . '@example.test';
        $student = User::create([
            'name' => 'Calon Mahasiswa Dihapus',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => User::USER_ROLE,
            'code' => 'DEL-' . strtoupper(substr(uniqid(), -8)),
        ]);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.mahasiswa-baru.destroy', $student->id))
            ->assertRedirect(route('admin.mahasiswa-baru.index'));

        $this->assertDatabaseMissing('users', ['id' => $student->id]);
        $this->assertTrue(Validator::make(
            ['email' => $email],
            ['email' => ['required', 'email', 'unique:users,email']]
        )->passes());
    }
}
