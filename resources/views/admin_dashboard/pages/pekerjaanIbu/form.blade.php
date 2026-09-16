@extends('admin_dashboard.layout.master')
@section('title', ($pekerjaanIbu ? 'Ubah' : 'Tambah') . ' Pekerjaan Ibu')

@section('content')

{{-- Page Hero --}}
@include('admin_dashboard.components.page-hero', [
    'title' => ($pekerjaanIbu ? 'Ubah' : 'Tambah') . ' Pekerjaan Ibu',
    'subtitle' => 'Formulir untuk ' . ($pekerjaanIbu ? 'memperbarui' : 'membuat') . ' data referensi pekerjaan ibu kandung calon mahasiswa.',
    'icon' => 'server',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Data Master'],
        ['label' => 'Pekerjaan Ibu', 'url' => route('admin.pekerjaan-ibu.index')],
        ['label' => $pekerjaanIbu ? 'Ubah' : 'Tambah']
    ]
])

<div class="row justify-content-center">
    <div class="col-lg-7">
        @component('admin_dashboard.components.admin-card', [
            'title' => ($pekerjaanIbu ? 'Ubah Detail Pekerjaan' : 'Tambah Pekerjaan Baru'),
            'icon' => 'bx bx-briefcase',
            'noPadding' => false
        ])
            <form action="{{ $action_url }}" method="POST" id="pekerjaanIbuForm" class="needs-validation" novalidate>
                @csrf
                @if($method == 'PUT')
                    @method('PUT')
                @endif

                <div class="row g-4">
                    {{-- Nama Pekerjaan Ibu --}}
                    <div class="col-12">
                        @include('admin_dashboard.components.form-input', [
                            'name' => 'nama_pek_ibu',
                            'label' => 'Pekerjaan Ibu',
                            'type' => 'text',
                            'required' => true,
                            'value' => $pekerjaanIbu?->nama_pek_ibu,
                            'placeholder' => 'Contoh: Ibu Rumah Tangga, Guru, Pegawai Swasta...',
                            'help' => 'Masukkan nama bidang pekerjaan atau profesi ibu secara umum.'
                        ])
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex justify-content-end gap-3 mt-5 pt-3 border-top">
                    <a href="{{ route('admin.pekerjaan-ibu.index') }}" class="btn btn-outline-secondary btn-lg px-4" id="btnCancel">
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
    $('#pekerjaanIbuForm').on('submit', function() {
        const $btn = $('#btnSubmit');
        if($btn.length) {
            $btn.prop('disabled', true);
            $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Menyimpan...');
        }
    });

    // Form dirty check warning
    let isFormDirty = false;
    $('#pekerjaanIbuForm input').on('change input', function() {
        isFormDirty = true;
    });
    $('#pekerjaanIbuForm').on('submit', function() {
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
