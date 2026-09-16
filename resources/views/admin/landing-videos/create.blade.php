@extends('admin_dashboard.layout.master')
@section('title', 'Tambah Video Shorts')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class='bx bx-plus-circle text-primary me-2'></i>Tambah Video Shorts
                </h4>
                <p class="text-muted mb-0">Tambahkan video YouTube Shorts baru untuk ditampilkan di landing page</p>
            </div>
            <a href="{{ route('admin.landing-videos.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>

        {{-- Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <form action="{{ route('admin.landing-videos.store') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold required">Link atau Video ID YouTube</label>
                                <input type="text" class="form-control" name="youtube_video_id" id="youtube_video_id" 
                                       value="{{ old('youtube_video_id') }}" required 
                                       placeholder="Contoh: https://www.youtube.com/shorts/uuNixnANYwY atau uuNixnANYwY">
                                <div class="form-text">
                                    Anda dapat memasukkan link lengkap YouTube Shorts / video biasa, atau cukup masukkan 11 karakter Video ID-nya saja.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold required">Judul Tampilan</label>
                                <input type="text" class="form-control" name="title" value="{{ old('title') }}" required 
                                       placeholder="Masukkan judul menarik atau deskripsi singkat video">
                                <div class="form-text">Judul ini akan ditampilkan di bawah video pada card carousel landing page.</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Urutan Tampil (Sort Order)</label>
                                    <input type="number" class="form-control" name="sort_order" value="{{ old('sort_order', 0) }}">
                                    <div class="form-text">Angka lebih kecil akan ditampilkan lebih awal di carousel (kiri).</div>
                                </div>
                                <div class="col-md-6 d-flex align-items-center">
                                    <div class="form-check form-switch mt-md-4 pt-1">
                                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active" value="1" checked>
                                        <label class="form-check-label fw-semibold" for="is_active">Aktifkan Video</label>
                                    </div>
                                </div>
                            </div>

                            {{-- Preview Thumbnail --}}
                            <div class="mt-4 pt-3 border-top">
                                <label class="form-label fw-semibold d-block">Live Preview Thumbnail</label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded overflow-hidden bg-dark d-flex align-items-center justify-content-center" style="width: 120px; height: 160px;">
                                        <img src="" id="thumbnail_preview" alt="Preview" class="img-fluid d-none" style="object-fit: cover; width: 100%; height: 100%;">
                                        <i class='bx bxl-youtube text-muted fs-1' id="thumbnail_placeholder"></i>
                                    </div>
                                    <div class="text-muted small">
                                        <p class="mb-1 fw-semibold text-dark">ID Terdeteksi: <code id="detected_id">-</code></p>
                                        Thumbnail otomatis dimuat dari server YouTube jika link/ID valid.
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="card-footer text-end border-top">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i> Simpan Video
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const inputId = document.getElementById('youtube_video_id');
            const previewImg = document.getElementById('thumbnail_preview');
            const placeholderIcon = document.getElementById('thumbnail_placeholder');
            const detectedIdText = document.getElementById('detected_id');

            function extractId(urlOrId) {
                urlOrId = urlOrId.trim();
                if (/^[a-zA-Z0-9_-]{11}$/.test(urlOrId)) {
                    return urlOrId;
                }
                const match = urlOrId.match(/(?:youtube(?:-nocookie)?\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|shorts\/)|youtu\.be\/)([^"&?/\s]{11})/i);
                return match ? match[1] : null;
            }

            function updatePreview() {
                const val = inputId.value;
                const videoId = extractId(val);

                if (videoId) {
                    detectedIdText.textContent = videoId;
                    previewImg.src = `https://img.youtube.com/vi/${videoId}/hqdefault.jpg`;
                    previewImg.classList.remove('d-none');
                    placeholderIcon.classList.add('d-none');
                } else {
                    detectedIdText.textContent = '-';
                    previewImg.classList.add('d-none');
                    placeholderIcon.classList.remove('d-none');
                }
            }

            inputId.addEventListener('input', updatePreview);
            updatePreview(); // trigger on load if editing/old value exists
        });
    </script>
@endpush
