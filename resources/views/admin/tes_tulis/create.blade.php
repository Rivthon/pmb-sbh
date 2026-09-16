@extends('admin_dashboard.layout.master')
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">Tambah Tes Tulis</h4>
        <a href="{{ route('admin.tes-tulis.index') }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.tes-tulis.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Tes</label>
                    <input type="text" name="nama_tes" class="form-control" value="{{ old('nama_tes') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3">{{ old('deskripsi') }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Durasi (menit)</label>
                        <input type="number" name="durasi_menit" class="form-control" value="{{ old('durasi_menit') }}"
                            required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Skor Lulus</label>
                        <input type="number" name="skor_lulus" class="form-control" value="{{ old('skor_lulus') }}"
                            required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Status Aktif</label>
                        <select name="status_aktif" class="form-select">
                            <option value="1" {{ old('status_aktif')==1 ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('status_aktif')==0 ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="acak_soal" value="1" id="acakSoal" {{
                                old('acak_soal') ? 'checked' : '' }}>
                            <label class="form-check-label" for="acakSoal">Acak Soal</label>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="acak_pilihan" value="1"
                                id="acakPilihan" {{ old('acak_pilihan') ? 'checked' : '' }}>
                            <label class="form-check-label" for="acakPilihan">Acak Pilihan Jawaban</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection