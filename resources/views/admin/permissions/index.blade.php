@extends('admin_dashboard.layout.master')

@section('title', 'Daftar Permission')

@section('content')
@php
    $breadcrumbs = [
        ['label' => 'Pengaturan'],
        ['label' => 'Daftar Permission']
    ];

    // Map permissions to their modules
    $modules = \App\Support\AdminPermissions::modules();
    $permissionModuleMap = [];
    foreach ($modules as $modName => $perms) {
        foreach ($perms as $pName) {
            $permissionModuleMap[$pName] = $modName;
        }
    }

    // Friendly names and descriptions for premium display
    $permissionDetails = [
        'dashboard.view' => ['friendly' => 'Lihat Dashboard', 'desc' => 'Melihat ringkasan statistik dan grafik pendaftaran PMB.'],
        'pmb.view' => ['friendly' => 'Lihat Data PMB', 'desc' => 'Melihat daftar calon mahasiswa baru yang mendaftar.'],
        'pmb.create' => ['friendly' => 'Tambah Pendaftar', 'desc' => 'Menginput data pendaftaran calon mahasiswa baru.'],
        'pmb.edit' => ['friendly' => 'Ubah Data Pendaftar', 'desc' => 'Mengubah data biodata atau status calon mahasiswa.'],
        'pmb.delete' => ['friendly' => 'Hapus Pendaftar', 'desc' => 'Menghapus pendaftar PMB secara aman.'],
        'pmb.restore' => ['friendly' => 'Restore Pendaftar', 'desc' => 'Memulihkan pendaftar PMB yang terhapus.'],
        'pmb.force-delete' => ['friendly' => 'Hapus Permanen PMB', 'desc' => 'Menghapus data pendaftar PMB selamanya dari database.'],
        'pmb.update-status' => ['friendly' => 'Update Status Lulus', 'desc' => 'Memperbarui status pendaftaran/kelulusan calon mahasiswa.'],
        'pmb.verify-document' => ['friendly' => 'Verifikasi Berkas', 'desc' => 'Memeriksa dan menyetujui berkas pendaftaran calon mahasiswa.'],
        'pmb.export' => ['friendly' => 'Ekspor Data PMB', 'desc' => 'Mengekspor laporan pendaftar PMB ke Excel/PDF.'],
        'pmb.send-email' => ['friendly' => 'Kirim Email PMB', 'desc' => 'Mengirim email notifikasi kelulusan atau instruksi.'],
        'payment.view' => ['friendly' => 'Lihat Keuangan', 'desc' => 'Melihat daftar pembayaran registrasi dan uang kuliah.'],
        'payment.verify' => ['friendly' => 'Verifikasi Pembayaran', 'desc' => 'Menyetujui bukti pembayaran pendaftaran pendaftar.'],
        'payment.reject' => ['friendly' => 'Tolak Pembayaran', 'desc' => 'Menolak bukti pembayaran yang tidak sesuai.'],
        'payment.export' => ['friendly' => 'Ekspor Transaksi', 'desc' => 'Mengekspor data transaksi keuangan PMB.'],
        'master.view' => ['friendly' => 'Lihat Data Master', 'desc' => 'Melihat data master penunjang (agama, pekerjaan, kuesioner).'],
        'master.create' => ['friendly' => 'Tambah Data Master', 'desc' => 'Menambahkan data master penunjang baru.'],
        'master.edit' => ['friendly' => 'Ubah Data Master', 'desc' => 'Mengubah data master penunjang.'],
        'master.delete' => ['friendly' => 'Hapus Data Master', 'desc' => 'Menghapus data master penunjang.'],
        'master.restore' => ['friendly' => 'Restore Data Master', 'desc' => 'Memulihkan data master penunjang yang terhapus.'],
        'periode.view' => ['friendly' => 'Lihat Periode', 'desc' => 'Melihat daftar periode/tahun ajaran aktif.'],
        'periode.create' => ['friendly' => 'Tambah Periode', 'desc' => 'Menambahkan periode tahun akademik baru.'],
        'periode.edit' => ['friendly' => 'Ubah Periode', 'desc' => 'Mengubah nama atau status tahun akademik.'],
        'periode.delete' => ['friendly' => 'Hapus Periode', 'desc' => 'Menghapus periode tahun akademik.'],
        'periode.set-active' => ['friendly' => 'Aktifkan Periode', 'desc' => 'Mengatur periode aktif sistem saat ini.'],
        'gelombang.view' => ['friendly' => 'Lihat Gelombang', 'desc' => 'Melihat gelombang pendaftaran PMB yang dibuka.'],
        'gelombang.create' => ['friendly' => 'Tambah Gelombang', 'desc' => 'Menambahkan gelombang pendaftaran baru.'],
        'gelombang.edit' => ['friendly' => 'Ubah Gelombang', 'desc' => 'Mengubah tanggal atau nama gelombang PMB.'],
        'gelombang.delete' => ['friendly' => 'Hapus Gelombang', 'desc' => 'Menghapus gelombang pendaftaran.'],
        'gelombang.set-active' => ['friendly' => 'Aktifkan Gelombang', 'desc' => 'Mengatur gelombang aktif berjalan.'],
        'jurusan.view' => ['friendly' => 'Lihat Jurusan', 'desc' => 'Melihat daftar program studi/jurusan kampus.'],
        'jurusan.create' => ['friendly' => 'Tambah Jurusan', 'desc' => 'Menambahkan prodi/jurusan baru.'],
        'jurusan.edit' => ['friendly' => 'Ubah Jurusan', 'desc' => 'Mengubah informasi prodi/jurusan.'],
        'jurusan.delete' => ['friendly' => 'Hapus Jurusan', 'desc' => 'Menghapus prodi/jurusan.'],
        'user.view' => ['friendly' => 'Lihat User', 'desc' => 'Melihat daftar admin dan pengguna sistem.'],
        'user.create' => ['friendly' => 'Tambah User', 'desc' => 'Membuat akun pengguna administrator/staf baru.'],
        'user.edit' => ['friendly' => 'Ubah User', 'desc' => 'Mengubah detail kredensial dan hak pengguna.'],
        'user.delete' => ['friendly' => 'Hapus User', 'desc' => 'Menghapus akun pengguna dari database.'],
        'user.assign-role' => ['friendly' => 'Assign Role', 'desc' => 'Membagikan peran/role akses ke akun pengguna.'],
        'role.view' => ['friendly' => 'Lihat Role', 'desc' => 'Melihat daftar peran/role otorisasi sistem.'],
        'role.create' => ['friendly' => 'Tambah Role', 'desc' => 'Membuat role peran otorisasi baru.'],
        'role.edit' => ['friendly' => 'Ubah Role', 'desc' => 'Memodifikasi pembagian hak akses pada role.'],
        'role.delete' => ['friendly' => 'Hapus Role', 'desc' => 'Menghapus role peran otorisasi.'],
        'role.assign-permission' => ['friendly' => 'Bagi Hak Akses', 'desc' => 'Menetapkan permission pada role tertentu.'],
        'activity-log.view' => ['friendly' => 'Lihat Log Aktivitas', 'desc' => 'Memantau log aktivitas audit kegiatan admin.'],
        'activity-log.export' => ['friendly' => 'Ekspor Log Aktivitas', 'desc' => 'Mengekspor laporan audit log.'],
        'activity-log.delete' => ['friendly' => 'Hapus Log Aktivitas', 'desc' => 'Mengosongkan riwayat audit log aktivitas.'],
        'report.view' => ['friendly' => 'Lihat Laporan', 'desc' => 'Melihat kompilasi laporan statistik PMB.'],
        'report.export' => ['friendly' => 'Ekspor Laporan', 'desc' => 'Mengekspor laporan statistik kampus.'],
        'setting.view' => ['friendly' => 'Lihat Pengaturan', 'desc' => 'Melihat pengaturan umum sistem PMB.'],
        'setting.edit' => ['friendly' => 'Ubah Pengaturan', 'desc' => 'Memperbarui konfigurasi sistem PMB.'],
        'wawancara.review' => ['friendly' => 'Review Wawancara', 'desc' => 'Melakukan penilaian wawancara calon mahasiswa baru.'],
        'kesehatan.review' => ['friendly' => 'Review Medis', 'desc' => 'Melakukan pemeriksaan medis/kesehatan calon mahasiswa.'],
        'landing-page.manage' => ['friendly' => 'Kelola Landing Page', 'desc' => 'Mengatur isi konten halaman depan seperti video/biaya.'],
    ];
@endphp

{{-- ✅ Page Hero --}}
@include('admin_dashboard.components.page-hero', [
    'title' => 'Daftar Permission',
    'subtitle' => 'Kelola hak akses detail untuk setiap fitur dan fungsionalitas sistem.',
    'icon' => 'key',
    'breadcrumbs' => $breadcrumbs,
    'actions' => '<a href="' . route('admin.permissions.create') . '" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Tambah Permission</a>'
])

<div class="container-fluid px-0">

    {{-- ✅ Search & Filter Card --}}
    @component('admin_dashboard.components.admin-card', [
        'title' => 'Pencarian Hak Akses',
        'icon' => 'bx bx-filter-alt text-primary'
    ])
        <form method="GET" action="{{ route('admin.permissions.index') }}" class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Cari Kata Kunci</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama permission atau role terkait..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter me-1"></i> Cari</button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-label-secondary"><i class="bx bx-refresh"></i></a>
                @endif
            </div>
        </form>
    @endcomponent

    {{-- ✅ Main Table Card --}}
    @component('admin_dashboard.components.admin-card', [
        'title' => 'Hak Akses Tersedia',
        'icon' => 'bx bx-shield-quarter text-success'
    ])
        @if ($permissions->count() > 0)
            <div class="table-responsive admin-table-wrapper">
                <table class="table admin-table table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">#</th>
                            <th width="20%">Hak Akses (Sistem)</th>
                            <th width="20%">Modul Fitur</th>
                            <th width="35%">Deskripsi Hak Akses</th>
                            <th width="10%" class="text-center">Digunakan Oleh</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($permissions as $perm)
                            @php
                                $modName = $permissionModuleMap[$perm->name] ?? 'Sistem / Custom';
                                $details = $permissionDetails[$perm->name] ?? ['friendly' => ucfirst(str_replace('.', ': ', $perm->name)), 'desc' => 'Hak akses khusus/legacy untuk integrasi sistem.'];
                                $isCanonical = in_array($perm->name, \App\Support\AdminPermissions::all(), true);
                            @endphp
                            <tr>
                                <td class="text-center text-muted fw-semibold">
                                    {{ ($permissions->currentPage() - 1) * $permissions->perPage() + $loop->iteration }}
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <h6 class="mb-0 fw-semibold text-heading">{{ $details['friendly'] }}</h6>
                                        <small class="text-muted fs-8 font-monospace">{{ $perm->name }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $modName === 'Sistem / Custom' ? 'bg-label-secondary' : 'bg-label-primary' }} fw-semibold">
                                        {{ $modName }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted fs-7">{{ $details['desc'] }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-wrap justify-content-center gap-1">
                                        @forelse ($perm->roles as $role)
                                            <span class="badge bg-label-secondary fs-8 rounded" data-bs-toggle="tooltip" title="{{ $role->name }}">
                                                {{ substr($role->name, 0, 8) }}{{ strlen($role->name) > 8 ? '..' : '' }}
                                            </span>
                                        @empty
                                            <span class="text-muted fs-8">Belum dipakai</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        {{-- Edit Button --}}
                                        <a href="{{ route('admin.permissions.edit', $perm->id) }}" class="btn btn-sm btn-icon btn-label-warning"
                                           data-bs-toggle="tooltip" title="Edit Permission">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>

                                        {{-- Delete Button --}}
                                        @if(!$isCanonical)
                                            <form action="{{ route('admin.permissions.destroy', $perm->id) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus permission {{ addslashes($perm->name) }} secara permanen?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-icon btn-label-danger"
                                                        data-bs-toggle="tooltip" title="Hapus Permission">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-sm btn-icon btn-label-secondary" disabled data-bs-toggle="tooltip" title="Hak akses bawaan tidak boleh dihapus">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- ✅ Pagination --}}
            <div class="d-flex justify-content-between align-items-center flex-wrap mt-4 gap-3">
                <span class="text-muted fs-7">
                    Menampilkan {{ $permissions->firstItem() ?? 0 }} - {{ $permissions->lastItem() ?? 0 }} dari {{ $permissions->total() }} data hak akses.
                </span>
                <div>
                    {{ $permissions->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @else
            @include('admin_dashboard.components.empty-state', [
                'title' => 'Data Permission Kosong',
                'description' => 'Tidak ada data permission/hak akses sistem yang terdaftar atau cocok dengan kriteria pencarian.',
                'icon' => 'key'
            ])
        @endif
    @endcomponent

</div>
@endsection