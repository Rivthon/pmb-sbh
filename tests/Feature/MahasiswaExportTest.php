<?php

namespace Tests\Feature;

use App\Enums\PmbStatus;
use App\Exports\MahasiswaExport;
use App\Models\Admin;
use App\Models\User;
use App\Support\AdminPermissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MahasiswaExportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_export_pdf_with_parent_names(): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $permission = Permission::firstOrCreate([
            'name' => AdminPermissions::PMB_EXPORT,
            'guard_name' => AdminPermissions::GUARD,
        ]);

        $admin = Admin::create([
            'name' => 'Admin Export',
            'email' => 'admin-export-' . uniqid() . '@example.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $admin->givePermissionTo($permission);

        $student = User::create([
            'name' => 'Mahasiswa Export',
            'email' => 'student-export-' . uniqid() . '@example.test',
            'password' => Hash::make('password'),
            'role' => User::USER_ROLE,
            'code' => 'EXP-' . strtoupper(substr(uniqid(), -8)),
            'status_pemb' => PmbStatus::Verified->value,
            'nama_ayah' => 'Bapak Export',
            'nama_ibu' => 'Ibu Export',
        ]);

        $this->actingAs($admin, 'admin')
            // Tanpa filter untuk memastikan export data dalam jumlah besar
            // tidak lagi menghabiskan memori DomPDF.
            ->get(route('admin.mahasiswa.cetak'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $html = view('admin_dashboard.pages.mahasiswa.pdf', [
            'data' => collect([$student]),
            'meta' => [],
        ])->render();

        $this->assertStringContainsString('Bapak Export', $html);
        $this->assertStringContainsString('Ibu Export', $html);

        $mapped = (new MahasiswaExport(User::query()))->map($student);
        $this->assertContains('Bapak Export', $mapped);
        $this->assertContains('Ibu Export', $mapped);
    }
}
