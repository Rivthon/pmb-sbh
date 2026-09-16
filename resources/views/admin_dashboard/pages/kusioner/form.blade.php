@extends('admin_dashboard.layout.master')
@section('title', ($kuesioner ? 'Ubah' : 'Tambah') . ' Data Kuesioner')

@section('content')

{{-- Page Hero --}}
@include('admin_dashboard.components.page-hero', [
    'title' => ($kuesioner ? 'Ubah' : 'Tambah') . ' Data Kuesioner',
    'subtitle' => 'Formulir untuk ' . ($kuesioner ? 'memperbarui' : 'membuat') . ' data pertanyaan asal info / kuesioner PMB calon mahasiswa baru.',
    'icon' => 'server',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Data Master'],
        ['label' => 'Kuesioner', 'url' => route('admin.kuesioner.index')],
        ['label' => $kuesioner ? 'Ubah' : 'Tambah']
    ]
])

<div class="row justify-content-center">
    <div class="col-lg-7">
        @component('admin_dashboard.components.admin-card', [
            'title' => ($kuesioner ? 'Ubah Detail Kuesioner' : 'Tambah Kuesioner Baru'),
            'icon' => 'bx bx-help-circle',
            'noPadding' => false
        ])
            <form action="{{ $action_url }}" method="POST" id="kusionerForm" class="needs-validation" novalidate>
                @csrf
                @if($method == 'PUT')
                    @method('PUT')
                @endif

                <div class="row g-4">
                    {{-- Nama Kusioner --}}
                    <div class="col-12">
                        @include('admin_dashboard.components.form-input', [
                            'name' => 'nama_kusioner',
                            'label' => 'Nama Sumber Informasi / Kuesioner',
                            'type' => 'text',
                            'required' => true,
                            'value' => $kuesioner?->nama_kusioner,
                            'placeholder' => 'Contoh: Brosur, Media Sosial, Rekomendasi Teman...',
                            'help' => 'Masukkan pilihan opsi asal informasi pendaftaran bagi pendaftar.'
                        ])
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex justify-content-end gap-3 mt-5 pt-3 border-top">
                    <a href="{{ route('admin.kuesioner.index') }}" class="btn btn-outline-secondary btn-lg px-4" id="btnCancel">
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
    $('#kusionerForm').on('submit', function() {
        const $btn = $('#btnSubmit');
        if($btn.length) {
            $btn.prop('disabled', true);
            $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Menyimpan...');
        }
    });

    // Form dirty check warning
    let isFormDirty = false;
    $('#kusionerForm input').on('change input', function() {
        isFormDirty = true;
    });
    $('#kusionerForm').on('submit', function() {
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
