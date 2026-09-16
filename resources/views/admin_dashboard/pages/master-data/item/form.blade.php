@extends('dashboard.layout.master')
@section('title', $title)

@section('content')
    <!-- Form Barang -->
    <div class="card">
        <div class="d-flex align-items-center justify-content-between pe-4">
            <h5 class="card-header mb-0">{{ $title }}</h5>
        </div>
        <div class="card-body pt-0">
            <form action="{{ $action_url }}" method="POST" class="row">
                @method($method)
                @csrf
                <div class="col-12 mb-3">
                    <label for="code" class="form-label">Kode/SKU</label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror" name="code" id="code" value="{{ old('code', $data?->code) }}" placeholder="Masukkan Kode/SKU Barang" @disabled($method == 'PUT')>
                    <i class="d-block">
                        <small>*Kosongkan untuk generate kode otomatis</small>
                    </i>
                    @error('code')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label required">Nama Barang</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name" value="{{ old('name', $data?->name) }}" placeholder="Masukkan Nama Barang" required>
                    @error('name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="item_category_id" class="form-label required">Kategori</label>
                    <select name="item_category_id" id="item_category_id" class="form-select select2 w-100" required>
                        <option value="">Pilih Kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->uuid }}" @selected(old('item_category_id', $data?->item_category_id) == $category->uuid)>{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                    @error('item_category_id')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="col-lg-3 col-sm-6 mb-3">
                    <label for="stock" class="form-label required">Stok Barang</label>
                    <input type="number" class="form-control @error('stock') is-invalid @enderror" name="stock" id="stock" value="{{ old('stock', $data?->stock) ?? 0 }}" placeholder="0" required>
                    @error('stock')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="col-lg-3 col-sm-6 mb-3">
                    <label for="unit" class="form-label required">Satuan</label>
                    <input type="text" class="form-control @error('unit') is-invalid @enderror" name="unit" id="unit" value="{{ old('unit', $data?->unit) }}" placeholder="Contoh: Pcs, Kg, dan lainnya" required>
                    @error('unit')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="col-lg-3 col-sm-6 mb-3">
                    <label for="hpp" class="form-label required">Harga Beli (HPP)</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control @error('hpp') is-invalid @enderror" name="hpp" id="hpp" value="{{ old('hpp', $data?->hpp) ?? 0 }}" placeholder="0" required>
                    </div>
                    @error('hpp')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="col-lg-3 col-sm-6 mb-3">
                    <label for="selling_price" class="form-label required">Harga Jual</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control @error('selling_price') is-invalid @enderror" name="selling_price" id="selling_price" value="{{ old('selling_price', $data?->selling_price) ?? 0 }}" placeholder="0" required>
                    </div>
                    @error('selling_price')
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
    <!-- End Form Barang -->
@endsection