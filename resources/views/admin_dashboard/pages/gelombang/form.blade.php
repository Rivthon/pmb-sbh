@extends('admin_dashboard.layout.master')
@section('title', ($method == 'POST' ? 'Tambah' : 'Edit') . ' Gelombang PMB')

@section('content')

{{-- Page Hero --}}
@include('admin_dashboard.components.page-hero', [
    'title' => ($method == 'POST' ? 'Tambah' : 'Ubah') . ' Gelombang PMB',
    'subtitle' => 'Formulir untuk ' . ($method == 'POST' ? 'membuat' : 'memperbarui') . ' data tahapan gelombang PMB yang terikat dengan Periode Akademik.',
    'icon' => 'layer',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Data Master'],
        ['label' => 'Gelombang', 'url' => route('admin.gelombang.index')],
        ['label' => $method == 'POST' ? 'Tambah' : 'Ubah']
    ]
])

<div class="row justify-content-center">
    <div class="col-lg-8">
        @component('admin_dashboard.components.admin-card', [
            'title' => ($method == 'POST' ? 'Tambah Gelombang Baru' : 'Ubah Detail Gelombang'),
            'icon' => 'bx bx-edit-alt',
            'noPadding' => false
        ])
            <form action="{{ $action_url }}" method="POST" id="gelombangForm" class="needs-validation" novalidate>
                @csrf
                @if($method == 'PUT')
                    @method('PUT')
                @endif

                <div class="row g-4">
                    {{-- Periode ID Selection --}}
                    <div class="col-12">
                        <label for="periode_id" class="form-label fw-semibold text-dark">
                            Tahun Ajaran / Periode <span class="text-danger">*</span>
                        </label>
                        <select name="periode_id" id="periode_id" class="form-select form-select-lg @error('periode_id') is-invalid @enderror" required>
                            <option value="" disabled selected>-- Pilih Tahun Ajaran / Periode --</option>
                            @foreach ($periodes as $periode)
                                <option value="{{ $periode->id }}" @selected(old('periode_id', $gelombang?->periode_id) == $periode->id)>
                                    Tahun Ajaran {{ \Carbon\Carbon::parse($periode->tgl_mulai)->format('Y') }} - {{ $periode->deskripsi }}
                                    @if ($periode->status_periode == 'aktif')
                                         (Periode Aktif)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted small">Hubungkan gelombang ini dengan periode akademik tertentu agar tidak bentrok dengan periode lainnya.</div>
                        @error('periode_id')
                            <div class="invalid-feedback fw-medium mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Nama Gelombang --}}
                    <div class="col-12">
                        <label for="nama_gelombang" class="form-label fw-semibold text-dark">
                            Nama Gelombang <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-lg @error('nama_gelombang') is-invalid @enderror" 
                               name="nama_gelombang" id="nama_gelombang" placeholder="Contoh: Gelombang 1"
                               value="{{ old('nama_gelombang', $gelombang?->nama_gelombang) }}" required>
                        <div class="form-text text-muted small">Nama gelombang harus unik untuk periode terpilih.</div>
                        @error('nama_gelombang')
                            <div class="invalid-feedback fw-medium mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tanggal Mulai --}}
                    <div class="col-md-6">
                        <label for="tgl_mulai" class="form-label fw-semibold text-dark">
                            Tanggal Mulai <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bx bx-calendar text-muted"></i></span>
                            <input type="date" class="form-control form-control-lg border-start-0 ps-0 @error('tgl_mulai') is-invalid @enderror" 
                                   name="tgl_mulai" id="tgl_mulai" 
                                   value="{{ old('tgl_mulai', $gelombang?->tgl_mulai) }}" required>
                        </div>
                        @error('tgl_mulai')
                            <div class="invalid-feedback fw-medium mt-1 d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tanggal Selesai --}}
                    <div class="col-md-6">
                        <label for="tgl_selesai" class="form-label fw-semibold text-dark">
                            Tanggal Selesai <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bx bx-calendar-event text-muted"></i></span>
                            <input type="date" class="form-control form-control-lg border-start-0 ps-0 @error('tgl_selesai') is-invalid @enderror" 
                                   name="tgl_selesai" id="tgl_selesai" 
                                   value="{{ old('tgl_selesai', $gelombang?->tgl_selesai) }}" required>
                        </div>
                        @error('tgl_selesai')
                            <div class="invalid-feedback fw-medium mt-1 d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Status Gelombang --}}
                    <div class="col-12">
                        <label for="status_gelombang" class="form-label fw-semibold text-dark">
                            Status Gelombang <span class="text-danger">*</span>
                        </label>
                        <select class="form-select form-select-lg @error('status_gelombang') is-invalid @enderror" 
                                name="status_gelombang" id="status_gelombang" required>
                            <option value="aktif" @selected(old('status_gelombang', $gelombang?->status_gelombang ?? 'aktif') == 'aktif')>Aktif</option>
                            <option value="nonaktif" @selected(old('status_gelombang', $gelombang?->status_gelombang) == 'nonaktif')>Non Aktif</option>
                        </select>
                        <div class="form-text text-muted small">Gelombang aktif akan ditampilkan sebagai opsi utama pendaftaran PMB pada periode terpilih.</div>
                        @error('status_gelombang')
                            <div class="invalid-feedback fw-medium mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex justify-content-end gap-3 mt-5 pt-3 border-top">
                    <a href="{{ route('admin.gelombang.index') }}" class="btn btn-outline-secondary btn-lg px-4">
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

@push('script')
<script>
$(document).ready(function() {
    // Prevent double submit with loading spinner UX
    $('#gelombangForm').on('submit', function() {
        const $btn = $('#btnSubmit');
        if($btn.length) {
            $btn.prop('disabled', true);
            $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Menyimpan...');
        }
    });

    // Form dirty check warning
    let isFormDirty = false;
    $('#gelombangForm input, #gelombangForm select').on('change input', function() {
        isFormDirty = true;
    });
    $('#gelombangForm').on('submit', function() {
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
@endsection
