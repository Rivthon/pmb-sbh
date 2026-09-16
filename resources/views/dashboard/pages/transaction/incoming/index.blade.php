@extends('dashboard.layout.master')
@section('title', 'Barang Masuk')

@section('content')
<!-- Tabel Barang Masuk -->

<div class="card">
    <div class="d-flex align-items-center flex-wrap justify-content-between pe-4  ">
        <h5 class="card-header mb-0">Barang Masuk</h5>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="{{ route('dashboard.transaction.incoming.filter', [
                'start_date' => request()->query('start_date'),
                'end_date' => request()->query('end_date'),
            ]) }}" data-type="modal" data-size-modal="sm" data-modal-title="Filter Barang Masuk" class="btn btn-icon btn-info" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Filter">
                <i class="bx bx-search-alt"></i>
            </a>
            <a href="{{ route('dashboard.transaction.incoming.export.excel', [
                'start_date' => request()->query('start_date'),
                'end_date' => request()->query('end_date'),
            ]) }}" class="btn btn-icon btn-success" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Export Excel">
                <i class="bx bx-export"></i>
            </a>
            <a href="{{ route('dashboard.transaction.incoming.create') }}" class="btn btn-icon btn-primary" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Tambah">
                <i class="bx bx-plus-circle"></i>
            </a>
        </div>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <caption class="ms-4">
                List Barang Masuk
            </caption>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Supplier</th>
                    <th>Barang</th>
                    <th>Jumlah Stok</th>
                    <th>Tanggal</th>
                    <th class="text-center">Opsi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                <tr>
                    <td>{{ $item->code }}</td>
                    <td>{{ $item->supplier?->name ?? '-' }}</td>
                    <td>
                        <ul class="ps-0 mb-0">
                            @foreach ($item->items as $i)
                                <li>{{ "{$i->item->name} ({$i->item->code})" }}</li>
                            @endforeach
                        </ul>
                    </td>
                    <td>
                        <ul class="ps-0 mb-0">
                            @foreach ($item->items as $i)
                                <li>{{ "{$i->total_stock} {$i->item?->unit}" }}</li>
                            @endforeach
                        </ul>
                    </td>
                    <td>{{ $item->incoming_date?->translatedFormat('d F Y') }}</td>
                    <td>
                        <div class="d-flex flex-wrap align-items-center justify-content-center gap-2">
                            <a href="{{ route('dashboard.transaction.incoming.delete', $item->uuid) }}" class="btn btn-danger btn-icon btn-sm delete-confirm" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Hapus">
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
<!-- End Tabel Barang Masuk -->
@endsection