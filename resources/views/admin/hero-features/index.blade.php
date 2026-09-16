@extends('admin_dashboard.layout.master')
@section('title', 'Keunggulan Hero')

@section('content')
    <div class="container">
        <div class="mb-4">
            <h4 class="fw-bold mb-1"><i class="bx bx-grid-alt text-primary me-2"></i>Keunggulan Hero</h4>
            <p class="text-muted mb-0">Edit kartu keunggulan yang tampil di bagian utama landing page.</p>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bx bx-check-circle me-1"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="row g-4">
            @foreach ($features as $feature)
                <div class="col-xl-6">
                    <div class="card h-100">
                        <form action="{{ route('admin.hero-features.update', $feature) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="card-header d-flex align-items-center gap-3 border-bottom">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-{{ $feature->color }} bg-opacity-10 text-{{ $feature->color }}" style="width:48px;height:48px">
                                    <i class="{{ $feature->icon }} fs-4"></i>
                                </span>
                                <div><strong>{{ $feature->title }}</strong><div class="small text-muted">Kartu urutan {{ $feature->sort_order }}</div></div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Judul</label>
                                        <input type="text" name="title" class="form-control" value="{{ old('title', $feature->title) }}" maxlength="100" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Deskripsi</label>
                                        <input type="text" name="description" class="form-control" value="{{ old('description', $feature->description) }}" maxlength="160" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Class Boxicons</label>
                                        <input type="text" name="icon" class="form-control" value="{{ old('icon', $feature->icon) }}" placeholder="bx bx-laptop" required>
                                        <div class="form-text">Contoh: bx bx-laptop atau bxs bx-wallet-alt</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Warna</label>
                                        <select name="color" class="form-select" required>
                                            @foreach (['warning' => 'Kuning', 'success' => 'Hijau', 'info' => 'Biru', 'danger' => 'Merah', 'primary' => 'Ungu', 'secondary' => 'Abu-abu'] as $value => $label)
                                                <option value="{{ $value }}" @selected(old('color', $feature->color) === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Urutan</label>
                                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $feature->sort_order) }}" min="0" max="999" required>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="is_active" value="0">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active-{{ $feature->id }}" @checked(old('is_active', $feature->is_active))>
                                            <label class="form-check-label" for="active-{{ $feature->id }}">Tampilkan di landing page</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end border-top">
                                <button class="btn btn-primary" type="submit"><i class="bx bx-save me-1"></i>Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
