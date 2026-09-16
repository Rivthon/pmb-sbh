@extends('dashboard.layout.master')
@section('title', $title)

@section('content')
    <!-- Form Pengguna -->
    <div class="card">
        <div class="d-flex align-items-center justify-content-between pe-4">
            <h5 class="card-header mb-0">{{ $title }}</h5>
        </div>
        <div class="card-body pt-0">
            <form action="{{ $action_url }}" method="POST" class="row">
                @method($method)
                @csrf
                <div class="col-12 mb-3">
                    <label for="name" class="form-label required">Nama Lengkap</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name" value="{{ old('name', $data?->name) }}" placeholder="Masukkan Nama Lengkap" required>
                    @error('name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label required">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" value="{{ old('email', $data?->email) }}" placeholder="Masukkan Email" required>
                    @error('email')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label required">No. HP</label>
                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" name="phone" id="phone" value="{{ old('phone', $data?->phone) }}" placeholder="Masukkan Nomor HP" required>
                    @error('phone')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label @if($method == 'POST') required @endif">Password</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" id="password" placeholder="Masukkan Password" @if($method == 'POST') required @endif>
                    @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label @if($method == 'POST') required @endif">Konfirmasi Password</label>
                    <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation" id="password_confirmation" placeholder="Konfirmasi Password" @if($method == 'POST') required @endif>
                    @error('password_confirmation')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="col-12 mb-3">
                    <label for="address" class="form-label required">Alamat</label>
                    <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="5" placeholder="Masukkan Alamat">{{ old('address', $data?->address) }}</textarea>
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
    <!-- End Form Pengguna -->
@endsection