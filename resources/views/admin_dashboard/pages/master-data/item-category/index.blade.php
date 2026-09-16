@extends('dashboard.layout.master')
@section('title', 'Kategori Barang')

@section('content')
<!-- Tabel Kategori Barang -->

<div class="card">
    <div class="d-flex align-items-center justify-content-between pe-4  ">
        <h5 class="card-header mb-0">Kategori Barang</h5>
        <a href="{{ route('dashboard.items.category.create') }}" class="btn btn-icon btn-primary" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Tambah">
            <i class="bx bx-plus-circle"></i>
        </a>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <caption class="ms-4">
                List Kategori Barang
            </caption>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Kategori</th>
                    @role('admin')
                        <th>Dibuat Oleh</th>
                    @endrole
                    <th>Tanggal Dibuat</th>
                    <th class="text-center">Opsi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->category_name }}</td>
                    @role('admin')
                        <td>{{ $item->user?->name }}</td>
                    @endrole
                    <td>{{ $item->created_at?->translatedFormat('d F Y H:i') }}</td>
                    <td>
                        <div class="d-flex flex-wrap align-items-center justify-content-center gap-2">
                            <a href="{{ route('dashboard.items.category.edit', $item->uuid) }}" class="btn btn-info btn-icon btn-sm" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Ubah">
                                <i class="bx bx-edit-alt"></i>
                            </a>
                            <a href="{{ route('dashboard.items.category.delete', $item->uuid) }}" class="btn btn-danger btn-icon btn-sm delete-confirm" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Hapus">
                                <i class="bx bx-trash-alt"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<!-- End Tabel Kategori Barang -->
@endsection