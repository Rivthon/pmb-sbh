@extends('admin_dashboard.layout.master')

@section('title', 'Daftar Role')

@section('content')
@php
    $breadcrumbs = [
        ['label' => 'Pengaturan'],
        ['label' => 'Daftar Role']
    ];

    // Dynamic Description Mapping for Premium UX
    $roleDescriptions = [
        \App\Support\AdminPermissions::ROLE_SUPER_ADMIN => 'Akses penuh tanpa batasan ke seluruh modul dan fitur sistem.',
        \App\Support\AdminPermissions::ROLE_ADMIN_PMB => 'Mengelola proses pendaftaran, gelombang, prodi, kuesioner, dan administrasi PMB.',
        \App\Support\AdminPermissions::ROLE_STAFF_PMB => 'Melakukan verifikasi dokumen pendaftaran dan administrasi dasar PMB.',
        \App\Support\AdminPermissions::ROLE_PETUGAS_PEMBERKASAN => 'Menerima berkas fisik dan melakukan scan barcode kedatangan peserta.',
        \App\Support\AdminPermissions::ROLE_PJ_SELEKSI_ONLINE => 'Mengelola sesi seleksi online, notifikasi surat kesehatan, dan penugasan wawancara tanpa akses scan kedatangan.',
        \App\Support\AdminPermissions::ROLE_KEUANGAN => 'Memverifikasi bukti pembayaran pendaftaran dan biaya kuliah.',
        \App\Support\AdminPermissions::ROLE_BAAK => 'Mengelola administrasi akademik dan laporan kelulusan mahasiswa baru.',
        \App\Support\AdminPermissions::ROLE_UPMI => 'Melakukan pengawasan mutu sistem dan log aktivitas kegiatan.',
        \App\Support\AdminPermissions::ROLE_BAUK => 'Mengelola logistik dan sarana prasarana penunjang umum.',
        \App\Support\AdminPermissions::ROLE_KEMAHASISWAAN => 'Mengurus administrasi kemahasiswaan dan bimbingan konseling.',
        \App\Support\AdminPermissions::ROLE_DOSEN => 'Melakukan penilaian dan wawancara calon mahasiswa baru.',
        \App\Support\AdminPermissions::ROLE_MAHASISWA => 'Akses calon mahasiswa untuk mengunggah berkas dan melihat kelulusan.',
    ];
@endphp

{{-- ✅ Page Hero --}}
@include('admin_dashboard.components.page-hero', [
    'title' => 'Daftar Role',
    'subtitle' => 'Kelola pengelompokan hak akses pengguna berdasarkan tugas dan tanggung jawab.',
    'icon' => 'shield-quarter',
    'breadcrumbs' => $breadcrumbs,
    'actions' => '<a href="' . route('admin.roles.create') . '" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Tambah Role</a>'
])

<div class="container-fluid px-0">

    {{-- ✅ Roles List Card --}}
    @component('admin_dashboard.components.admin-card', [
        'title' => 'Manajemen Peran & Otorisasi',
        'icon' => 'bx bx-lock-open text-primary'
    ])
        @if ($roles->count() > 0)
            <div class="table-responsive admin-table-wrapper">
                <table class="table admin-table table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">#</th>
                            <th width="25%">Nama Role</th>
                            <th width="40%">Deskripsi Peran</th>
                            <th width="10%" class="text-center">Jumlah User</th>
                            <th width="10%" class="text-center">Hak Akses</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            @php
                                $isSuperAdmin = $role->name === \App\Support\AdminPermissions::ROLE_SUPER_ADMIN;
                                $isCanonical = in_array($role->name, $canonicalRoles, true);
                                $desc = $roleDescriptions[$role->name] ?? 'Peran kustom/legacy untuk penyesuaian hak akses operasional.';
                            @endphp
                            <tr>
                                <td class="text-center text-muted fw-semibold">
                                    {{ ($roles->currentPage() - 1) * $roles->perPage() + $loop->iteration }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <h6 class="mb-0 fw-semibold text-heading">{{ $role->name }}</h6>
                                        @if($isSuperAdmin)
                                            <span class="badge bg-label-danger fs-9 py-0"><i class="bx bxs-star me-1 fs-9"></i>System</span>
                                        @elseif($isCanonical)
                                            <span class="badge bg-label-primary fs-9 py-0">Bawaan</span>
                                        @else
                                            <span class="badge bg-label-warning fs-9 py-0">Custom</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted fs-7">{{ $desc }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-label-secondary fw-bold rounded-pill px-2">
                                        {{ $role->users_count ?? 0 }} Pengguna
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-label-success fw-bold rounded-pill px-2">
                                        {{ $role->permissions->count() }} Akses
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        {{-- Edit Button --}}
                                        @if(!$isSuperAdmin)
                                            <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-icon btn-label-warning"
                                               data-bs-toggle="tooltip" title="Edit Role & Permission">
                                                <i class="bx bx-edit-alt"></i>
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-sm btn-icon btn-label-secondary" disabled data-bs-toggle="tooltip" title="Role sistem tidak dapat diubah">
                                                <i class="bx bx-edit-alt"></i>
                                            </button>
                                        @endif

                                        {{-- Delete Button --}}
                                        @if(!$isSuperAdmin)
                                            <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus role {{ addslashes($role->name) }} secara permanen?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-icon btn-label-danger"
                                                        data-bs-toggle="tooltip" title="Hapus Role">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-sm btn-icon btn-label-secondary" disabled data-bs-toggle="tooltip" title="Role sistem tidak dapat dihapus">
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
                    Menampilkan {{ $roles->firstItem() ?? 0 }} - {{ $roles->lastItem() ?? 0 }} dari {{ $roles->total() }} data peran.
                </span>
                <div>
                    {{ $roles->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @else
            @include('admin_dashboard.components.empty-state', [
                'title' => 'Data Role Kosong',
                'description' => 'Tidak ada data peran/role sistem yang terdaftar atau cocok dengan kriteria pencarian.',
                'icon' => 'shield'
            ])
        @endif
    @endcomponent

</div>
@endsection
