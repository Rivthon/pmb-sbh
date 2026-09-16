@extends('admin_dashboard.layout.master')
@section('title', 'Data Pemeriksaan Kesehatan')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary">Data Pemeriksaan Kesehatan</h4>

        {{-- Tombol tambah data --}}
        @if($pemeriksaans->count() > 0)
        <a href="{{ route('admin.tes-kesehatan.pemeriksaan.create', $pemeriksaans->first()->tes_kesehatan_anamnesa_id ?? 0) }}"
            class="btn btn-primary">
            <i class="bx bx-plus"></i> Tambah Pemeriksaan
        </a>
        @endif
    </div>

    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama Mahasiswa</th>
                        <th>Tanggal Pemeriksaan</th>
                        <th>Pemeriksa</th>
                        <th>Rekomendasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pemeriksaans as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->anamnesa->user->name ?? '-' }}</td>
                        <td>
                            {{ $item->tanggal_pemeriksaan
                            ? \Carbon\Carbon::parse($item->tanggal_pemeriksaan)->format('d M Y')
                            : '-' }}
                        </td>
                        <td>{{ $item->nama_pemeriksa ?? '-' }}</td>
                        <td>{{ $item->rekomendasi ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.tes-kesehatan.pemeriksaan.show', $item->id) }}"
                                class="btn btn-sm btn-info">
                                <i class="bx bx-show"></i>
                            </a>
                            <a href="{{ route('admin.tes-kesehatan.pemeriksaan.edit', $item->id) }}"
                                class="btn btn-sm btn-warning">
                                <i class="bx bx-edit"></i>
                            </a>
                            <form action="{{ route('admin.tes-kesehatan.pemeriksaan.destroy', $item->id) }}"
                                method="POST" class="d-inline"
                                onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="bx bx-info-circle"></i>
                            <p class="mt-2 mb-3">Belum ada data pemeriksaan.</p>
                            <a href="{{ route('admin.tes-kesehatan.index') }}" class="btn btn-outline-primary">
                                <i class="bx bx-plus"></i> Tambah Pemeriksaan Baru
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($pemeriksaans->count() > 0)
            <div class="mt-3">
                {{ $pemeriksaans->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
