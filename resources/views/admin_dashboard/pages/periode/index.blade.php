@extends('admin_dashboard.layout.master')
@section('title', 'Periode PMB')

@section('content')

{{-- Page Hero --}}
@include('admin_dashboard.components.page-hero', [
    'title' => 'Periode Akademik PMB',
    'subtitle' => 'Kelola periode tahun akademik penerimaan mahasiswa baru di lingkungan kampus.',
    'icon' => 'calendar-event',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Data Master'],
        ['label' => 'Periode']
    ],
    'actions' => '
        <a href="' . route('admin.periode.create') . '" class="btn btn-primary btn-action">
            <i class="bx bx-plus-circle"></i> Tambah Periode
        </a>
    '
])

{{-- Table --}}
<div class="row">
    <div class="col-12">
        @component('admin_dashboard.components.admin-card', [
            'title' => 'Daftar Periode Pendaftaran',
            'icon' => 'bx bx-calendar',
            'noPadding' => true
        ])
            @component('admin_dashboard.components.admin-table', [
                'headers' => ['No', 'Tanggal Mulai', 'Deskripsi Periode', 'Status', 'Aksi']
            ])
                @slot('slot')
                    @forelse($periodes as $item)
                    <tr>
                        <td class="fw-medium">{{ $loop->iteration }}</td>
                        <td>
                            <i class="bx bx-calendar-check text-muted me-1"></i>
                            {{ \Carbon\Carbon::parse($item->tgl_mulai)->translatedFormat('l, d F Y') }}
                        </td>
                        <td class="fw-semibold text-dark">{{ $item->deskripsi }}</td>
                        <td>
                            @include('admin_dashboard.components.status-badge', [
                                'status' => $item->status_periode ?? 'nonaktif',
                                'label' => ($item->status_periode == 'aktif') ? 'Sedang Aktif' : 'Non Aktif'
                            ])
                        </td>
                        <td>
                            <div class="table-actions">
                                {{-- Tombol Aktif / Nonaktif --}}
                                @if ($item->status_periode === 'nonaktif')
                                <form action="{{ route('admin.periode.aktifkan', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-outline-success btn-icon-action btn-sm"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Aktifkan Periode">
                                        <i class="bx bx-check-circle fs-5"></i>
                                    </button>
                                </form>
                                @else
                                <form action="{{ route('admin.periode.nonaktifkan', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-outline-warning btn-icon-action btn-sm"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Nonaktifkan Periode">
                                        <i class="bx bx-pause-circle fs-5"></i>
                                    </button>
                                </form>
                                @endif

                                {{-- Tombol Edit --}}
                                <a href="{{ route('admin.periode.edit', $item->id) }}"
                                    class="btn btn-outline-info btn-icon-action btn-sm" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Ubah Data">
                                    <i class="bx bx-edit-alt fs-5"></i>
                                </a>

                                {{-- Tombol Hapus --}}
                                <form action="{{ route('admin.periode.destroy', $item->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-outline-danger btn-icon-action btn-sm"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Data"
                                        onclick="confirmDelete(this.closest('form'), 'Periode {{ addslashes($item->deskripsi) }}')">
                                        <i class="bx bx-trash fs-5"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            @include('admin_dashboard.components.empty-state', [
                                'title' => 'Belum Ada Periode',
                                'description' => 'Silakan tambahkan periode pendaftaran baru terlebih dahulu.',
                                'icon' => 'bx-calendar'
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
