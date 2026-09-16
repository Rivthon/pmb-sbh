@extends('admin_dashboard.layout.master')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <h4 class="card-title mb-4">Data Wawancara PMB</h4>

                {{-- 🔍 SEARCH --}}
                <form method="GET" class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control"
                            placeholder="Cari nama mahasiswa, program studi..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary">
                            <i class="bx bx-search"></i> Cari
                        </button>
                    </div>
                </form>

                {{-- 📋 TABLE --}}
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th width="5%">No</th>
                                <th>Nama Mahasiswa</th>
                                <th>Program Studi</th>
                                <th>Status Wawancara</th>
                                <th width="18%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $item)
                            <tr>
                                <td class="text-center">
                                    {{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $item->user->name ?? '-' }}
                                    </div>
                                    <small class="text-muted">
                                        {{ $item->user->email ?? '' }}
                                    </small>
                                </td>

                                <td>
                                    {{ $item->user->jurusan->nama_jurusan ?? '-' }}
                                </td>

                                <td class="text-center">
                                    @if ($item->status === 'submitted')
                                    <span class="badge bg-warning">
                                        Menunggu Review
                                    </span>
                                    @elseif ($item->status === 'reviewed')
                                    <span class="badge bg-info">
                                        Sudah Direview
                                    </span>
                                    @elseif ($item->status === 'locked')
                                    <span class="badge bg-success">
                                        Final
                                    </span>
                                    @else
                                    <span class="badge bg-secondary">
                                        -
                                    </span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <a href="{{ route('admin.wawancara.show', $item) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-show"></i> Lihat
                                    </a>

                                    @if($item->status === 'submitted')
                                    <span class="badge bg-label-danger ms-1">
                                        Perlu Review
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Data wawancara belum tersedia
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- 📄 PAGINATION --}}
                <div class="d-flex justify-content-end mt-3">
                    {{ $data->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
