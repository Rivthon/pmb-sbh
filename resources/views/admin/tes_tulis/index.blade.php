@extends('admin_dashboard.layout.master')
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">Daftar Tes Tulis</h4>
        <a href="{{ route('admin.tes-tulis.create') }}" class="btn btn-primary">+ Tambah Tes Tulis</a>
    </div>

    {{-- ✅ Alert Section --}}
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- ✅ Tabel Tes Tulis --}}
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-primary text-center align-middle">
                <tr>
                    <th width="5%">#</th>
                    <th>Nama Tes</th>
                    <th>Deskripsi</th>
                    <th width="10%">Durasi</th>
                    <th width="10%">Skor Lulus</th>
                    <th width="10%">Acak Soal</th>
                    <th width="10%">Acak Pilihan</th>
                    <th width="10%">Status</th>
                    <th width="15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tesTulis as $tes)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $tes->nama_tes }}</td>
                    <td>{{ Str::limit($tes->deskripsi, 60) }}</td>
                    <td class="text-center">{{ $tes->durasi_menit }} menit</td>
                    <td class="text-center">{{ $tes->skor_lulus }}</td>
                    <td class="text-center">
                        <span class="badge {{ $tes->acak_soal ? 'bg-success' : 'bg-secondary' }}">
                            {{ $tes->acak_soal ? 'Ya' : 'Tidak' }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $tes->acak_pilihan ? 'bg-success' : 'bg-secondary' }}">
                            {{ $tes->acak_pilihan ? 'Ya' : 'Tidak' }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $tes->status_aktif ? 'bg-success' : 'bg-danger' }}">
                            {{ $tes->status_aktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center align-items-center gap-2">
                            {{-- Lihat Soal --}}
                            <a href="{{ route('admin.soal.index', $tes->id) }}" class="btn btn-sm btn-info"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat Soal">
                                <i class="bx bx-book-open"></i>
                            </a>

                            {{-- Edit Tes --}}
                            <a href="{{ route('admin.tes-tulis.edit', $tes->id) }}" class="btn btn-sm btn-warning"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Tes">
                                <i class="bx bx-edit"></i>
                            </a>

                            {{-- Hapus Tes --}}
                            <form action="{{ route('admin.tes-tulis.destroy', $tes->id) }}" method="POST"
                                class="d-inline" onsubmit="return confirm('Yakin ingin menghapus tes ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Hapus Tes">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted">Belum ada data tes tulis.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ✅ Pagination --}}
    <div class="mt-3">
        {{ $tesTulis->links() }}
    </div>
</div>
@endsection