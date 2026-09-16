@extends('admin_dashboard.layout.master')
@section('title', 'Jurusan PMB')

@section('content')

{{-- Page Hero --}}
@include('admin_dashboard.components.page-hero', [
    'title' => 'Program Studi / Jurusan',
    'subtitle' => 'Kelola program studi, kode prodi, jenjang akademik, serta konfigurasi kuota mahasiswa baru.',
    'icon' => 'bookmark',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Data Master'],
        ['label' => 'Jurusan']
    ],
    'actions' => '
        <a href="' . route('admin.jurusan.create') . '" class="btn btn-primary btn-action">
            <i class="bx bx-plus-circle"></i> Tambah Program Studi
        </a>
    '
])

{{-- Table --}}
<div class="row">
    <div class="col-12">
        @component('admin_dashboard.components.admin-card', [
            'title' => 'Daftar Program Studi',
            'icon' => 'bx bx-bookmark',
            'noPadding' => true
        ])
            @component('admin_dashboard.components.admin-table', [
                'headers' => ['Kode Prodi', 'Nama Program Studi', 'Jenjang / Deskripsi', 'Aksi']
            ])
                @slot('slot')
                    @forelse($jurusans as $item)
                    <tr>
                        <td class="fw-semibold text-primary">{{ $item->kd_jurusan }}</td>
                        <td class="fw-bold text-dark">{{ $item->nama_jurusan }}</td>
                        <td class="fw-medium text-muted">{{ $item->deskripsi }}</td>
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('admin.jurusan.edit', $item->id) }}" class="btn btn-outline-info btn-icon-action btn-sm" data-bs-toggle="tooltip" title="Ubah Data">
                                    <i class="bx bx-edit-alt fs-5"></i>
                                </a>
                                <form action="{{ route('admin.jurusan.destroy', $item->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-outline-danger btn-icon-action btn-sm" data-bs-toggle="tooltip" title="Hapus Data"
                                        onclick="confirmDelete(this.closest('form'), 'Jurusan {{ addslashes($item->nama_jurusan) }}')">
                                        <i class="bx bx-trash fs-5"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            @include('admin_dashboard.components.empty-state', [
                                'title' => 'Belum Ada Jurusan',
                                'description' => 'Silakan tambahkan program studi baru terlebih dahulu.',
                                'icon' => 'bx-bookmark'
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
