@extends('admin_dashboard.layout.master')
@section('title', 'Penghasilan Orang Tua')

@section('content')
<!-- Tabel Penghasilan Orang Tua -->
<div class="card">
    <div class="d-flex align-items-center justify-content-between pe-4">
        <h5 class="card-header mb-0">Penghasilan Orang Tua</h5>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.penghasilan-orang-tua.create') }}" class="btn btn-icon btn-primary" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Tambah">
                <i class="bx bx-plus-circle"></i>
            </a>
        </div>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <caption class="ms-4">
                List Penghasilan Orang Tua
            </caption>
            <thead>
                <tr>
                    <th>Jumlah Penghasilan</th>
                    <th class="text-center">Opsi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($penghasilanOrtu as $item)
                <tr>
                    <td>{{ $item->nama_peng }}</td>
                    <td>
                        <div class="d-flex flex-wrap align-items-center justify-content-center gap-2">
                            <a href="{{ route('admin.penghasilan-orang-tua.show', $item->id) }}" class="btn btn-info btn-icon btn-sm" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Ubah">
                                <i class="bx bx-edit-alt"></i>
                            </a>
                            <form action="{{ route('admin.penghasilan-orang-tua.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Anda yakin ingin menghapus penghasilan ini?');">
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
<!-- End Tabel Penghasilan Orang Tua -->
@endsection
