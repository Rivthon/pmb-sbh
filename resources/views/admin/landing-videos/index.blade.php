@extends('admin_dashboard.layout.master')
@section('title', 'Manajemen Video Shorts')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class='bx bxl-youtube text-danger me-2'></i>Manajemen Video Shorts
                </h4>
                <p class="text-muted mb-0">Kelola daftar video YouTube Shorts pada section "Cerita Mahasiswa Kami" di landing page</p>
            </div>
            <a href="{{ route('admin.landing-videos.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Tambah Video
            </a>
        </div>

        {{-- Alert --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Video List --}}
        <div class="card">
            <div class="table-responsive text-nowrap">
                <table class="table align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;">Urutan</th>
                            <th style="width: 120px;">Thumbnail</th>
                            <th class="text-start">Judul Video</th>
                            <th>Video ID</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($videos as $video)
                            <tr>
                                <td class="fw-bold">{{ $video->sort_order }}</td>
                                <td>
                                    <div class="rounded overflow-hidden bg-dark d-flex align-items-center justify-content-center mx-auto" style="width: 60px; height: 80px;">
                                        <img src="https://img.youtube.com/vi/{{ $video->youtube_video_id }}/hqdefault.jpg" 
                                             alt="Thumbnail" class="img-fluid" style="object-fit: cover; width: 100%; height: 100%;">
                                    </div>
                                </td>
                                <td class="text-start text-wrap fw-semibold" style="max-width: 300px;">
                                    {{ $video->title }}
                                </td>
                                <td>
                                    <code class="text-body">{{ $video->youtube_video_id }}</code>
                                    <a href="https://www.youtube.com/shorts/{{ $video->youtube_video_id }}" target="_blank" class="text-muted ms-1" title="Lihat di YouTube">
                                        <i class='bx bx-link-external'></i>
                                    </a>
                                </td>
                                <td>
                                    <form action="{{ route('admin.landing-videos.toggle', $video->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm border-0 p-0" title="Klik untuk ubah status">
                                            @if ($video->is_active)
                                                <span class="badge bg-label-success"><i class='bx bx-check me-1'></i> Aktif</span>
                                            @else
                                                <span class="badge bg-label-secondary"><i class='bx bx-x me-1'></i> Nonaktif</span>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('admin.landing-videos.edit', $video->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.landing-videos.destroy', $video->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus video ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-muted py-4">Belum ada data Video Shorts yang ditambahkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
