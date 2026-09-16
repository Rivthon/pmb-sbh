@extends('admin_dashboard.layout.master')
@section('title', 'Detail Pemeriksaan Kesehatan')

@section('content')
@php
    $includeUrine = (bool) $pemeriksaan->anamnesa?->user?->jurusan?->isD3Kebidanan();
    $officialExamSections = \App\Models\TesKesehatanPemeriksaan::officialExamSections($includeUrine);
@endphp

<div class="container py-4">
    <div class="d-flex justify-content-between mb-4">
        <h4 class="fw-bold text-primary">Detail Pemeriksaan Kesehatan</h4>
        <a href="{{ route('admin.tes-kesehatan.pemeriksaan.index') }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back"></i> Kembali
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-label-primary">
            <h6 class="fw-bold text-primary mb-0"><i class="bx bx-id-card me-2"></i>Identitas Pemeriksaan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <tr>
                        <th style="width: 240px">Nama Mahasiswa</th>
                        <td>{{ $pemeriksaan->anamnesa->user->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Program Studi</th>
                        <td>{{ $pemeriksaan->anamnesa->user->jurusan->nama_jurusan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Pemeriksaan</th>
                        <td>{{ $pemeriksaan->tanggal_pemeriksaan ? \Carbon\Carbon::parse($pemeriksaan->tanggal_pemeriksaan)->format('d M Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Nama Pemeriksa</th>
                        <td>{{ $pemeriksaan->nama_pemeriksa ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tinggi Badan</th>
                        <td>{{ $pemeriksaan->tinggi_badan ? $pemeriksaan->tinggi_badan . ' cm' : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Berat Badan</th>
                        <td>{{ $pemeriksaan->berat_badan ? $pemeriksaan->berat_badan . ' kg' : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tekanan Darah</th>
                        <td>{{ $pemeriksaan->tekanan_darah ? $pemeriksaan->tekanan_darah . ' mmHg' : '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-label-primary">
            <h6 class="fw-bold text-primary mb-0"><i class="bx bx-stethoscope me-2"></i>Hasil Pemeriksaan Fisik</h6>
        </div>
        <div class="card-body">
            @foreach($officialExamSections as $sectionTitle => $items)
            <div class="table-responsive mb-4">
                <h6 class="fw-bold text-primary mb-2">{{ $sectionTitle }}</h6>
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Jenis Pemeriksaan</th>
                            <th style="width: 140px">Hasil</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $field => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td>{{ $pemeriksaan->conditionLabel(data_get($pemeriksaan, "{$field}_kondisi")) }}</td>
                            <td>{{ data_get($pemeriksaan, "{$field}_keterangan") ?: '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endforeach
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-label-primary">
            <h6 class="fw-bold text-primary mb-0"><i class="bx bx-check-circle me-2"></i>Catatan & Rekomendasi</h6>
        </div>
        <div class="card-body">
            <table class="table table-bordered mb-0">
                <tr>
                    <th style="width: 240px">Catatan Pemeriksaan</th>
                    <td>{{ $pemeriksaan->catatan ?: '-' }}</td>
                </tr>
                <tr>
                    <th>Rekomendasi</th>
                    <td><strong>{{ $pemeriksaan->rekomendasi ?? '-' }}</strong></td>
                </tr>
            </table>
        </div>
    </div>
</div>
@endsection
