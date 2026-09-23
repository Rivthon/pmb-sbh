@extends('admin_dashboard.layout.master')
@section('title', 'Manajemen Biaya Kuliah')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class='bx bx-money text-primary me-2'></i>Manajemen Biaya Kuliah
                </h4>
                <p class="text-muted mb-0">Kelola biaya kuliah per program studi yang tampil di landing page</p>
            </div>
        </div>

        {{-- Alert --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Prodi Cards --}}
        <div class="row g-4">
            @foreach ($prodis as $prodi)
                @php
                    $colorMap = [
                        'd3' => ['bg' => 'bg-label-info', 'border' => 'border-info', 'icon' => 'bx-plus-medical', 'gradient' => 'linear-gradient(135deg, #06b6d4, #0ea5e9)'],
                        'farmasi' => ['bg' => 'bg-label-primary', 'border' => 'border-primary', 'icon' => 'bx-capsule', 'gradient' => 'linear-gradient(135deg, #8b5cf6, #a78bfa)'],
                        'karyawan' => ['bg' => 'bg-label-warning', 'border' => 'border-warning', 'icon' => 'bx-briefcase-alt', 'gradient' => 'linear-gradient(135deg, #f59e0b, #f97316)'],
                        'gizi' => ['bg' => 'bg-label-success', 'border' => 'border-success', 'icon' => 'bx-leaf', 'gradient' => 'linear-gradient(135deg, #10b981, #34d399)'],
                    ];
                    $color = $colorMap[$prodi->prodi_key] ?? ['bg' => 'bg-label-secondary', 'border' => 'border-secondary', 'icon' => 'bx-book', 'gradient' => '#6c757d'];
                    $preview = $previewData[$prodi->prodi_key] ?? [];
                    $total = array_sum($preview);
                @endphp
                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 border-top {{ $color['border'] }}" style="border-top-width: 3px !important;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar me-3" style="width:48px; height:48px;">
                                    <span class="avatar-initial rounded {{ $color['bg'] }}"
                                        style="width:48px; height:48px; display:flex; align-items:center; justify-content:center;">
                                        <i class="bx {{ $color['icon'] }} fs-4"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $prodi->prodi_nama }}</h6>
                                    <small class="text-muted">{{ $prodi->jumlah_semester }} Semester</small>
                                    <span class="badge d-block mt-1 {{ $prodi->is_visible ? 'bg-label-success' : 'bg-label-secondary' }}">
                                        <i class="bx {{ $prodi->is_visible ? 'bx-show' : 'bx-hide' }} me-1"></i>{{ $prodi->is_visible ? 'Tampil' : 'Disembunyikan' }}
                                    </span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted d-block mb-2">Biaya Gelombang I:</small>
                                @foreach ($preview as $idx => $biaya)
                                    <div class="d-flex justify-content-between py-1 {{ !$loop->last ? 'border-bottom' : '' }}"
                                        style="font-size: 12px;">
                                        <span class="text-muted">{{ $idx === 0 ? 'Biaya Awal' : 'Semester ' . ($idx + 1) }}</span>
                                        <span class="fw-semibold">Rp {{ number_format($biaya, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="p-2 rounded mb-3" style="background: rgba(99,102,241,0.05);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="fw-semibold text-muted">Estimasi </small>
                                    <span class="fw-bold" style="color: #6366f1;">Rp
                                        {{ number_format($total, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.biaya-kuliah.edit', $prodi->prodi_key) }}" class="btn btn-sm btn-primary">
                                    <i class="bx bx-edit me-1"></i> Edit Biaya
                                </a>
                                <form action="{{ route('admin.biaya-kuliah.visibility', $prodi->prodi_key) }}" method="POST">
                                    @csrf @method('PUT')
                                    <button type="submit" class="btn btn-sm w-100 {{ $prodi->is_visible ? 'btn-outline-danger' : 'btn-outline-success' }}" onclick="return confirm('{{ $prodi->is_visible ? 'Sembunyikan program studi ini dari halaman utama?' : 'Tampilkan program studi ini di halaman utama?' }}')">
                                        <i class="bx {{ $prodi->is_visible ? 'bx-hide' : 'bx-show' }} me-1"></i>{{ $prodi->is_visible ? 'Sembunyikan' : 'Tampilkan' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Info --}}
        <div class="alert alert-info mt-4" role="alert">
            <i class="bx bx-info-circle me-1"></i>
            <strong>Info:</strong> Perubahan biaya akan langsung terlihat di halaman utama (landing page) setelah disimpan.
        </div>
    </div>
@endsection
