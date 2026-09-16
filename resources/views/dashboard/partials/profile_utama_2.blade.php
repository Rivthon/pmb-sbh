<!-- Agama dan Jurusan -->
<div class="row mb-3">
    <div class="col-md-6">
        <label for="agama_id" class="form-label required">Agama</label>
        <select name="agama_id" id="agama_id" class="form-control @error('agama_id') is-invalid @enderror" required>
            <option value="">Pilih Agama</option>
            @foreach($agama as $item)
            <option value="{{ $item->id }}" {{ old('agama_id', auth()->user()->agama_id) == $item->id ? 'selected' : ''
                }}>{{ $item->nama_agama }}</option>
            @endforeach
        </select>
        @error('agama_id')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label for="jurusan_id" class="form-label required">Jurusan</label>
        <select name="jurusan_id" id="jurusan_id" class="form-control @error('jurusan_id') is-invalid @enderror"
            required>
            <option value="">Pilih Jurusan</option>
            @foreach($jurusan as $jurusanItem)
            <option value="{{ $jurusanItem->id }}" {{ old('jurusan_id', auth()->user()->jurusan_id) == $jurusanItem->id
                ? 'selected' : '' }}>{{ $jurusanItem->nama_jurusan }}</option>
            @endforeach
        </select>
        @error('jurusan_id')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<!-- Tanggal Lahir dan No. HP -->
<div class="row mb-3">
    <div class="col-md-6">
        <label for="tgl_lahir" class="form-label required">Tanggal Lahir</label>
        <input type="date" class="form-control @error('tgl_lahir') is-invalid @enderror" name="tgl_lahir" id="tgl_lahir"
            value="{{ old('tgl_lahir', auth()->user()->tgl_lahir) }}" required>
        @error('tgl_lahir')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label for="phone" class="form-label required">No. HP</label>
        <input type="tel" class="form-control @error('phone') is-invalid @enderror" name="phone" id="phone"
            value="{{ old('phone', auth()->user()->phone) }}" placeholder="Masukkan Nomor HP" required>
        @error('phone')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<!-- Password dan Konfirmasi Password -->
<div class="row mb-3">
    <div class="col-md-6">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control @error('password') is-invalid @enderror" name="password"
            id="password" placeholder="Masukkan Password">
        <small class="d-block text-muted">*Kosongkan jika tidak ingin mengganti password</small>
        @error('password')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
            name="password_confirmation" id="password_confirmation" placeholder="Konfirmasi Password">
        <small class="d-block text-muted">*Kosongkan jika tidak ingin mengganti password</small>
        @error('password_confirmation')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<!-- Asal Sekolah -->
<div class="row mb-3">
    <div class="col-md-12">
        <label for="asal_sekolah" class="form-label">Asal Sekolah</label>
        <input type="text" class="form-control @error('asal_sekolah') is-invalid @enderror" name="asal_sekolah"
            id="asal_sekolah" value="{{ old('asal_sekolah', auth()->user()->asal_sekolah) }}"
            placeholder="Masukkan Asal Sekolah">
        @error('asal_sekolah')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>