@extends('admin_dashboard.layout.master')
@section('title', ($jurusan ? 'Ubah' : 'Tambah') . ' Program Studi')

@section('content')

{{-- Page Hero --}}
@include('admin_dashboard.components.page-hero', [
    'title' => ($jurusan ? 'Ubah' : 'Tambah') . ' Program Studi',
    'subtitle' => 'Formulir untuk ' . ($jurusan ? 'memperbarui' : 'membuat') . ' data Program Studi / Jurusan pendidikan STIKES BOGOR HUSADA.',
    'icon' => 'book-open',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Data Master'],
        ['label' => 'Program Studi', 'url' => route('admin.jurusan.index')],
        ['label' => $jurusan ? 'Ubah' : 'Tambah']
    ]
])

<div class="row justify-content-center">
    <div class="col-lg-8">
        @component('admin_dashboard.components.admin-card', [
            'title' => ($jurusan ? 'Ubah Detail Program Studi' : 'Tambah Program Studi Baru'),
            'icon' => 'bx bx-book-open',
            'noPadding' => false
        ])
            <form action="{{ $action_url }}" method="POST" id="jurusanForm" class="needs-validation" novalidate>
                @csrf
                @if($method == 'PUT')
                    @method('PUT')
                @endif

                <div class="row g-4">
                    {{-- Kode Jurusan --}}
                    <div class="col-md-6">
                        @include('admin_dashboard.components.form-input', [
                            'name' => 'kd_jurusan',
                            'label' => 'Kode Program Studi',
                            'type' => 'text',
                            'required' => true,
                            'value' => $jurusan?->kd_jurusan,
                            'placeholder' => 'Contoh: S1-KEB',
                            'disabled' => ($method == 'PUT'),
                            'help' => ($method == 'PUT') ? 'Kode Program Studi tidak dapat diubah.' : 'Kode identifikasi unik program studi.'
                        ])
                        {{-- Hidden field to preserve value in request when disabled --}}
                        @if($method == 'PUT')
                            <input type="hidden" name="kd_jurusan" value="{{ $jurusan?->kd_jurusan }}">
                        @endif
                    </div>

                    {{-- Nama Jurusan --}}
                    <div class="col-md-6">
                        @include('admin_dashboard.components.form-input', [
                            'name' => 'nama_jurusan',
                            'label' => 'Nama Program Studi',
                            'type' => 'text',
                            'required' => true,
                            'value' => $jurusan?->nama_jurusan,
                            'placeholder' => 'Contoh: S1 Kebidanan',
                            'help' => 'Nama lengkap Program Studi.'
                        ])
                    </div>

                    {{-- Deskripsi --}}
                    <div class="col-12">
                        @include('admin_dashboard.components.form-textarea', [
                            'name' => 'deskripsi',
                            'label' => 'Deskripsi Keterangan',
                            'required' => false,
                            'value' => $jurusan?->deskripsi,
                            'placeholder' => 'Masukkan keterangan singkat mengenai program studi, akreditasi, atau prospek...',
                            'rows' => 4
                        ])
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex justify-content-end gap-3 mt-5 pt-3 border-top">
                    <a href="{{ route('admin.jurusan.index') }}" class="btn btn-outline-secondary btn-lg px-4" id="btnCancel">
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
    $('#jurusanForm').on('submit', function() {
        const $btn = $('#btnSubmit');
        if($btn.length) {
            $btn.prop('disabled', true);
            $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Menyimpan...');
        }
    });

    // Form dirty check warning
    let isFormDirty = false;
    $('#jurusanForm input, #jurusanForm select, #jurusanForm textarea').on('change input', function() {
        isFormDirty = true;
    });
    $('#jurusanForm').on('submit', function() {
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
