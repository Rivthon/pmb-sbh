@extends('admin_dashboard.layout.master')

@section('title', 'Daftar User')

@section('content')
@php
    $breadcrumbs = [
        ['label' => 'Pengaturan'],
        ['label' => 'Daftar User']
    ];
@endphp

{{-- ✅ Page Hero --}}
@include('admin_dashboard.components.page-hero', [
    'title' => 'Daftar User',
    'subtitle' => 'Kelola akun administrator, staff, dan berbagai hak akses pengguna sistem PMB.',
    'icon' => 'user-check',
    'breadcrumbs' => $breadcrumbs,
    'actions' => '<a href="' . route('admin.users.create') . '" class="btn btn-primary"><i class="bx bx-user-plus me-1"></i> Tambah User</a>'
])

<div class="container-fluid px-0">

    {{-- ✅ Filter Card --}}
    @component('admin_dashboard.components.admin-card', [
        'title' => 'Pencarian & Filter',
        'icon' => 'bx bx-filter-alt text-primary'
    ])
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Cari Pengguna</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama atau email..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Role Akses</label>
                <select name="role" class="form-select">
                    <option value="">Semua Role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status Akun</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter me-1"></i> Filter</button>
                @if(request()->anyFilled(['search', 'role', 'status']))
                    <a href="{{ route('admin.users.index') }}" class="btn btn-label-secondary"><i class="bx bx-refresh"></i></a>
                @endif
            </div>
        </form>
    @endcomponent

    {{-- ✅ Main Table Card --}}
    @component('admin_dashboard.components.admin-card', [
        'title' => 'Data Pengguna Sistem',
        'icon' => 'bx bx-shield-quarter text-success'
    ])
        @if ($users->count() > 0)
            <div class="table-responsive admin-table-wrapper">
                <table class="table admin-table table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">#</th>
                            <th>Pengguna</th>
                            <th>Role Akses</th>
                            <th class="text-center">Status</th>
                            <th>Tanggal Terdaftar</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            @php
                                $isSelf = $user->id === auth('admin')->id();
                                $isSuperAdmin = $user->hasRole(\App\Support\AdminPermissions::ROLE_SUPER_ADMIN);
                                $currentUserIsSuperAdmin = auth('admin')->user()->hasRole(\App\Support\AdminPermissions::ROLE_SUPER_ADMIN);
                                $canModify = !$isSuperAdmin || $currentUserIsSuperAdmin;
                            @endphp
                            <tr>
                                <td class="text-center text-muted fw-semibold">
                                    {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar avatar-md rounded-circle bg-label-primary d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 40px; height: 40px;">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold text-heading">{{ $user->name }}</h6>
                                            <span class="text-muted fs-7">{{ $user->email }}</span>
                                            @if($isSelf)
                                                <span class="badge bg-label-success ms-1 fs-8 py-0">Anda</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @forelse ($user->roles as $role)
                                            <span class="badge {{ $role->name === \App\Support\AdminPermissions::ROLE_SUPER_ADMIN ? 'bg-label-danger' : 'bg-label-primary' }} text-uppercase fs-8 fw-semibold">
                                                {{ $role->name }}
                                            </span>
                                        @empty
                                            <span class="badge bg-label-secondary fs-8">Tanpa Role</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="text-center">
                                    @include('admin_dashboard.components.status-badge', [
                                        'status' => $user->is_active ? 'aktif' : 'nonaktif'
                                    ])
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-heading fw-medium">{{ $user->created_at->translatedFormat('d M Y') }}</span>
                                        <small class="text-muted fs-7">{{ $user->created_at->format('H:i') }} WIB</small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        {{-- Reset Password Button --}}
                                        @if($canModify)
                                            <button type="button" class="btn btn-sm btn-icon btn-label-info"
                                                    onclick="openResetPasswordModal('{{ route('admin.users.reset-password', $user->id) }}', '{{ addslashes($user->name) }}')"
                                                    data-bs-toggle="tooltip" title="Reset Password">
                                                <i class="bx bx-key"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-icon btn-label-secondary" disabled>
                                                <i class="bx bx-key"></i>
                                            </button>
                                        @endif

                                        {{-- Toggle Status Button --}}
                                        @if($canModify && !$isSelf && !$isSuperAdmin)
                                            <form action="{{ route('admin.users.toggle-active', $user->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-sm btn-icon {{ $user->is_active ? 'btn-label-warning' : 'btn-label-success' }}"
                                                        data-bs-toggle="tooltip" title="{{ $user->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}">
                                                    <i class="bx bx-{{ $user->is_active ? 'block' : 'user-check' }}"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-sm btn-icon btn-label-secondary" disabled>
                                                <i class="bx bx-block"></i>
                                            </button>
                                        @endif

                                        {{-- Edit Button --}}
                                        @if($canModify)
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-icon btn-label-warning"
                                               data-bs-toggle="tooltip" title="Edit User">
                                                <i class="bx bx-edit-alt"></i>
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-sm btn-icon btn-label-secondary" disabled>
                                                <i class="bx bx-edit-alt"></i>
                                            </button>
                                        @endif

                                        {{-- Delete Button --}}
                                        @if($canModify && !$isSelf)
                                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna {{ addslashes($user->name) }} secara permanen?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-icon btn-label-danger"
                                                        data-bs-toggle="tooltip" title="Hapus User">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-sm btn-icon btn-label-secondary" disabled>
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
                    Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} data pengguna.
                </span>
                <div>
                    {{ $users->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @else
            @include('admin_dashboard.components.empty-state', [
                'title' => 'Data Pengguna Kosong',
                'description' => 'Tidak ada data pengguna sistem yang terdaftar atau cocok dengan kriteria pencarian.',
                'icon' => 'user-voice'
            ])
        @endif
    @endcomponent

</div>

{{-- ✅ Reset Password Modal --}}
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="resetPasswordForm" method="POST" action="" class="w-100">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-semibold text-heading">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="text-muted mb-4 fs-7">Ubah kata sandi untuk pengguna <b id="resetPasswordUser" class="text-dark"></b>. Pastikan password kuat dan mudah diingat.</p>
                    
                    <div class="admin-form-group mb-3">
                        <label class="form-label required">Password Baru</label>
                        <div class="input-group input-group-merge admin-password-group">
                            <input type="password" id="modal_password" name="password" class="form-control" required minlength="6" placeholder="Masukkan password baru">
                            <button class="input-group-text admin-password-toggle" type="button" data-password-toggle="modal_password" aria-label="Tampilkan password baru">
                                <i class="bx bx-hide" id="modal_password_icon"></i>
                            </button>
                        </div>
                        <small class="text-muted">Minimal 6 karakter.</small>
                    </div>

                    <div class="admin-form-group mb-0">
                        <label class="form-label required">Konfirmasi Password Baru</label>
                        <div class="input-group input-group-merge admin-password-group">
                            <input type="password" id="modal_password_confirmation" name="password_confirmation" class="form-control" required minlength="6" placeholder="Ulangi password baru">
                            <button class="input-group-text admin-password-toggle" type="button" data-password-toggle="modal_password_confirmation" aria-label="Tampilkan konfirmasi password baru">
                                <i class="bx bx-hide" id="modal_password_confirmation_icon"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light-50">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-check me-1"></i> Simpan Password</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('script')
<script>
    window.openResetPasswordModal = function (url, name) {
        const form = document.getElementById('resetPasswordForm');
        const userLabel = document.getElementById('resetPasswordUser');
        const modalEl = document.getElementById('resetPasswordModal');

        if (!form || !userLabel || !modalEl) return;

        form.action = url;
        userLabel.innerText = name;

        ['modal_password', 'modal_password_confirmation'].forEach(id => {
            const input = document.getElementById(id);
            const icon = document.getElementById(id + '_icon');
            if (input) input.value = '';
            if (input) input.type = 'password';
            if (icon) icon.className = 'bx bx-hide';
        });

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    };

    document.addEventListener('DOMContentLoaded', function () {
        function togglePasswordVisibility(id) {
            const input = document.getElementById(id);
            const icon = document.getElementById(id + '_icon');

            if (!input || !icon) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bx bx-show';
            } else {
                input.type = 'password';
                icon.className = 'bx bx-hide';
            }
        }

        document.querySelectorAll('[data-password-toggle]').forEach(button => {
            button.addEventListener('click', () => togglePasswordVisibility(button.dataset.passwordToggle));
        });
    });
</script>
@endpush
