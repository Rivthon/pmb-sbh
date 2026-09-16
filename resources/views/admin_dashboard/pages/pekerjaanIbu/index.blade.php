@extends('admin_dashboard.layout.master')
@section('title', 'Pekerjaan Ibu')

@section('content')
<!-- Tabel Pekerjaan Ibu -->
<div class="card">
    <div class="d-flex align-items-center justify-content-between pe-4">
        <h5 class="card-header mb-0">Pekerjaan Ibu</h5>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.pekerjaan-ibu.create') }}" class="btn btn-icon btn-primary" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Tambah">
                <i class="bx bx-plus-circle"></i>
            </a>
        </div>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <caption class="ms-4">
                List Pekerjaan Ibu
            </caption>
            <thead>
                <tr>
                    <th>Nama Pekerjaan</th>
                    <th class="text-center">Opsi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pekerjaanIbu as $item)
                <tr>
                    <td>{{ $item->nama_pek_ibu }}</td>
                    <td>
                        <div class="d-flex flex-wrap align-items-center justify-content-center gap-2">
                            <a href="{{ route('admin.pekerjaan-ibu.show', $item->id) }}" class="btn btn-info btn-icon btn-sm" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Ubah">
                                <i class="bx bx-edit-alt"></i>
                            </a>
                            <form action="{{ route('admin.pekerjaan-ibu.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Anda yakin ingin menghapus pekerjaan ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-icon btn-sm" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Hapus">
                                    <i class="bx bx-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<!-- End Tabel Pekerjaan Ibu -->
@endsection
