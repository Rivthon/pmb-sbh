@extends('dashboard.layout.master')
@section('title', 'Laporan Stok Barang')

@section('content')

<!-- Tabel Laporan Stok Barang -->
<div class="card">
    <div class="d-flex align-items-center justify-content-between pe-4  ">
        <h5 class="card-header mb-0">Laporan Stok Barang</h5>
        
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('dashboard.report.stock.export.pdf') }}" class="btn btn-icon btn-danger" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Cetak PDF">
                <i class="bx bxs-file-pdf"></i>
            </a>
        </div>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <caption class="ms-4">
                List Stok Barang
            </caption>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th>Jumlah Stok</th>
                    <th>Harga Beli (HPP)</th>
                    <th>Harga Jual</th>

                    @role('admin')
                        <th>Dibuat Oleh</th>
                    @endrole
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                <tr>
                    <td>{{ $item->code }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ "{$item->stock} {$item->unit}" }}</td>
                    <td>Rp {{ number_format($item->hpp) }}</td>
                    <td>Rp {{ number_format($item->selling_price) }}</td>

                    @role('admin')
                        <td>{{ $item->user?->name }}</td>
                    @endrole
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<!-- End Tabel Laporan Stok Barang -->
@endsection