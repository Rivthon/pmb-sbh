@extends('admin_dashboard.layout.master')
@section('title', 'Edit Pemeriksaan Kesehatan')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-4 text-primary">Edit Pemeriksaan Kesehatan</h4>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('admin.tes-kesehatan.pemeriksaan.update', $pemeriksaan->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tinggi Badan (cm)</label>
                        <input type="number" name="tinggi_badan" class="form-control"
                            value="{{ $pemeriksaan->tinggi_badan }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Berat Badan (kg)</label>
                        <input type="number" name="berat_badan" class="form-control"
                            value="{{ $pemeriksaan->berat_badan }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tekanan Darah</label>
                        <input type="text" name="tekanan_darah" class="form-control"
                            value="{{ $pemeriksaan->tekanan_darah }}">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Catatan Pemeriksaan</label>
                        <textarea name="catatan" rows="3" class="form-control">{{ $pemeriksaan->catatan }}</textarea>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Rekomendasi</label>
                        <textarea name="rekomendasi" rows="2"
                            class="form-control">{{ $pemeriksaan->rekomendasi }}</textarea>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Simpan Perubahan</button>
                    <a href="{{ route('admin.tes-kesehatan.pemeriksaan.show', $pemeriksaan->id) }}"
                        class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
