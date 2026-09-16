@extends('admin_dashboard.layout.master')
@section('title', 'Gambar Utama Website')

@section('content')
<div class="container">
    <div class="mb-4">
        <h4 class="fw-bold mb-1"><i class="bx bx-image text-primary me-2"></i>Gambar Utama Website</h4>
        <p class="text-muted mb-0">Ganti gambar hero landing page dan latar halaman login/registrasi.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="bx bx-check-circle me-1"></i>{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="row g-4">
        @foreach ($media as $item)
            <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header border-bottom">
                        <h5 class="mb-1 fw-bold">{{ $item->label }}</h5>
                        <small class="text-muted">{{ $item->key === 'hero_image' ? 'Ditampilkan di sisi kanan bagian hero halaman utama.' : 'Digunakan sebagai latar halaman login dan registrasi.' }}</small>
                    </div>
                    <div class="card-body">
                        <div class="rounded border bg-light overflow-hidden mb-3" style="height:280px">
                            <img id="preview-{{ $item->id }}" src="{{ $item->url }}" alt="{{ $item->label }}" class="w-100 h-100" style="object-fit:cover" onerror="this.classList.add('d-none'); document.getElementById('preview-error-{{ $item->id }}').classList.remove('d-none')">
                            <div id="preview-error-{{ $item->id }}" class="d-none d-flex h-100 align-items-center justify-content-center text-center text-muted p-4">
                                <div><i class="bx bx-image-alt fs-1 d-block mb-2"></i>Gambar tidak dapat ditampilkan.</div>
                            </div>
                        </div>
                        <div class="border rounded p-3 mb-4 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold"><i class="bx bx-file me-1"></i>File Saat Ini</span>
                                <span class="badge {{ $item->is_custom ? 'bg-label-success' : 'bg-label-secondary' }}">{{ $item->is_custom ? 'Upload Admin' : 'Gambar Bawaan' }}</span>
                            </div>
                            <div class="row g-2 small">
                                <div class="col-12"><span class="text-muted">Nama:</span> <span class="text-break">{{ $item->file_name }}</span></div>
                                <div class="col-6"><span class="text-muted">Format:</span> {{ $item->file_format }}</div>
                                <div class="col-6"><span class="text-muted">Ukuran:</span> {{ $item->file_size_label }}</div>
                                <div class="col-6"><span class="text-muted">Dimensi:</span> {{ $item->dimensions }}</div>
                                <div class="col-6"><span class="text-muted">Diperbarui:</span> {{ $item->updated_at?->format('d/m/Y H:i') ?? '-' }}</div>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.landing-media.update', $item) }}" enctype="multipart/form-data">
                            @csrf @method('PUT')
                            <label class="form-label fw-semibold" for="image-{{ $item->id }}">Pilih Gambar Baru</label>
                            <input class="form-control media-image-input" type="file" name="image" id="image-{{ $item->id }}" accept="image/jpeg,image/png,image/webp" data-preview="preview-{{ $item->id }}" required>
                            <div class="form-text">Format JPG, PNG, atau WebP. Maksimal 5 MB. Disarankan gambar horizontal beresolusi tinggi.</div>
                            <div class="text-end mt-3"><button class="btn btn-primary"><i class="bx bx-upload me-1"></i>Unggah & Ganti Gambar</button></div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@push('script')
<script>
document.querySelectorAll('.media-image-input').forEach(function (input) {
    input.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (file) {
            const preview = document.getElementById(this.dataset.preview);
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
            document.getElementById('preview-error-' + this.dataset.preview.replace('preview-', '')).classList.add('d-none');
        }
    });
});
</script>
@endpush
