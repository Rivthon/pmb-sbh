@extends('admin_dashboard.layout.master')
@section('title', 'Hasil Tes Tulis - Calon Mahasiswa Baru')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Hasil Tes Tulis</h5>
        <a href="{{ route('admin.hasil-tes.index') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-refresh"></i> Refresh
        </a>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama Calon Maba</th>
                        <th>Skor</th>
                        <th>Benar</th>
                        <th>Salah</th>
                        <th>Status</th>
                        <th>Waktu Tes</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($hasilTes as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-semibold">{{ $item->user->name ?? '-' }}</div>
                            <small class="text-muted">{{ $item->user->email ?? '' }}</small>
                        </td>
                        <td><span class="fw-bold text-primary">{{ $item->skor }}</span></td>
                        <td>{{ $item->jawaban_benar }}</td>
                        <td>{{ $item->jawaban_salah }}</td>
                        <td>
                            @if ($item->status = 'selesai')
                            <span class="badge bg-label-success">Selesai</span>
                            @else
                            <span class="badge bg-label-warning">Belum</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ $item->waktu_mulai?->format('d M Y H:i') ?? '-' }}<br>
                                <i class="ti ti-arrow-down"></i><br>
                                {{ $item->waktu_selesai?->format('d M Y H:i') ?? '-' }}
                            </small>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                {{-- Tombol Detail --}}
                                <a href="{{ route('admin.hasil-tes.show', $item->id) }}"
                                    class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Lihat Detail">
                                    <i class="bx bx-show fs-5"></i>
                                </a>

                                {{-- Tombol Cetak PDF --}}
                                <a href="{{ route('admin.hasil-tes.cetak', $item->id) }}" target="_blank"
                                    class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Cetak Laporan PDF">
                                    <i class="bx bx-file fs-5"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection