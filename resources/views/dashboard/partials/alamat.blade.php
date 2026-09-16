<div x-data="alamatSelector()" x-init="initData()" class="row g-3">

    <!-- Provinsi -->
    <div class="col-md-6">
        <label for="provinsi_id" class="form-label required">Provinsi</label>
        <select x-model="provinsi_id" @change="fetchKabupaten" name="provinsi_id" id="provinsi_id" class="form-select"
            {{-- :disabled="loading.provinsi" --}} required>
            <option value="">Pilih Provinsi</option>

            <template x-if="loading.provinsi">
                <option disabled>Memuat data provinsi...</option>
            </template>

            @foreach ($provinsi as $prov)
            <option value="{{ $prov->id_prov }}">{{ $prov->nama }}</option>
            @endforeach
        </select>
    </div>

    <!-- Kabupaten -->
    <div class="col-md-6">
        <label for="kabupaten_id" class="form-label required">Kabupaten/Kota</label>
        <select x-model="kabupaten_id" @change="fetchKecamatan" name="kabupaten_id" id="kabupaten_id"
            class="form-select" :disabled="!provinsi_id || loading.kabupaten" required>
            <option value="">Pilih Kabupaten</option>

            <template x-if="loading.kabupaten">
                <option disabled>Memuat data kabupaten...</option>
            </template>

            <template x-for="kab in kabupaten" :key="kab.id_kab">
                <option :value="kab.id_kab" x-text="kab.nama_kab"></option>
            </template>
        </select>
    </div>

    <!-- Kecamatan -->
    <div class="col-md-6">
        <label for="kecamatan_id" class="form-label required">Kecamatan</label>
        <select x-model="kecamatan_id" @change="fetchKelurahan" name="kecamatan_id" id="kecamatan_id"
            class="form-select" :disabled="!kabupaten_id || loading.kecamatan" required>
            <option value="">Pilih Kecamatan</option>

            <template x-if="loading.kecamatan">
                <option disabled>Memuat data kecamatan...</option>
            </template>

            <template x-for="kec in kecamatan" :key="kec.id_kec">
                <option :value="kec.id_kec" x-text="kec.nama_kec"></option>
            </template>
        </select>
    </div>

    <!-- Kelurahan -->
    <div class="col-md-6">
        <label for="kelurahan_id" class="form-label required">Kelurahan/Desa</label>
        <select x-model="kelurahan_id" name="kelurahan_id" id="kelurahan_id" class="form-select"
            :disabled="!kecamatan_id || loading.kelurahan" required>
            <option value="">Pilih Kelurahan</option>

            <template x-if="loading.kelurahan">
                <option disabled>Memuat data kelurahan...</option>
            </template>

            <template x-for="kel in kelurahan" :key="kel.id_kel">
                <option :value="kel.id_kel" x-text="kel.nama_kel"></option>
            </template>
        </select>
    </div>

    <!-- Alamat -->
    <div class="col-12">
        <label for="address" class="form-label required">Alamat Lengkap</label>
        <textarea name="address" id="address" class="form-control" rows="3"
            placeholder="Masukkan alamat lengkap tempat tinggal"
            required>{{ old('address', auth()->user()->address) }}</textarea>
    </div>

</div>
