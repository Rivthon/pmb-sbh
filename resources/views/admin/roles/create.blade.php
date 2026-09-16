@extends('admin_dashboard.layout.master')

@section('title', 'Tambah Role')

@section('content')
@php
    $breadcrumbs = [
        ['label' => 'Pengaturan', 'url' => route('admin.roles.index')],
        ['label' => 'Tambah Role']
    ];

    // Readable Label Mapping for Permissions
    $permissionFriendlyNames = [
        'dashboard.view' => 'Lihat Dashboard',
        'pmb.view' => 'Lihat Data PMB',
        'pmb.create' => 'Tambah Pendaftar',
        'pmb.edit' => 'Ubah Data Pendaftar',
        'pmb.delete' => 'Hapus Pendaftar',
        'pmb.restore' => 'Restore Pendaftar',
        'pmb.force-delete' => 'Hapus Permanen PMB',
        'pmb.update-status' => 'Update Status Lulus',
        'pmb.verify-document' => 'Verifikasi Berkas',
        'pmb.export' => 'Ekspor Data PMB',
        'pmb.send-email' => 'Kirim Email PMB',
        'payment.view' => 'Lihat Keuangan',
        'payment.verify' => 'Verifikasi Pembayaran',
        'payment.reject' => 'Tolak Pembayaran',
        'payment.export' => 'Ekspor Transaksi',
        'master.view' => 'Lihat Data Master',
        'master.create' => 'Tambah Data Master',
        'master.edit' => 'Ubah Data Master',
        'master.delete' => 'Hapus Data Master',
        'master.restore' => 'Restore Data Master',
        'periode.view' => 'Lihat Periode',
        'periode.create' => 'Tambah Periode',
        'periode.edit' => 'Ubah Periode',
        'periode.delete' => 'Hapus Periode',
        'periode.set-active' => 'Aktifkan Periode',
        'gelombang.view' => 'Lihat Gelombang',
        'gelombang.create' => 'Tambah Gelombang',
        'gelombang.edit' => 'Ubah Gelombang',
        'gelombang.delete' => 'Hapus Gelombang',
        'gelombang.set-active' => 'Aktifkan Gelombang',
        'jurusan.view' => 'Lihat Jurusan',
        'jurusan.create' => 'Tambah Jurusan',
        'jurusan.edit' => 'Ubah Jurusan',
        'jurusan.delete' => 'Hapus Jurusan',
        'user.view' => 'Lihat User',
        'user.create' => 'Tambah User',
        'user.edit' => 'Ubah User',
        'user.delete' => 'Hapus User',
        'user.assign-role' => 'Assign Role',
        'role.view' => 'Lihat Role',
        'role.create' => 'Tambah Role',
        'role.edit' => 'Ubah Role',
        'role.delete' => 'Hapus Role',
        'role.assign-permission' => 'Bagi Hak Akses',
        'activity-log.view' => 'Lihat Log Aktivitas',
        'activity-log.export' => 'Ekspor Log Aktivitas',
        'activity-log.delete' => 'Hapus Log Aktivitas',
        'report.view' => 'Lihat Laporan',
        'report.export' => 'Ekspor Laporan',
        'setting.view' => 'Lihat Pengaturan',
        'setting.edit' => 'Ubah Pengaturan',
        'wawancara.review' => 'Review Wawancara',
        'kesehatan.review' => 'Review Medis',
        'landing-page.manage' => 'Kelola Landing Page',
    ];
@endphp

{{-- ✅ Page Hero --}}
@include('admin_dashboard.components.page-hero', [
    'title' => 'Tambah Role Baru',
    'subtitle' => 'Buat kelompok otorisasi baru dan hubungkan dengan hak akses mendetail untuk fitur sistem.',
    'icon' => 'shield-quarter',
    'breadcrumbs' => $breadcrumbs
])

<div class="container-fluid px-0">
    <form id="roleForm" action="{{ route('admin.roles.store') }}" method="POST">
        @csrf

        <div class="row">
            {{-- ✅ Form Left/Top Column - Role Info --}}
            <div class="col-12">
                @component('admin_dashboard.components.admin-card', [
                    'title' => 'Informasi Peran',
                    'icon' => 'bx bx-key text-primary',
                    'subtitle' => 'Silakan tentukan nama pengenal yang unik untuk role ini.'
                ])
                    <div class="row">
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-input', [
                                'name' => 'name',
                                'label' => 'Nama Role / Peran',
                                'placeholder' => 'Misal: Staf Akademik, Dosen Penguji...',
                                'required' => true,
                                'value' => old('name')
                            ])
                        </div>
                        <div class="col-md-6 d-flex align-items-end mb-3">
                            <div class="alert alert-warning py-2 mb-0 fs-7 d-flex align-items-center gap-2">
                                <i class="bx bx-info-circle fs-5"></i>
                                <span>Gunakan nama yang representatif agar memudahkan dalam pembagian ke user.</span>
                            </div>
                        </div>
                    </div>
                @endcomponent
            </div>

            {{-- ✅ Toolbar Filter & Actions --}}
            <div class="col-12 mb-4">
                <div class="card p-3 shadow-sm border border-light-50">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleAllPermissions(true)">
                                <i class="bx bx-check-double me-1"></i> Pilih Semua
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleAllPermissions(false)">
                                <i class="bx bx-x me-1"></i> Hapus Semua Pilihan
                            </button>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="input-group input-group-merge">
                                <span class="input-group-text text-muted"><i class="bx bx-search fs-5"></i></span>
                                <input type="text" id="permissionSearch" class="form-control form-control-sm" placeholder="Cari hak akses...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ✅ Grouped Permissions List --}}
            <div class="col-12">
                <div class="row g-4" id="permissionsContainer">
                    @foreach ($permissionModules as $moduleName => $permissions)
                        <div class="col-12 module-section" data-module="{{ strtolower($moduleName) }}">
                            <div class="card border shadow-sm h-100 animate-fade-in">
                                <div class="card-header bg-light d-flex align-items-center justify-content-between py-3 border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar avatar-sm rounded bg-label-primary d-flex align-items-center justify-content-center fw-bold fs-7">
                                            {{ substr($moduleName, 0, 1) }}
                                        </span>
                                        <div>
                                            <h6 class="mb-0 fw-semibold text-heading">{{ $moduleName }}</h6>
                                            <small class="text-muted fs-8">Modul Fitur</small>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        {{-- Module Selected Counter --}}
                                        <span class="badge bg-label-secondary fw-semibold fs-8 module-counter" id="counter_{{ Str::slug($moduleName) }}">
                                            0 / {{ count($permissions) }} Terpilih
                                        </span>
                                        {{-- Toggle Module Checkbox --}}
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input module-toggle" type="checkbox" id="toggle_{{ Str::slug($moduleName) }}" onclick="toggleModulePermissions('{{ Str::slug($moduleName) }}', this.checked)">
                                            <label class="form-check-label text-muted fs-8" for="toggle_{{ Str::slug($moduleName) }}">Pilih Semua</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body py-4">
                                    <div class="row g-3 module-permissions-list" id="list_{{ Str::slug($moduleName) }}">
                                        @foreach ($permissions as $perm)
                                            @php
                                                $friendlyName = $permissionFriendlyNames[$perm->name] ?? ucfirst(str_replace('.', ': ', $perm->name));
                                            @endphp
                                            <div class="col-md-4 col-sm-6 permission-item" data-name="{{ strtolower($perm->name) }}" data-friendly="{{ strtolower($friendlyName) }}">
                                                <div class="form-check border rounded p-2 px-3 hover-shadow-sm transition-all cursor-pointer d-flex align-items-center gap-2 bg-light-50">
                                                    <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]" value="{{ $perm->id }}" id="perm_{{ $perm->id }}"
                                                           data-module-id="{{ Str::slug($moduleName) }}" onclick="updateCounters()">
                                                    <label class="form-check-label cursor-pointer w-100" for="perm_{{ $perm->id }}">
                                                        <span class="d-block fw-semibold text-heading fs-7">{{ $friendlyName }}</span>
                                                        <span class="text-muted fs-9">{{ $perm->name }}</span>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ✅ Sticky Action/Status Footer --}}
            <div class="col-12 mt-5">
                <div class="card card-action sticky-action-bar border-top shadow-lg" style="position: sticky; bottom: 15px; z-index: 999; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: var(--admin-radius-lg);">
                    <div class="card-body d-flex justify-content-between align-items-center py-3 flex-wrap gap-3">
                        <div>
                            <span class="text-heading fw-semibold fs-6">Ringkasan Hak Akses:</span>
                            <span class="badge bg-label-primary ms-2 fs-7 fw-bold" id="globalCounter">0 / 0 Terpilih</span>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-label-secondary"><i class="bx bx-x me-1"></i> Batal</a>
                            <button type="submit" id="btnSubmit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan Role</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

@push('script')
<script>
    // Search filter logic
    const searchInput = document.getElementById('permissionSearch');
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        const modules = document.querySelectorAll('.module-section');
        
        modules.forEach(module => {
            const permissionItems = module.querySelectorAll('.permission-item');
            let visibleCount = 0;
            
            permissionItems.forEach(item => {
                const name = item.getAttribute('data-name');
                const friendly = item.getAttribute('data-friendly');
                
                if (name.includes(query) || friendly.includes(query)) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Hide whole module if no visible permissions inside it
            if (visibleCount > 0) {
                module.style.display = 'block';
            } else {
                module.style.display = 'none';
            }
        });
    });

    // Check/Uncheck all for a specific module
    function toggleModulePermissions(moduleId, isChecked) {
        const list = document.getElementById('list_' + moduleId);
        const checkboxes = list.querySelectorAll('.permission-checkbox');
        checkboxes.forEach(chk => {
            if (chk.closest('.permission-item').style.display !== 'none') {
                chk.checked = isChecked;
            }
        });
        updateCounters();
    }

    // Check/Uncheck all globally
    function toggleAllPermissions(isChecked) {
        const checkboxes = document.querySelectorAll('.permission-checkbox');
        checkboxes.forEach(chk => {
            if (chk.closest('.permission-item').style.display !== 'none') {
                chk.checked = isChecked;
            }
        });
        updateCounters();
    }

    // Counter updates
    function updateCounters() {
        const globalCheckboxList = document.querySelectorAll('.permission-checkbox');
        let globalCheckedCount = 0;
        
        // Modules map to track items
        const modulesData = {};
        
        globalCheckboxList.forEach(chk => {
            const moduleId = chk.getAttribute('data-module-id');
            if (!modulesData[moduleId]) {
                modulesData[moduleId] = { total: 0, checked: 0 };
            }
            
            modulesData[moduleId].total++;
            if (chk.checked) {
                globalCheckedCount++;
                modulesData[moduleId].checked++;
            }
        });
        
        // Update Module Header counters & switches
        for (const moduleId in modulesData) {
            const counter = document.getElementById('counter_' + moduleId);
            if (counter) {
                counter.innerText = `${modulesData[moduleId].checked} / ${modulesData[moduleId].total} Terpilih`;
                
                // Colorize counter badge if all selected
                if (modulesData[moduleId].checked === modulesData[moduleId].total) {
                    counter.className = 'badge bg-label-success fw-semibold fs-8 module-counter';
                } else if (modulesData[moduleId].checked > 0) {
                    counter.className = 'badge bg-label-warning fw-semibold fs-8 module-counter';
                } else {
                    counter.className = 'badge bg-label-secondary fw-semibold fs-8 module-counter';
                }
            }
            
            const toggle = document.getElementById('toggle_' + moduleId);
            if (toggle) {
                toggle.checked = (modulesData[moduleId].checked === modulesData[moduleId].total);
            }
        }
        
        // Update Global Counter
        const globalCounter = document.getElementById('globalCounter');
        globalCounter.innerText = `${globalCheckedCount} / ${globalCheckboxList.length} Terpilih`;
        if (globalCheckedCount === globalCheckboxList.length) {
            globalCounter.className = 'badge bg-label-success ms-2 fs-7 fw-bold';
        } else if (globalCheckedCount > 0) {
            globalCounter.className = 'badge bg-label-primary ms-2 fs-7 fw-bold';
        } else {
            globalCounter.className = 'badge bg-label-secondary ms-2 fs-7 fw-bold';
        }
    }

    // Submit loading state
    const form = document.getElementById('roleForm');
    const btnSubmit = document.getElementById('btnSubmit');
    form.addEventListener('submit', function(e) {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...';
    });

    // Dirty form warning
    let isDirty = false;
    const inputs = form.querySelectorAll('input, select, checkbox');
    inputs.forEach(input => {
        input.addEventListener('change', () => isDirty = true);
        input.addEventListener('keydown', () => isDirty = true);
    });

    window.addEventListener('beforeunload', function(e) {
        if (isDirty && !btnSubmit.disabled) {
            e.preventDefault();
            e.returnValue = 'Apakah Anda yakin ingin meninggalkan halaman ini? Perubahan yang belum disimpan akan hilang.';
            return e.returnValue;
        }
    });

    // Initial counter sync
    window.onload = function() {
        updateCounters();
    };
</script>
@endpush
