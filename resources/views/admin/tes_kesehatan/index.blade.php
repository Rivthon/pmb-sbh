@extends('admin_dashboard.layout.master')

@section('content')
@php
    $selectedRecommendation = request('rekomendasi');
    $recommendationTones = [
        'layak' => 'success',
        'tidak layak' => 'danger',
        'perlu pemeriksaan lanjutan' => 'warning',
    ];
@endphp

<div class="container py-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-primary">
                <i class="bx bx-file me-2"></i> Laporan Tes Kesehatan
            </h4>
            <div class="text-muted">Rangkuman rekomendasi dan hasil pemeriksaan kesehatan calon mahasiswa.</div>
        </div>
        <a href="{{ route('admin.tes-kesehatan.cetak-semua', request()->only(['search', 'rekomendasi'])) }}" target="_blank" class="btn btn-danger">
            <i class="bx bx-printer me-1"></i> Cetak Semua
        </a>
    </div>

    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-pill px-3 py-2">
        <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.tes-kesehatan.index') }}" class="row g-2 align-items-center">
                <div class="col-lg-5">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nama mahasiswa">
                </div>
                <div class="col-lg-3">
                    <select name="rekomendasi" class="form-select">
                        <option value="">Semua Rekomendasi</option>
                        @foreach($recommendationOptions as $value => $label)
                            <option value="{{ $value }}" @selected($selectedRecommendation === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <button class="btn btn-primary w-100">
                        <i class="bx bx-filter-alt me-1"></i> Filter
                    </button>
                </div>
                @if(request()->filled('search') || request()->filled('rekomendasi'))
                <div class="col-sm-6 col-lg-2">
                    <a href="{{ route('admin.tes-kesehatan.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bx bx-reset me-1"></i> Reset
                    </a>
                </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h5 class="mb-1">Rangkuman Hasil Tes Kesehatan</h5>
                <div class="text-muted small">Kolom hasil otomatis membaca kelainan pada pemeriksaan fisik.</div>
            </div>
            @if($selectedRecommendation && isset($recommendationOptions[$selectedRecommendation]))
                <span class="badge bg-label-{{ $recommendationTones[$selectedRecommendation] ?? 'secondary' }}">
                    {{ $recommendationOptions[$selectedRecommendation] }}
                </span>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th width="5%" class="text-center">#</th>
                            <th>Nama</th>
                            <th width="18%">Rekomendasi</th>
                            <th>Hasil Tes Kesehatan</th>
                            <th width="14%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($anamnesaList as $item)
                            @php
                                $exam = $item->pemeriksaan;
                                $recommendation = $exam?->rekomendasi;
                            @endphp
                            <tr class="align-middle">
                                <td class="text-center text-muted">{{ $anamnesaList->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $item->user->name ?? '-' }}</div>
                                    <div class="text-muted small">{{ $item->user?->jurusan?->nama_jurusan ?? '-' }}</div>
                                </td>
                                <td>
                                    @if($exam)
                                        <span class="badge bg-label-{{ $recommendationTones[$recommendation] ?? 'secondary' }}">
                                            {{ $exam->recommendationLabel() }}
                                        </span>
                                    @else
                                        <span class="badge bg-label-warning">Belum Diperiksa</span>
                                    @endif
                                </td>
                                <td class="text-break">
                                    {{ $exam ? $exam->healthSummary() : '-' }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.tes-kesehatan.show', $item->id) }}" class="btn btn-sm btn-icon btn-outline-primary me-1" data-bs-toggle="tooltip" title="Lihat Detail">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    @if($exam)
                                    <a href="{{ route('admin.tes-kesehatan.cetak', $item->id) }}" target="_blank" class="btn btn-sm btn-icon btn-outline-danger me-1" data-bs-toggle="tooltip" title="Cetak Hasil Kesehatan">
                                        <i class="bx bx-printer"></i>
                                    </a>
                                    @endif
                                    <form action="{{ route('admin.tes-kesehatan.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-icon btn-outline-danger" data-bs-toggle="tooltip" title="Hapus Data">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bx bx-info-circle me-1"></i> Belum ada data tes kesehatan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top">
                {{ $anamnesaList->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (el) {
            return new bootstrap.Tooltip(el);
        });
    });
</script>
@endsection
