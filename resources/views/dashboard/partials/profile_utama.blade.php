<!-- Nama Lengkap -->
<div class="row mb-3">
    <div class="col-md-12">
        <label for="name" class="form-label required">Nama Lengkap</label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name"
            value="{{ old('name', auth()->user()->name) }}" placeholder="Masukkan nama lengkap" required>
        @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<!-- Email dan NISN -->
<div class="row mb-3">
    <div class="col-md-6">
        <label for="email" class="form-label required">Email</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email"
            value="{{ old('email', auth()->user()->email) }}" placeholder="Masukkan Email" required>
        @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="nisn" class="form-label required">NISN</label>
        <input type="number" class="form-control @error('nisn') is-invalid @enderror" name="nisn" id="nisn"
            value="{{ old('nisn', auth()->user()->nisn) }}" placeholder="Masukkan NISN" required maxlength="10"
            oninput="limitNISNLength(this)">
        <small class="text-muted d-block mt-1">
            NISN terdiri dari <span id="nisn-count">0</span>/10 karakter angka
        </small>
        @error('nisn')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<!-- NIK dan Tempat Lahir -->
<div class="row mb-3">
    <div class="col-md-6">
        <label for="nik" class="form-label required">NIK</label>
        <input type="number" class="form-control @error('nik') is-invalid @enderror" name="nik" id="nik"
            value="{{ old('nik', auth()->user()->nik) }}" placeholder="Masukkan NIK" required maxlength="16"
            oninput="limitNIKLength(this)">
        <small class="text-muted d-block mt-1">
            NIK terdiri dari <span id="nik-count">0</span>/16 karakter angka
        </small>
        @error('nik')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="tempat_lahir" class="form-label required">Tempat Lahir</label>
        <input type="text" class="form-control @error('tempat_lahir') is-invalid @enderror" name="tempat_lahir"
            id="tempat_lahir" value="{{ old('tempat_lahir', auth()->user()->tempat_lahir) }}"
            placeholder="Masukkan Tempat Lahir" required>
        @error('tempat_lahir')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<!-- Jenis Kelamin -->
<div class="row mb-3">
    <div class="col-md-6">
        <label for="jenis_kelamin" class="form-label required">Jenis Kelamin</label>
        <select name="jenis_kelamin" id="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror"
            required>
            <option value="">-- Pilih Jenis Kelamin --</option>
            <option value="L" {{ old('jenis_kelamin', auth()->user()->jenis_kelamin) == 'L' ? 'selected' : '' }}>
                Laki-laki
            </option>
            <option value="P" {{ old('jenis_kelamin', auth()->user()->jenis_kelamin) == 'P' ? 'selected' : '' }}>
                Perempuan
            </option>
        </select>
        @error('jenis_kelamin')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<!-- Foto Profil -->
<div class="row mb-3">
    <div class="col-md-12">
        <label for="image" class="form-label">Foto Profil</label>
        <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror"
            onchange="previewFile(this, 'avatar-profile')" accept="image/*">
        <small class="d-block text-muted">*Kosongkan jika tidak ingin mengganti foto profil</small>
        @error('image')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<!-- Script Validasi Panjang NISN & NIK -->
<script>
    function limitNISNLength(input) {
        const maxLength = 10;
        const countDisplay = document.getElementById('nisn-count');
        input.value = input.value.replace(/\D/g, '').slice(0, maxLength);
        countDisplay.textContent = input.value.length;
    }

    function limitNIKLength(input) {
        const maxLength = 16;
        const countDisplay = document.getElementById('nik-count');
        input.value = input.value.replace(/\D/g, '').slice(0, maxLength);
        countDisplay.textContent = input.value.length;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const nisnInput = document.getElementById('nisn');
        const nikInput = document.getElementById('nik');
        if (nisnInput) limitNISNLength(nisnInput);
        if (nikInput) limitNIKLength(nikInput);
    });
</script>
