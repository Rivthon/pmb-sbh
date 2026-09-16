@extends('dashboard.layout.master')
@section('title', $title)

@section('content')
    <!-- Form Kategori Barang -->
    <div class="card">
        <div class="d-flex align-items-center justify-content-between pe-4">
            <h5 class="card-header mb-0">{{ $title }}</h5>
        </div>
        <div class="card-body pt-0">
            <form action="{{ $action_url }}" method="POST" class="row">
                @method($method)
                @csrf
                <div class="col-12 mb-3">
                    <label for="category_name" class="form-label required">Nama Kategori</label>
                    <input type="text" class="form-control @error('category_name') is-invalid @enderror" name="category_name" id="category_name" value="{{ old('category_name', $data?->category_name) }}" placeholder="Masukkan Nama Kategori" required>
                    @error('category_name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center gap-2">
                        <i class="bx bx-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- End Form Kategori Barang -->
@endsection