@extends('dashboard.layout.master')
@section('title', 'Laporan Barang Masuk')

@section('content')

<!-- Tabel Laporan Barang Masuk -->
<div class="card">
    <div class="d-flex align-items-center justify-content-between pe-4  ">
        <h5 class="card-header mb-0">Laporan Barang Masuk</h5>
        
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('dashboard.report.incoming.filter', [
                'start_date' => request()->query('start_date'),
                'end_date' => request()->query('end_date'),
            ]) }}" data-type="modal" data-size-modal="sm" data-modal-title="Filter Laporan Barang Masuk" class="btn btn-icon btn-info" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Filter">
                <i class="bx bx-search-alt"></i>
            </a>
            <a href="{{ route('dashboard.report.incoming.export.pdf', [
                'start_date' => request()->query('start_date'),
                'end_date' => request()->query('end_date'),
            ]) }}" class="btn btn-icon btn-danger" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Cetak PDF">
                <i class="bx bxs-file-pdf"></i>
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
                    <th>Kode Barang Masuk</th>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Stok Awal</th>
                    <th>Stok Akhir</th>
                    <th>Tanggal</th>
                    <th>Dibuat Oleh</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                <tr>
                    <td>{{ $item->incomingGoods->code }}</td>
                    <td>{{ $item->item->name }}</td>
                    <td>{{ "{$item->total_stock} {$item->item->unit}" }}</td>
                    <td>{{ "{$item->initial_stock} {$item->item->unit}" }}</td>
                    <td>{{ "{$item->current_stock} {$item->item->unit}" }}</td>
                    <td>{{ $item->incomingGoods->incoming_date?->translatedFormat('d F Y') }}</td>
                    <td>{{ $item->incomingGoods->user?->name }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<!-- End Tabel Laporan Barang Masuk -->
@endsection