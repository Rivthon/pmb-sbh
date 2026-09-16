@extends('admin_dashboard.layout.master')

@section('title', 'Tambah Permission')

@section('content')
@php
    $breadcrumbs = [
        ['label' => 'Pengaturan', 'url' => route('admin.permissions.index')],
        ['label' => 'Tambah Permission']
    ];
@endphp

{{-- ✅ Page Hero --}}
@include('admin_dashboard.components.page-hero', [
    'title' => 'Tambah Permission Baru',
    'subtitle' => 'Buat identitas hak akses baru untuk meluaskan pengaturan operasional sistem.',
    'icon' => 'key',
    'breadcrumbs' => $breadcrumbs
])

<div class="container-fluid px-0">
    <div class="row">
        <div class="col-lg-6 col-md-8 col-12">
            <form id="permissionForm" action="{{ route('admin.permissions.store') }}" method="POST">
                @csrf

                @component('admin_dashboard.components.admin-card', [
                    'title' => 'Detail Hak Akses Baru',
                    'icon' => 'bx bx-plus-circle text-primary',
                    'subtitle' => 'Silakan tentukan nama pengenal sistem untuk hak akses baru.'
                ])
                    <div class="row g-3">
                        <div class="col-md-12">
                            @include('admin_dashboard.components.form-input', [
                                'name' => 'name',
                                'label' => 'Nama Permission (Sistem)',
                                'placeholder' => 'Misal: pmb.view-internal, report.audit',
                                'required' => true,
                                'value' => old('name'),
                                'help' => 'Gunakan format huruf kecil dipisah titik untuk konsistensi (contoh: modul.aksi).'
                            ])
                        </div>
                    </div>
                @endcomponent

                {{-- Action Card --}}
                @component('admin_dashboard.components.admin-card', [
                    'title' => 'Aksi Formulir',
                    'icon' => 'bx bx-check-double text-success'
                ])
                    <div class="d-flex align-items-center gap-2 mb-0">
                        <button type="submit" id="btnSubmit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan Permission</button>
                        <a href="{{ route('admin.permissions.index') }}" class="btn btn-label-secondary"><i class="bx bx-x me-1"></i> Batal</a>
                    </div>
                @endcomponent
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Submit loading state
    const form = document.getElementById('permissionForm');
    const btnSubmit = document.getElementById('btnSubmit');
    form.addEventListener('submit', function() {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...';
    });

    // Dirty form warning
    let isDirty = false;
    const inputs = form.querySelectorAll('input');
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
</script>
@endsection