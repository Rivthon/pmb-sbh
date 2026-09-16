@extends('dashboard.layout.master')
@section('title', 'Pelanggan')

@section('content')
<!-- Tabel Pelanggan -->

<div class="card">
    <div class="d-flex align-items-center justify-content-between pe-4  ">
        <h5 class="card-header mb-0">Pelanggan</h5>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('dashboard.customer.export.excel') }}" class="btn btn-icon btn-success" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Export Excel">
                <i class="bx bx-export"></i>
            </a>
            <a href="{{ route('dashboard.customer.create') }}" class="btn btn-icon btn-primary" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Tambah">
                <i class="bx bx-plus-circle"></i>
            </a>
        </div>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <caption class="ms-4">
                List Pelanggan
            </caption>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Pelanggan</th>
                    <th>Nomor HP</th>
                    <th>Dibuat Oleh</th>
                    <th>Tanggal Dibuat</th>
                    <th class="text-center">Opsi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                <tr>
                    <td>{{ $item->code }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->phone }}</td>
                    <td>{{ $item->user?->name }}</td>
                    <td>{{ $item->created_at?->translatedFormat('d F Y H:i') }}</td>
                    <td>
                        <div class="d-flex flex-wrap align-items-center justify-content-center gap-2">
                            <a href="{{ route('dashboard.customer.edit', $item->uuid) }}" class="btn btn-info btn-icon btn-sm" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Ubah">
                                <i class="bx bx-edit-alt"></i>
                            </a>
                            <a href="{{ route('dashboard.customer.delete', $item->uuid) }}" class="btn btn-danger btn-icon btn-sm delete-confirm" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Hapus">
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
<!-- End Tabel Pelanggan -->
@endsection