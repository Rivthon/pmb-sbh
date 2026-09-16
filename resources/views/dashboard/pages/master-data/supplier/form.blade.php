@extends('dashboard.layout.master')
@section('title', $title)

@section('content')
    <!-- Form Supplier -->
    <div class="card">
        <div class="d-flex align-items-center justify-content-between pe-4">
            <h5 class="card-header mb-0">{{ $title }}</h5>
        </div>
        <div class="card-body pt-0">
            <form action="{{ $action_url }}" method="POST" class="row">
                @method($method)
                @csrf
                <div class="col-12 mb-3">
                    <label for="name" class="form-label required">Nama Supplier</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name" value="{{ old('name', $data?->name) }}" placeholder="Masukkan Nama Supplier" required>
                    @error('name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label required">Nomor Telepon</label>
                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" name="phone" id="phone" value="{{ old('phone', $data?->phone) }}" placeholder="Masukkan Nomor Telepon" required>
                    @error('phone')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" value="{{ old('email', $data?->email) }}" placeholder="Masukkan Email">
                    @error('email')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="col-12 mb-3">
                    <label for="address" class="form-label required">Alamat</label>
                    <textarea name="address" id="address" rows="4" class="form-control" placeholder="Masukkan Alamat Supplier" required>{{ old('address', $data?->address) }}</textarea>
                    @error('address')
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
    <!-- End Form Supplier -->
@endsection