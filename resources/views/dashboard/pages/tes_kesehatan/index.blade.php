@extends('admin.layout.master')
@section('title', 'Data Tes Kesehatan')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary">Daftar Tes Kesehatan Mahasiswa Baru</h4>
        <a href="{{ route('admin.tes-kesehatan.create', ['user' => 1]) }}" class="btn btn-primary">
            <i class="bx bx-plus"></i> Tambah Data
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama Mahasiswa</th>
                        <th>Tekanan Darah</th>
                        <th>Berat Badan</th>
                        <th>Tinggi Badan</th>
                        <th>Hasil</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tesKesehatan as $tes)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $tes->user->name ?? '-' }}</td>
                        <td>{{ $tes->tekanan_darah ?? '-' }}</td>
                        <td>{{ $tes->berat_badan ?? '-' }}</td>
                        <td>{{ $tes->tinggi_badan ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $tes->hasil == 'Sehat' ? 'success' : 'danger' }}">
                                {{ $tes->hasil }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.tes-kesehatan.show', $tes->id) }}"
                                class="btn btn-sm btn-info">Detail</a>
                            <a href="{{ route('admin.tes-kesehatan.edit', $tes->id) }}"
                                class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.tes-kesehatan.destroy', $tes->id) }}" method="POST"
                                class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Belum ada data tes kesehatan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection