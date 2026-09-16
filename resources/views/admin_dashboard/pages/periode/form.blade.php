@extends('admin_dashboard.layout.master')
@section('title', ($periode ? 'Ubah' : 'Tambah') . ' Periode Akademik')

@section('content')

{{-- Page Hero --}}
@include('admin_dashboard.components.page-hero', [
    'title' => ($periode ? 'Ubah' : 'Tambah') . ' Periode Akademik',
    'subtitle' => 'Formulir untuk ' . ($periode ? 'memperbarui' : 'membuat') . ' data tahun ajaran / periode penerimaan mahasiswa baru.',
    'icon' => 'calendar',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Data Master'],
        ['label' => 'Periode', 'url' => route('admin.periode.index')],
        ['label' => $periode ? 'Ubah' : 'Tambah']
    ]
])

<div class="row justify-content-center">
    <div class="col-lg-8">
        @component('admin_dashboard.components.admin-card', [
            'title' => ($periode ? 'Ubah Detail Periode' : 'Tambah Periode Baru'),
            'icon' => 'bx bx-calendar',
            'noPadding' => false
        ])
            <form action="{{ $action_url }}" method="POST" id="periodeForm" class="needs-validation" novalidate>
                @csrf
                @if($method == 'PUT')
                    @method('PUT')
                @endif

                <div class="row g-4">
                    {{-- Tanggal Mulai --}}
                    <div class="col-md-6">
                        @include('admin_dashboard.components.form-input', [
                            'name' => 'tgl_mulai',
                            'label' => 'Tanggal Mulai Periode',
                            'type' => 'date',
                            'required' => true,
                            'value' => $periode?->tgl_mulai,
                            'help' => 'Tanggal resmi dibukanya periode akademik.'
                        ])
                    </div>

                    {{-- Tanggal Tes --}}
                    <div class="col-md-6">
                        @include('admin_dashboard.components.form-input', [
                            'name' => 'tanggal_tes',
                            'label' => 'Tanggal Tes Ujian Masuk',
                            'type' => 'date',
                            'required' => false,
                            'value' => $periode?->tanggal_tes,
                            'help' => 'Jadwal pelaksanaan tes tulis PMB.'
                        ])
                    </div>

                    {{-- Deskripsi --}}
                    <div class="col-12">
                        @include('admin_dashboard.components.form-textarea', [
                            'name' => 'deskripsi',
                            'label' => 'Deskripsi / Nama Periode',
                            'required' => true,
                            'value' => $periode?->deskripsi,
                            'placeholder' => 'Contoh: Semester Ganjil TA 2026/2027',
                            'help' => 'Nama/deskripsi identifikasi periode akademik.'
                        ])
                    </div>

                    {{-- Status Periode --}}
                    <div class="col-md-6">
                        @include('admin_dashboard.components.form-select', [
                            'name' => 'status_periode',
                            'label' => 'Status Keaktifan',
                            'required' => true,
                            'selected' => $periode?->status_periode ?? 'nonaktif',
                            'options' => [
                                ['value' => 'aktif', 'label' => 'Aktif'],
                                ['value' => 'nonaktif', 'label' => 'Non Aktif']
                            ],
                            'help' => 'Periode aktif akan menjadi target default pendaftaran PMB.'
                        ])
                    </div>

                    {{-- Linked Link --}}
                    <div class="col-md-6">
                        @include('admin_dashboard.components.form-input', [
                            'name' => 'linked',
                            'label' => 'Tautan / Kode Sinkronisasi',
                            'type' => 'text',
                            'required' => false,
                            'value' => $periode?->linked,
                            'placeholder' => 'Contoh: pmb-2026-ganjil',
                            'help' => 'Kode eksternal sinkronisasi atau keterangan tambahan.'
                        ])
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex justify-content-end gap-3 mt-5 pt-3 border-top">
                    <a href="{{ route('admin.periode.index') }}" class="btn btn-outline-secondary btn-lg px-4" id="btnCancel">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg px-5" id="btnSubmit">
                        <i class="bx bx-save me-1"></i> Simpan Data
                    </button>
                </div>
            </form>
        @endcomponent
    </div>
</div>

@endsection

@push('script')
<script>
$(document).ready(function() {
    // Prevent double submit with loading spinner UX
    $('#periodeForm').on('submit', function() {
        const $btn = $('#btnSubmit');
        if($btn.length) {
            $btn.prop('disabled', true);
            $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Menyimpan...');
        }
    });

    // Form dirty check warning
    let isFormDirty = false;
    $('#periodeForm input, #periodeForm select, #periodeForm textarea').on('change input', function() {
        isFormDirty = true;
    });
    $('#periodeForm').on('submit', function() {
        isFormDirty = false;
    });
    window.addEventListener('beforeunload', function(e) {
        if (isFormDirty) {
            e.preventDefault();
            e.returnValue = 'Anda memiliki perubahan data yang belum disimpan. Yakin ingin meninggalkan halaman ini?';
            return e.returnValue;
        }
    });
});
</script>
@endpush