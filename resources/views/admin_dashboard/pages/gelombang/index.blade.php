@extends('admin_dashboard.layout.master')
@section('title', 'Gelombang PMB')

@section('content')

{{-- Page Hero --}}
@include('admin_dashboard.components.page-hero', [
    'title' => 'Gelombang Pendaftaran PMB',
    'subtitle' => 'Kelola tahapan gelombang pendaftaran, durasi waktu mulai/selesai, serta status keaktifan gelombang.',
    'icon' => 'layer',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Data Master'],
        ['label' => 'Gelombang']
    ],
    'actions' => '
        <a href="' . route('admin.gelombang.create') . '" class="btn btn-primary btn-action">
            <i class="bx bx-plus-circle"></i> Tambah Gelombang
        </a>
    '
])

{{-- Filter & Table --}}
<div class="row">
    {{-- Filter Card --}}
    <div class="col-12 mb-4">
        @component('admin_dashboard.components.admin-card', [
            'title' => 'Filter Gelombang',
            'icon' => 'bx bx-filter-alt',
            'noPadding' => false
        ])
            <form action="{{ route('admin.gelombang.index') }}" method="GET" class="row align-items-end g-3">
                <div class="col-md-8 col-lg-6">
                    <label for="periode_id" class="form-label fw-medium text-muted small mb-2">Pilih Tahun Ajaran / Periode</label>
                    <select name="periode_id" id="periode_id" class="form-select form-select-lg">
                        <option value="">Semua Periode / Tahun Ajaran</option>
                        @foreach ($periodes as $periode)
                            <option value="{{ $periode->id }}" {{ request('periode_id') == $periode->id ? 'selected' : '' }}>
                                Tahun Ajaran {{ \Carbon\Carbon::parse($periode->tgl_mulai)->format('Y') }} - {{ $periode->deskripsi }}
                                @if ($periode->status_periode == 'aktif')
                                    (Aktif)
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                        <i class="bx bx-filter-alt"></i> Filter
                    </button>
                    @if(request('periode_id'))
                        <a href="{{ route('admin.gelombang.index') }}" class="btn btn-outline-secondary btn-lg flex-grow-1">
                            Batal
                        </a>
                    @endif
                </div>
            </form>
        @endcomponent
    </div>

    {{-- Table Card --}}
    <div class="col-12">
        @component('admin_dashboard.components.admin-card', [
            'title' => 'Daftar Gelombang PMB',
            'icon' => 'bx bx-layer',
            'noPadding' => true
        ])
            @component('admin_dashboard.components.admin-table', [
                'headers' => ['No', 'Tahun Ajaran / Periode', 'Nama Gelombang', 'Tanggal Mulai', 'Tanggal Selesai', 'Status', 'Aksi']
            ])
                @slot('slot')
                    @forelse ($gelombangs as $gelombang)
                    <tr>
                        <td class="fw-medium">{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($gelombang->periode)
                                    <span class="fw-semibold text-primary">Tahun Ajaran {{ \Carbon\Carbon::parse($gelombang->periode->tgl_mulai)->format('Y') }}</span>
                                    @if ($gelombang->periode->status_periode == 'aktif')
                                        <span class="badge bg-success text-white rounded-pill ms-2 px-2 py-1" style="font-size: 0.7rem; font-weight: 500; letter-spacing: 0.3px;">Periode Aktif</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                            @if($gelombang->periode)
                                <small class="text-muted d-block mt-0.5">{{ $gelombang->periode->deskripsi }}</small>
                            @endif
                        </td>
                        <td class="fw-semibold text-dark">{{ $gelombang->nama_gelombang }}</td>
                        <td>
                            <i class="bx bx-calendar text-muted me-1"></i>
                            {{ \Carbon\Carbon::parse($gelombang->tgl_mulai)->translatedFormat('d M Y') }}
                        </td>
                        <td>
                            <i class="bx bx-calendar-event text-muted me-1"></i>
                            {{ \Carbon\Carbon::parse($gelombang->tgl_selesai)->translatedFormat('d M Y') }}
                        </td>
                        <td>
                            @include('admin_dashboard.components.status-badge', [
                                'status' => $gelombang->status_gelombang ?? 'nonaktif',
                                'label' => ($gelombang->status_gelombang == 'aktif') ? 'Sedang Aktif' : 'Non Aktif'
                            ])
                        </td>
                        <td>
                            <div class="table-actions">
                                @if ($gelombang->status_gelombang == 'nonaktif')
                                <form action="{{ route('admin.gelombang.aktifkan', $gelombang->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-outline-success btn-icon-action btn-sm" data-bs-toggle="tooltip" title="Aktifkan Gelombang">
                                        <i class="bx bx-check-circle fs-5"></i>
                                    </button>
                                </form>
                                @else
                                <form action="{{ route('admin.gelombang.nonaktifkan', $gelombang->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-outline-warning btn-icon-action btn-sm" data-bs-toggle="tooltip" title="Nonaktifkan Gelombang">
                                        <i class="bx bx-pause-circle fs-5"></i>
                                    </button>
                                </form>
                                @endif

                                <a href="{{ route('admin.gelombang.edit', $gelombang->id) }}" class="btn btn-outline-info btn-icon-action btn-sm" data-bs-toggle="tooltip" title="Ubah Data">
                                    <i class="bx bx-edit-alt fs-5"></i>
                                </a>

                                <form action="{{ route('admin.gelombang.destroy', $gelombang->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-outline-danger btn-icon-action btn-sm" data-bs-toggle="tooltip" title="Hapus Data"
                                        onclick="confirmDelete(this.closest('form'), 'Gelombang {{ addslashes($gelombang->nama_gelombang) }}')">
                                        <i class="bx bx-trash fs-5"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            @include('admin_dashboard.components.empty-state', [
                                'title' => 'Belum Ada Gelombang',
                                'description' => 'Silakan tambahkan gelombang pendaftaran baru terlebih dahulu.',
                                'icon' => 'bx-layer'
                            ])
                        </td>
                    </tr>
                    @endforelse
                @endslot
            @endcomponent
        @endcomponent
    </div>
</div>

@include('admin_dashboard.components.confirm-delete-modal')
@endsection
