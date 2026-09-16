@extends('admin_dashboard.layout.master')
@section('title', ($agama ? 'Ubah' : 'Tambah') . ' Data Agama')

@section('content')

{{-- Page Hero --}}
@include('admin_dashboard.components.page-hero', [
    'title' => ($agama ? 'Ubah' : 'Tambah') . ' Data Agama',
    'subtitle' => 'Formulir untuk ' . ($agama ? 'memperbarui' : 'membuat') . ' data referensi pilihan agama calon mahasiswa baru.',
    'icon' => 'server',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Data Master'],
        ['label' => 'Agama', 'url' => route('admin.agama.index')],
        ['label' => $agama ? 'Ubah' : 'Tambah']
    ]
])

<div class="row justify-content-center">
    <div class="col-lg-7">
        @component('admin_dashboard.components.admin-card', [
            'title' => ($agama ? 'Ubah Detail Agama' : 'Tambah Data Agama Baru'),
            'icon' => 'bx bx-server',
            'noPadding' => false
        ])
            <form action="{{ $action_url }}" method="POST" id="agamaForm" class="needs-validation" novalidate>
                @csrf
                @if($method == 'PUT')
                    @method('PUT')
                @endif

                <div class="row g-4">
                    {{-- Nama Agama --}}
                    <div class="col-12">
                        @include('admin_dashboard.components.form-input', [
                            'name' => 'nama_agama',
                            'label' => 'Nama Agama',
                            'type' => 'text',
                            'required' => true,
                            'value' => $agama?->nama_agama,
                            'placeholder' => 'Contoh: Islam, Kristen, Katolik...',
                            'help' => 'Masukkan nama agama dengan huruf kapital di awal.'
                        ])
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex justify-content-end gap-3 mt-5 pt-3 border-top">
                    <a href="{{ route('admin.agama.index') }}" class="btn btn-outline-secondary btn-lg px-4" id="btnCancel">
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
    $('#agamaForm').on('submit', function() {
        const $btn = $('#btnSubmit');
        if($btn.length) {
            $btn.prop('disabled', true);
            $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Menyimpan...');
        }
    });

    // Form dirty check warning
    let isFormDirty = false;
    $('#agamaForm input').on('change input', function() {
        isFormDirty = true;
    });
    $('#agamaForm').on('submit', function() {
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
