@extends('admin_dashboard.layout.master')

@section('title', 'Edit User')

@section('content')
@php
    $breadcrumbs = [
        ['label' => 'Pengaturan', 'url' => route('admin.users.index')],
        ['label' => 'Edit User']
    ];
@endphp

{{-- ✅ Page Hero --}}
@include('admin_dashboard.components.page-hero', [
    'title' => 'Edit User',
    'subtitle' => 'Ubah detail profil, kredensial, dan perbarui peran akses akun pengguna.',
    'icon' => 'user-voice',
    'breadcrumbs' => $breadcrumbs
])

<div class="container-fluid px-0">
    <form id="userForm" action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- ✅ Left Column - Account Info --}}
            <div class="col-lg-8">
                @component('admin_dashboard.components.admin-card', [
                    'title' => 'Informasi Akun',
                    'icon' => 'bx bx-user text-primary',
                    'subtitle' => 'Perbarui data nama lengkap, alamat email, atau kata sandi pengguna.'
                ])
                    <div class="row g-3">
                        <div class="col-md-12">
                            @include('admin_dashboard.components.form-input', [
                                'name' => 'name',
                                'label' => 'Nama Lengkap',
                                'placeholder' => 'Masukkan nama lengkap',
                                'required' => true,
                                'value' => old('name', $user->name)
                            ])
                        </div>

                        <div class="col-md-12 d-none" id="interviewerProgramField">
                            <div class="admin-form-group">
                                <label for="jurusan_id" class="form-label required">Program Studi Dosen</label>
                                <select id="jurusan_id" name="jurusan_id" class="form-select @error('jurusan_id') is-invalid @enderror">
                                    <option value="">Pilih program studi</option>
                                    @foreach($jurusans as $jurusan)
                                        <option value="{{ $jurusan->id }}" @selected((string) old('jurusan_id', $user->jurusan_id) === (string) $jurusan->id)>{{ $jurusan->nama_jurusan }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">S1 Farmasi juga menangani peserta S1 Farmasi Karyawan.</div>
                                @error('jurusan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="col-md-12">
                            @include('admin_dashboard.components.form-input', [
                                'name' => 'email',
                                'type' => 'email',
                                'label' => 'Alamat Email',
                                'placeholder' => 'nama@contoh.com',
                                'required' => true,
                                'value' => old('email', $user->email)
                            ])
                        </div>

                        <div class="col-md-12">
                            <div class="alert alert-warning d-flex align-items-center gap-2 mb-0 py-2 fs-7">
                                <i class="bx bx-info-circle"></i>
                                <span>Biarkan bidang password di bawah ini kosong jika Anda tidak berniat mengganti password.</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="form-label">Password Baru <span class="text-muted fw-normal fs-8">(Opsional)</span></label>
                                <div class="input-group input-group-merge admin-password-group">
                                    <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="6" placeholder="Masukkan password baru">
                                    <button class="input-group-text admin-password-toggle" type="button" data-password-toggle="password" aria-label="Tampilkan password baru">
                                        <i class="bx bx-hide" id="password_icon"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">
                                        <i class="bx bx-error-circle"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label class="form-label">Konfirmasi Password Baru <span class="text-muted fw-normal fs-8">(Opsional)</span></label>
                                <div class="input-group input-group-merge admin-password-group">
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" minlength="6" placeholder="Ulangi password baru">
                                    <button class="input-group-text admin-password-toggle" type="button" data-password-toggle="password_confirmation" aria-label="Tampilkan konfirmasi password baru">
                                        <i class="bx bx-hide" id="password_confirmation_icon"></i>
                                    </button>
                                </div>
                                @error('password_confirmation')
                                    <div class="invalid-feedback d-block">
                                        <i class="bx bx-error-circle"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endcomponent

                @component('admin_dashboard.components.admin-card', [
                    'title' => 'Catatan Tambahan (Opsional)',
                    'icon' => 'bx bx-note text-secondary',
                    'subtitle' => 'Keterangan opsional mengenai peran atau identitas akun.'
                ])
                    @include('admin_dashboard.components.form-textarea', [
                        'name' => 'keterangan',
                        'label' => 'Keterangan Akun',
                        'placeholder' => 'Misal: Admin Keuangan cabang pembantu atau Staf magang PMB...',
                        'required' => false,
                        'value' => old('keterangan', $user->keterangan ?? ''),
                        'rows' => 3
                    ])
                @endcomponent
            </div>

            {{-- ✅ Right Column - Roles & Actions --}}
            <div class="col-lg-4">
                @component('admin_dashboard.components.admin-card', [
                    'title' => 'Role & Hak Akses',
                    'icon' => 'bx bx-shield-quarter text-warning',
                    'subtitle' => 'Pilih satu atau beberapa peran akses pengguna.'
                ])
                    <div class="admin-form-group mb-0">
                        <label class="form-label required d-block mb-3">Pilih Peran (Role)</label>
                        
                        <div class="admin-role-list">
                            @foreach ($roles as $role)
                                @php
                                    $checked = is_array(old('roles')) 
                                        ? in_array($role->name, old('roles')) 
                                        : in_array($role->name, $userRoles);
                                @endphp
                                <div class="form-check admin-role-option cursor-pointer">
                                    <label class="form-check-label cursor-pointer w-100" for="role_{{ $role->id }}">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->name }}" id="role_{{ $role->id }}"
                                               {{ $checked ? 'checked' : '' }}>
                                        <span>{{ $role->name }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        
                        @error('roles')
                            <div class="invalid-feedback d-block mt-2">
                                <i class="bx bx-error-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="text-muted d-block mt-3">Perubahan peran akan langsung aktif setelah form ini disimpan dan pengguna memuat ulang halaman.</small>
                    </div>
                @endcomponent

                {{-- Action Card --}}
                @component('admin_dashboard.components.admin-card', [
                    'title' => 'Aksi Formulir',
                    'icon' => 'bx bx-check-double text-success'
                ])
                    <div class="d-flex flex-column gap-2 mb-0">
                        <button type="submit" id="btnSubmit" class="btn btn-primary w-100">
                            <i class="bx bx-save me-1"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-label-secondary w-100">
                            <i class="bx bx-x me-1"></i> Batal
                        </a>
                    </div>
                @endcomponent
            </div>
        </div>
    </form>
</div>

@endsection

@push('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function toggleUserPassword(id) {
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
            button.addEventListener('click', () => toggleUserPassword(button.dataset.passwordToggle));
        });

        const form = document.getElementById('userForm');
        const btnSubmit = document.getElementById('btnSubmit');

        if (!form || !btnSubmit) return;

        const interviewerRoles = @json([\App\Support\AdminPermissions::ROLE_DOSEN, \App\Support\AdminPermissions::LEGACY_ROLE_DOSEN_PEWAWANCARA]);
        const programField = document.getElementById('interviewerProgramField');
        const programInput = document.getElementById('jurusan_id');
        const syncProgramField = () => {
            const selectedRoles = Array.from(form.querySelectorAll('input[name="roles[]"]:checked')).map(input => input.value);
            const isInterviewer = selectedRoles.some(role => interviewerRoles.includes(role));
            programField?.classList.toggle('d-none', !isInterviewer);
            if (programInput) programInput.required = isInterviewer;
        };
        form.querySelectorAll('input[name="roles[]"]').forEach(input => input.addEventListener('change', syncProgramField));
        syncProgramField();

        let isDirty = false;
        let isSubmitting = false;

        form.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('change', () => isDirty = true);
            input.addEventListener('keydown', () => isDirty = true);
        });

        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                btnSubmit.disabled = false;
                return;
            }

            isSubmitting = true;
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...';
        });

        window.addEventListener('beforeunload', function(e) {
            if (isDirty && !isSubmitting) {
                e.preventDefault();
                e.returnValue = 'Apakah Anda yakin ingin meninggalkan halaman ini? Perubahan yang belum disimpan akan hilang.';
                return e.returnValue;
            }
        });
    });
</script>
@endpush
