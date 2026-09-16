@extends('admin_dashboard.layout.master')
@section('title', ($penghasilanOrtu ? 'Ubah' : 'Tambah') . ' Penghasilan Orang Tua')

@section('content')

{{-- Page Hero --}}
@include('admin_dashboard.components.page-hero', [
    'title' => ($penghasilanOrtu ? 'Ubah' : 'Tambah') . ' Penghasilan Orang Tua',
    'subtitle' => 'Formulir untuk ' . ($penghasilanOrtu ? 'memperbarui' : 'membuat') . ' data referensi rentang penghasilan gabungan orang tua/wali calon mahasiswa.',
    'icon' => 'server',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Data Master'],
        ['label' => 'Penghasilan Orang Tua', 'url' => route('admin.penghasilan-orang-tua.index')],
        ['label' => $penghasilanOrtu ? 'Ubah' : 'Tambah']
    ]
])

<div class="row justify-content-center">
    <div class="col-lg-7">
        @component('admin_dashboard.components.admin-card', [
            'title' => ($penghasilanOrtu ? 'Ubah Detail Penghasilan' : 'Tambah Rentang Penghasilan Baru'),
            'icon' => 'bx bx-wallet',
            'noPadding' => false
        ])
            <form action="{{ $action_url }}" method="POST" id="penghasilanForm" class="needs-validation" novalidate>
                @csrf
                @if($method == 'PUT')
                    @method('PUT')
                @endif

                <div class="row g-4">
                    {{-- Nama Rentang Penghasilan --}}
                    <div class="col-12">
                        @include('admin_dashboard.components.form-input', [
                            'name' => 'nama_peng',
                            'label' => 'Rentang Penghasilan Bulanan',
                            'type' => 'text',
                            'required' => true,
                            'value' => $penghasilanOrtu?->nama_peng,
                            'placeholder' => 'Contoh: Rp 1.000.000 - Rp 3.000.000, > Rp 5.000.000...',
                            'help' => 'Masukkan format rentang nominal rupiah secara informatif dan rapi.'
                        ])
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex justify-content-end gap-3 mt-5 pt-3 border-top">
                    <a href="{{ route('admin.penghasilan-orang-tua.index') }}" class="btn btn-outline-secondary btn-lg px-4" id="btnCancel">
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
    $('#penghasilanForm').on('submit', function() {
        const $btn = $('#btnSubmit');
        if($btn.length) {
            $btn.prop('disabled', true);
            $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Menyimpan...');
        }
    });

    // Form dirty check warning
    let isFormDirty = false;
    $('#penghasilanForm input').on('change input', function() {
        isFormDirty = true;
    });
    $('#penghasilanForm').on('submit', function() {
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
