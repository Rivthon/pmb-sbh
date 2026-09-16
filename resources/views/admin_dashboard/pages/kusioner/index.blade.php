@extends('admin_dashboard.layout.master')
@section('title', 'Jurusan')

@section('content')
<!-- Tabel Jurusan -->
<div class="card">
    <div class="d-flex align-items-center justify-content-between pe-4  ">
        <h5 class="card-header mb-0">Kusioner</h5>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.kuesioner.create') }}" class="btn btn-icon btn-primary" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Tambah">
                <i class="bx bx-plus-circle"></i>
            </a>
        </div>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <caption class="ms-4">
                List Kusioner
            </caption>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Deksripsi</th>
                    <th class="text-center">Opsi</th>

                </tr>
            </thead>
            <tbody>
                @foreach($kuesioners as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td> <!-- Menampilkan nomor urut -->
                    <td>{{ $item->nama_kusioner }}</td>
                    <td>
                         <div class="d-flex flex-wrap align-items-center justify-content-center gap-2">
                            <a href="{{ route('admin.kuesioner.show', $item->id) }}" class="btn btn-info btn-icon btn-sm" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Ubah">
                                <i class="bx bx-edit-alt"></i>
                            </a>
                            <a href="{{ route('admin.kuesioner.destroy', $item->id) }}" class="btn btn-danger btn-icon btn-sm delete-confirm" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Hapus">
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
<!-- End Tabel Kusioner -->
@endsection
