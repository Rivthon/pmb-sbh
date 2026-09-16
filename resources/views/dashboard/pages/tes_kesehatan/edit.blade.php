@extends('admin.layout.master')
@section('title', 'Edit Tes Kesehatan')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold text-primary mb-4">Edit Data Tes Kesehatan</h4>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.tes-kesehatan.update', $tes->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tekanan Darah</label>
                        <input type="text" name="tekanan_darah" class="form-control"
                            value="{{ old('tekanan_darah', $tes->tekanan_darah) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Berat Badan (kg)</label>
                        <input type="number" name="berat_badan" class="form-control"
                            value="{{ old('berat_badan', $tes->berat_badan) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tinggi Badan (cm)</label>
                        <input type="number" name="tinggi_badan" class="form-control"
                            value="{{ old('tinggi_badan', $tes->tinggi_badan) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold d-block">Buta Warna</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="buta_warna" value="Ya" {{
                                $tes->buta_warna == 'Ya' ? 'checked' : '' }}>
                            <label class="form-check-label">Ya</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="buta_warna" value="Tidak" {{
                                $tes->buta_warna == 'Tidak' ? 'checked' : '' }}>
                            <label class="form-check-label">Tidak</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold d-block">Hasil Pemeriksaan</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="hasil" value="Sehat" {{ $tes->hasil ==
                            'Sehat' ? 'checked' : '' }}>
                            <label class="form-check-label">Sehat</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="hasil" value="Tidak Sehat" {{ $tes->hasil
                            == 'Tidak Sehat' ? 'checked' : '' }}>
                            <label class="form-check-label">Tidak Sehat</label>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Catatan</label>
                        <textarea name="catatan" class="form-control"
                            rows="3">{{ old('catatan', $tes->catatan) }}</textarea>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button class="btn btn-primary">Update</button>
                    <a href="{{ route('admin.tes-kesehatan.index') }}" class="btn btn-outline-secondary ms-2">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection