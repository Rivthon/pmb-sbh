@extends('dashboard.layout.master')
@section('title', 'Layanan Pelanggan')

@section('content')
<!-- Tabel Layanan Pelanggan -->

<div class="card">
    <div class="d-flex align-items-center justify-content-between pe-4  ">
        <h5 class="card-header mb-0">Layanan Pelanggan</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <caption class="ms-4">
                List Pesan dari Pelanggan
            </caption>
            <thead>
                <tr>
                    <th>No</th>
                    <th>IP</th>
                    <th>Nama Lengkap</th>
                    <th>Email</th>
                    <th>Pesan</th>
                    <th>Tanggal Dibuat</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->customer_ip }}</td>
                    <td>{{ $item->customer_name }}</td>
                    <td>{{ $item->customer_email }}</td>
                    <td>{{ $item->message }}</td>
                    <td>{{ $item->created_at?->translatedFormat('d F Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<!-- End Tabel Layanan Pelanggan -->
@endsection