<!-- Pekerjaan Ayah -->
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="pek_ayah_id" class="form-label">Pekerjaan Ayah</label>
        <select name="pek_ayah_id" id="pek_ayah_id" class="form-select @error('pek_ayah_id') is-invalid @enderror">
            <option value="">Pilih Pekerjaan Ayah</option>
            @foreach($pekerjaanAyah as $item)
                <option value="{{ $item->id }}" {{ old('pek_ayah_id', auth()->user()->pek_ayah_id) == $item->id ? 'selected' : '' }}>
                    {{ $item->nama_pek_ayah }}
                </option>
            @endforeach
        </select>
        @error('pek_ayah_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Pekerjaan Ibu -->
    <div class="col-md-6 mb-3">
        <label for="pek_ibu_id" class="form-label">Pekerjaan Ibu</label>
        <select name="pek_ibu_id" id="pek_ibu_id" class="form-select @error('pek_ibu_id') is-invalid @enderror">
            <option value="">Pilih Pekerjaan Ibu</option>
            @foreach($pekerjaanIbu as $item)
                <option value="{{ $item->id }}" {{ old('pek_ibu_id', auth()->user()->pek_ibu_id) == $item->id ? 'selected' : '' }}>
                    {{ $item->nama_pek_ibu }}
                </option>
            @endforeach
        </select>
        @error('pek_ibu_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <!-- Penghasilan Orang Tua -->
    <div class="col-md-6 mb-3">
        <label for="penghasilan_id" class="form-label">Penghasilan Orang Tua</label>
        <select class="form-select @error('penghasilan_id') is-invalid @enderror" name="penghasilan_id" id="penghasilan_id">
            <option value="">Pilih Penghasilan Orang Tua</option>
            @foreach($penghasilan as $peng)
                <option value="{{ $peng->id }}" {{ old('penghasilan_id', auth()->user()->penghasilan_id) == $peng->id ? 'selected' : '' }}>
                    {{ $peng->nama_peng }}
                </option>
            @endforeach
        </select>
        @error('penghasilan_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Nomor Telepon Orang Tua -->
    <div class="col-md-6 mb-3">
        <label for="no_telp_ortu" class="form-label">Nomor Telepon Orang Tua</label>
        <input type="text" class="form-control @error('no_telp_ortu') is-invalid @enderror" name="no_telp_ortu" id="no_telp_ortu" value="{{ old('no_telp_ortu', auth()->user()->no_telp_ortu) }}" placeholder="Masukkan Nomor Telepon Orang Tua">
        @error('no_telp_ortu')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <!-- Nama Ayah -->
    <div class="col-md-6 mb-3">
        <label for="nama_ayah" class="form-label">Nama Ayah</label>
        <input type="text" class="form-control @error('nama_ayah') is-invalid @enderror" name="nama_ayah" id="nama_ayah" value="{{ old('nama_ayah', auth()->user()->nama_ayah) }}" placeholder="Masukkan Nama Ayah">
        @error('nama_ayah')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Nama Ibu -->
    <div class="col-md-6 mb-3">
        <label for="nama_ibu" class="form-label">Nama Ibu</label>
        <input type="text" class="form-control @error('nama_ibu') is-invalid @enderror" name="nama_ibu" id="nama_ibu" value="{{ old('nama_ibu', auth()->user()->nama_ibu) }}" placeholder="Masukkan Nama Ibu">
        @error('nama_ibu')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <!-- Nama Wali -->
    <div class="col-md-6 mb-3">
        <label for="nama_wali" class="form-label">Nama Wali</label>
        <input type="text" class="form-control @error('nama_wali') is-invalid @enderror" name="nama_wali" id="nama_wali" value="{{ old('nama_wali', auth()->user()->nama_wali) }}" placeholder="Masukkan Nama Wali (jika ada)">
        @error('nama_wali')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
