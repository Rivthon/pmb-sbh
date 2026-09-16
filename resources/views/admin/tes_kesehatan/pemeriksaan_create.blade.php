@extends('admin_dashboard.layout.master')
@section('title', 'Tambah Pemeriksaan Kesehatan')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-4 text-primary">Form Pemeriksaan Fisik Mahasiswa</h4>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('admin.tes-kesehatan.pemeriksaan.store') }}" method="POST">
                @csrf
                <input type="hidden" name="tes_kesehatan_anamnesa_id" value="{{ $anamnesa->id }}">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Pemeriksa</label>
                        <input type="text" name="nama_pemeriksa" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tanggal Pemeriksaan</label>
                        <input type="date" name="tanggal_pemeriksaan" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tinggi Badan (cm)</label>
                        <input type="number" name="tinggi_badan" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Berat Badan (kg)</label>
                        <input type="number" name="berat_badan" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tekanan Darah</label>
                        <input type="text" name="tekanan_darah" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Buta Warna</label>
                        <input type="text" name="buta_warna" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Bunyi Jantung</label>
                        <input type="text" name="bunyi_jantung" class="form-control">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Catatan Pemeriksaan</label>
                        <textarea name="catatan" rows="3" class="form-control"></textarea>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Rekomendasi</label>
                        <textarea name="rekomendasi" rows="2" class="form-control"
                            placeholder="Contoh: Lulus, perlu pemeriksaan lanjutan, dsb"></textarea>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save"></i> Simpan Pemeriksaan
                    </button>
                    <a href="{{ route('admin.tes-kesehatan.pemeriksaan.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
