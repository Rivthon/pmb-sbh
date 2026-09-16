@extends('dashboard.layout.master')
@section('title', 'Biodata PMB')

@section('content')
@php
    $user = auth()->user();
    $stepErrors = [
        'akun' => ['name', 'email', 'phone', 'password'],
        'identitas' => ['nik', 'nisn', 'tempat_lahir', 'tgl_lahir', 'jenis_kelamin', 'agama_id'],
        'alamat' => ['provinsi_id', 'kabupaten_id', 'kecamatan_id', 'kelurahan_id', 'address'],
        'akademik' => ['jurusan_id', 'asal_sekolah'],
        'ortu' => ['nama_ayah', 'pek_ayah_id', 'nama_ibu', 'pek_ibu_id', 'penghasilan_id', 'no_telp_ortu', 'nama_wali'],
    ];
    $hasStepError = fn ($fields) => collect($fields)->contains(fn ($field) => $errors->has($field));
@endphp

<div class="student-page-shell">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <h4 class="fw-bold mb-1">Biodata Peserta PMB</h4>
            <p class="text-muted mb-0">Lengkapi data sesuai dokumen resmi. Setiap field memiliki keterangan format agar input tidak keliru.</p>
        </div>
        @include('dashboard.components.student-status-badge', [
            'label' => (int) $user->status_biodata === 1 ? 'Biodata Terisi' : 'Perlu Dilengkapi',
            'tone' => (int) $user->status_biodata === 1 ? 'success' : 'warning',
            'icon' => (int) $user->status_biodata === 1 ? 'bx-check-circle' : 'bx-time-five',
        ])
    </div>

    @if ($errors->any())
        <div class="alert alert-danger d-flex gap-2" role="alert">
            <i class="bx bx-error-circle fs-4"></i>
            <div>
                <strong>Data belum bisa disimpan.</strong>
                <div>Periksa field yang ditandai merah. Pesan validasi sudah ditampilkan di bawah masing-masing field.</div>
            </div>
        </div>
    @endif

    <form id="profileForm" action="{{ route('dashboard.profile.update') }}" method="POST" enctype="multipart/form-data" class="row g-4">
        @csrf

        <div class="col-lg-3">
            <div class="student-wizard-card p-3 sticky-lg-top" style="top: 90px;">
                <div class="nav student-wizard-nav" id="profileWizardTabs" role="tablist">
                    @foreach([
                        'akun' => ['label' => '1. Akun', 'icon' => 'bx-user'],
                        'identitas' => ['label' => '2. Identitas', 'icon' => 'bx-id-card'],
                        'alamat' => ['label' => '3. Alamat', 'icon' => 'bx-map'],
                        'akademik' => ['label' => '4. Akademik', 'icon' => 'bx-bookmark'],
                        'ortu' => ['label' => '5. Orang Tua', 'icon' => 'bx-group'],
                        'preview' => ['label' => '6. Preview', 'icon' => 'bx-check-shield'],
                    ] as $key => $meta)
                        <button class="nav-link {{ $loop->first ? 'active' : '' }} {{ isset($stepErrors[$key]) && $hasStepError($stepErrors[$key]) ? 'border-danger text-danger' : '' }}"
                            id="{{ $key }}-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#tab-{{ $key }}"
                            type="button"
                            role="tab">
                            <i class="bx {{ $meta['icon'] }}"></i>
                            <span>{{ $meta['label'] }}</span>
                            @if(isset($stepErrors[$key]) && $hasStepError($stepErrors[$key]))
                                <i class="bx bx-error-circle ms-auto"></i>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="tab-content p-0">
                <div class="tab-pane fade show active" id="tab-akun" role="tabpanel">
                    <div class="student-wizard-card p-4">
                        <h5 class="mb-3">Informasi Akun</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                @include('dashboard.components.student-form-field', [
                                    'name' => 'name',
                                    'label' => 'Nama Lengkap',
                                    'value' => $user->name,
                                    'required' => true,
                                    'placeholder' => 'Masukkan nama lengkap sesuai ijazah/akta',
                                    'help' => 'Gunakan nama lengkap sesuai dokumen resmi. Maksimal 255 karakter.',
                                ])
                            </div>
                            <div class="col-md-6">
                                @include('dashboard.components.student-form-field', [
                                    'name' => 'email',
                                    'label' => 'Email Aktif',
                                    'type' => 'email',
                                    'value' => $user->email,
                                    'required' => true,
                                    'placeholder' => 'nama@email.com',
                                    'help' => 'Email digunakan untuk login dan informasi PMB.',
                                ])
                            </div>
                            <div class="col-md-6">
                                @include('dashboard.components.student-form-field', [
                                    'name' => 'phone',
                                    'label' => 'Nomor HP/WhatsApp',
                                    'type' => 'tel',
                                    'value' => $user->phone,
                                    'required' => true,
                                    'placeholder' => '081234567890',
                                    'help' => 'Gunakan nomor aktif yang bisa dihubungi panitia. Maksimal 20 karakter.',
                                ])
                            </div>
                            <div class="col-md-6">
                                @include('dashboard.components.student-form-field', [
                                    'name' => 'password',
                                    'label' => 'Password Baru',
                                    'type' => 'password',
                                    'required' => false,
                                    'placeholder' => 'Kosongkan jika tidak ingin mengganti',
                                    'help' => 'Minimal 8 karakter. Kosongkan jika password tidak diubah.',
                                ])
                            </div>
                            <div class="col-md-6">
                                @include('dashboard.components.student-form-field', [
                                    'name' => 'password_confirmation',
                                    'label' => 'Konfirmasi Password',
                                    'type' => 'password',
                                    'required' => false,
                                    'placeholder' => 'Ulangi password baru',
                                    'help' => 'Harus sama dengan password baru.',
                                ])
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-identitas" role="tabpanel">
                    <div class="student-wizard-card p-4">
                        <h5 class="mb-3">Identitas Pribadi</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                @include('dashboard.components.student-form-field', [
                                    'name' => 'nik',
                                    'label' => 'NIK',
                                    'value' => $user->nik,
                                    'required' => true,
                                    'inputmode' => 'numeric',
                                    'maxlength' => 16,
                                    'placeholder' => '16 digit NIK',
                                    'help' => 'Wajib 16 digit angka sesuai KTP/KK.',
                                ])
                            </div>
                            <div class="col-md-6">
                                @include('dashboard.components.student-form-field', [
                                    'name' => 'nisn',
                                    'label' => 'NISN',
                                    'value' => $user->nisn,
                                    'required' => true,
                                    'inputmode' => 'numeric',
                                    'maxlength' => 10,
                                    'placeholder' => '10 digit NISN',
                                    'help' => 'Wajib 10 digit angka sesuai data sekolah.',
                                ])
                            </div>
                            <div class="col-md-6">
                                @include('dashboard.components.student-form-field', [
                                    'name' => 'tempat_lahir',
                                    'label' => 'Tempat Lahir',
                                    'value' => $user->tempat_lahir,
                                    'required' => true,
                                    'placeholder' => 'Kota/Kabupaten tempat lahir',
                                    'help' => 'Isi sesuai dokumen resmi.',
                                ])
                            </div>
                            <div class="col-md-6">
                                @include('dashboard.components.student-form-field', [
                                    'name' => 'tgl_lahir',
                                    'label' => 'Tanggal Lahir',
                                    'type' => 'date',
                                    'value' => $user->tgl_lahir,
                                    'required' => true,
                                    'help' => 'Pilih tanggal lahir sesuai dokumen resmi.',
                                ])
                            </div>
                            <div class="col-md-6">
                                <label for="jenis_kelamin" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select name="jenis_kelamin" id="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                                    <option value="">Pilih jenis kelamin</option>
                                    <option value="L" @selected(old('jenis_kelamin', $user->jenis_kelamin) === 'L')>Laki-laki</option>
                                    <option value="P" @selected(old('jenis_kelamin', $user->jenis_kelamin) === 'P')>Perempuan</option>
                                </select>
                                @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@else<div class="form-text">Hanya pilihan Laki-laki atau Perempuan.</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="agama_id" class="form-label">Agama <span class="text-danger">*</span></label>
                                <select name="agama_id" id="agama_id" class="form-select @error('agama_id') is-invalid @enderror" required>
                                    <option value="">Pilih agama</option>
                                    @foreach($agama as $item)
                                        <option value="{{ $item->id }}" @selected(old('agama_id', $user->agama_id) == $item->id)>{{ $item->nama_agama }}</option>
                                    @endforeach
                                </select>
                                @error('agama_id')<div class="invalid-feedback">{{ $message }}</div>@else<div class="form-text">Pilih agama sesuai data diri.</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="image" class="form-label">Foto Profil</label>
                                <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                                @error('image')<div class="invalid-feedback">{{ $message }}</div>@else<div class="form-text">Opsional. Format JPG/JPEG/PNG maksimal 2 MB.</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-alamat" role="tabpanel">
                    <div class="student-wizard-card p-4" x-data="alamatSelector()" x-init="initData()">
                        <h5 class="mb-3">Alamat Domisili</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="provinsi_id" class="form-label">Provinsi <span class="text-danger">*</span></label>
                                <select x-model="provinsi_id" @change="fetchKabupaten" name="provinsi_id" id="provinsi_id" class="form-select @error('provinsi_id') is-invalid @enderror" required>
                                    <option value="">Pilih provinsi</option>
                                    @foreach ($provinsi as $prov)
                                        <option value="{{ $prov->id_prov }}">{{ $prov->nama }}</option>
                                    @endforeach
                                </select>
                                @error('provinsi_id')<div class="invalid-feedback">{{ $message }}</div>@else<div class="form-text">Pilih provinsi domisili saat ini.</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="kabupaten_id" class="form-label">Kabupaten/Kota <span class="text-danger">*</span></label>
                                <select x-model="kabupaten_id" @change="fetchKecamatan" name="kabupaten_id" id="kabupaten_id" class="form-select @error('kabupaten_id') is-invalid @enderror" :disabled="loading.kabupaten" required>
                                    <option value="">Pilih kabupaten/kota</option>
                                    <template x-for="kab in kabupaten" :key="kab.id_kab">
                                        <option :value="kab.id_kab" x-text="kab.nama_kab"></option>
                                    </template>
                                </select>
                                @error('kabupaten_id')<div class="invalid-feedback">{{ $message }}</div>@else<div class="form-text" x-text="loading.kabupaten ? 'Memuat data kabupaten...' : 'Pilih setelah provinsi terisi.'"></div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="kecamatan_id" class="form-label">Kecamatan <span class="text-danger">*</span></label>
                                <select x-model="kecamatan_id" @change="fetchKelurahan" name="kecamatan_id" id="kecamatan_id" class="form-select @error('kecamatan_id') is-invalid @enderror" :disabled="loading.kecamatan" required>
                                    <option value="">Pilih kecamatan</option>
                                    <template x-for="kec in kecamatan" :key="kec.id_kec">
                                        <option :value="kec.id_kec" x-text="kec.nama_kec"></option>
                                    </template>
                                </select>
                                @error('kecamatan_id')<div class="invalid-feedback">{{ $message }}</div>@else<div class="form-text" x-text="loading.kecamatan ? 'Memuat data kecamatan...' : 'Pilih setelah kabupaten/kota terisi.'"></div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="kelurahan_id" class="form-label">Kelurahan/Desa <span class="text-danger">*</span></label>
                                <select x-model="kelurahan_id" name="kelurahan_id" id="kelurahan_id" class="form-select @error('kelurahan_id') is-invalid @enderror" :disabled="loading.kelurahan" required>
                                    <option value="">Pilih kelurahan/desa</option>
                                    <template x-for="kel in kelurahan" :key="kel.id_kel">
                                        <option :value="kel.id_kel" x-text="kel.nama_kel"></option>
                                    </template>
                                </select>
                                @error('kelurahan_id')<div class="invalid-feedback">{{ $message }}</div>@else<div class="form-text" x-text="loading.kelurahan ? 'Memuat data kelurahan...' : 'Pilih setelah kecamatan terisi.'"></div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="address" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                                <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="4" maxlength="1000" required placeholder="Nama jalan, nomor rumah, RT/RW, dusun/komplek">{{ old('address', $user->address) }}</textarea>
                                @error('address')<div class="invalid-feedback">{{ $message }}</div>@else<div class="form-text">Tulis alamat lengkap domisili. Maksimal 1000 karakter.</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-akademik" role="tabpanel">
                    <div class="student-wizard-card p-4">
                        <h5 class="mb-3">Data Akademik</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="jurusan_id" class="form-label">Program Studi <span class="text-danger">*</span></label>
                                <select name="jurusan_id" id="jurusan_id" class="form-select @error('jurusan_id') is-invalid @enderror" required>
                                    <option value="">Pilih program studi tujuan</option>
                                    @foreach($jurusan as $jurusanItem)
                                        <option value="{{ $jurusanItem->id }}" @selected(old('jurusan_id', $user->jurusan_id) == $jurusanItem->id)>{{ $jurusanItem->nama_jurusan }}</option>
                                    @endforeach
                                </select>
                                @error('jurusan_id')<div class="invalid-feedback">{{ $message }}</div>@else<div class="form-text">Pilih prodi yang akan diikuti pada PMB SBH.</div>@enderror
                            </div>
                            <div class="col-md-6">
                                @include('dashboard.components.student-form-field', [
                                    'name' => 'asal_sekolah',
                                    'label' => 'Asal Sekolah',
                                    'value' => $user->asal_sekolah,
                                    'placeholder' => 'Contoh: SMAN 1 Bogor',
                                    'help' => 'Isi nama sekolah asal. Opsional jika belum tersedia.',
                                ])
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-ortu" role="tabpanel">
                    <div class="student-wizard-card p-4">
                        <h5 class="mb-3">Data Orang Tua / Wali</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                @include('dashboard.components.student-form-field', ['name' => 'nama_ayah', 'label' => 'Nama Ayah', 'value' => $user->nama_ayah, 'placeholder' => 'Masukkan nama ayah', 'help' => 'Isi sesuai dokumen keluarga jika tersedia.'])
                            </div>
                            <div class="col-md-6">
                                <label for="pek_ayah_id" class="form-label">Pekerjaan Ayah</label>
                                <select name="pek_ayah_id" id="pek_ayah_id" class="form-select @error('pek_ayah_id') is-invalid @enderror">
                                    <option value="">Pilih pekerjaan ayah</option>
                                    @foreach($pekerjaanAyah as $item)
                                        <option value="{{ $item->id }}" @selected(old('pek_ayah_id', $user->pek_ayah_id) == $item->id)>{{ $item->nama_pek_ayah }}</option>
                                    @endforeach
                                </select>
                                @error('pek_ayah_id')<div class="invalid-feedback">{{ $message }}</div>@else<div class="form-text">Pilih dari data referensi yang tersedia.</div>@enderror
                            </div>
                            <div class="col-md-6">
                                @include('dashboard.components.student-form-field', ['name' => 'nama_ibu', 'label' => 'Nama Ibu', 'value' => $user->nama_ibu, 'placeholder' => 'Masukkan nama ibu', 'help' => 'Isi sesuai dokumen keluarga jika tersedia.'])
                            </div>
                            <div class="col-md-6">
                                <label for="pek_ibu_id" class="form-label">Pekerjaan Ibu</label>
                                <select name="pek_ibu_id" id="pek_ibu_id" class="form-select @error('pek_ibu_id') is-invalid @enderror">
                                    <option value="">Pilih pekerjaan ibu</option>
                                    @foreach($pekerjaanIbu as $item)
                                        <option value="{{ $item->id }}" @selected(old('pek_ibu_id', $user->pek_ibu_id) == $item->id)>{{ $item->nama_pek_ibu }}</option>
                                    @endforeach
                                </select>
                                @error('pek_ibu_id')<div class="invalid-feedback">{{ $message }}</div>@else<div class="form-text">Pilih dari data referensi yang tersedia.</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="penghasilan_id" class="form-label">Penghasilan Orang Tua</label>
                                <select name="penghasilan_id" id="penghasilan_id" class="form-select @error('penghasilan_id') is-invalid @enderror">
                                    <option value="">Pilih rentang penghasilan</option>
                                    @foreach($penghasilan as $peng)
                                        <option value="{{ $peng->id }}" @selected(old('penghasilan_id', $user->penghasilan_id) == $peng->id)>{{ $peng->nama_peng }}</option>
                                    @endforeach
                                </select>
                                @error('penghasilan_id')<div class="invalid-feedback">{{ $message }}</div>@else<div class="form-text">Pilih perkiraan rentang penghasilan orang tua/wali.</div>@enderror
                            </div>
                            <div class="col-md-6">
                                @include('dashboard.components.student-form-field', ['name' => 'no_telp_ortu', 'label' => 'Nomor Telepon Orang Tua', 'type' => 'tel', 'value' => $user->no_telp_ortu, 'placeholder' => '081234567890', 'help' => 'Nomor aktif keluarga yang dapat dihubungi. Maksimal 20 karakter.'])
                            </div>
                            <div class="col-md-6">
                                @include('dashboard.components.student-form-field', ['name' => 'nama_wali', 'label' => 'Nama Wali', 'value' => $user->nama_wali, 'placeholder' => 'Isi jika tinggal bersama wali', 'help' => 'Opsional jika ada wali selain orang tua.'])
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-preview" role="tabpanel">
                    <div class="student-wizard-card p-4">
                        <h5 class="mb-3">Preview Data</h5>
                        <p class="text-muted">Pastikan data sudah benar sebelum disimpan. Anda tetap bisa kembali ke langkah sebelumnya untuk memperbaiki isian.</p>
                        <div class="student-preview-grid">
                            @foreach([
                                'Nama' => old('name', $user->name),
                                'Email' => old('email', $user->email),
                                'Nomor HP' => old('phone', $user->phone),
                                'NIK' => old('nik', $user->nik),
                                'NISN' => old('nisn', $user->nisn),
                                'Program Studi' => optional($jurusan->firstWhere('id', old('jurusan_id', $user->jurusan_id)))->nama_jurusan,
                                'Tanggal Lahir' => old('tgl_lahir', $user->tgl_lahir),
                                'Alamat' => old('address', $user->address),
                            ] as $label => $value)
                                <div class="student-preview-item">
                                    <span>{{ $label }}</span>
                                    <strong>{{ $value ?: '-' }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column flex-sm-row justify-content-between gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary" id="prevWizardStep">
                        <i class="bx bx-chevron-left me-1"></i> Sebelumnya
                    </button>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <button type="button" class="btn btn-outline-primary" id="nextWizardStep">
                            Lanjut <i class="bx bx-chevron-right ms-1"></i>
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Simpan Biodata
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('script')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tabButtons = Array.from(document.querySelectorAll('#profileWizardTabs [data-bs-toggle="tab"]'));
        const nextButton = document.getElementById('nextWizardStep');
        const prevButton = document.getElementById('prevWizardStep');

        function activeIndex() {
            return tabButtons.findIndex((button) => button.classList.contains('active'));
        }

        function showStep(index) {
            const target = tabButtons[Math.max(0, Math.min(index, tabButtons.length - 1))];
            if (target) bootstrap.Tab.getOrCreateInstance(target).show();
        }

        nextButton?.addEventListener('click', () => showStep(activeIndex() + 1));
        prevButton?.addEventListener('click', () => showStep(activeIndex() - 1));

        const firstInvalid = document.querySelector('.is-invalid');
        if (firstInvalid) {
            const pane = firstInvalid.closest('.tab-pane');
            const trigger = pane ? document.querySelector(`[data-bs-target="#${pane.id}"]`) : null;
            if (trigger) bootstrap.Tab.getOrCreateInstance(trigger).show();
            setTimeout(() => firstInvalid.focus({ preventScroll: false }), 250);
        }
    });

    document.addEventListener('alpine:init', () => {
        Alpine.data('alamatSelector', () => ({
            provinsi_id: '{{ old('provinsi_id', $user->provinsi_id ?? '') }}',
            kabupaten_id: '{{ old('kabupaten_id', $user->kabupaten_id ?? '') }}',
            kecamatan_id: '{{ old('kecamatan_id', $user->kecamatan_id ?? '') }}',
            kelurahan_id: '{{ old('kelurahan_id', $user->kelurahan_id ?? '') }}',
            kabupaten: [],
            kecamatan: [],
            kelurahan: [],
            loading: { kabupaten: false, kecamatan: false, kelurahan: false },
            async initData() {
                if (this.provinsi_id) await this.fetchKabupaten(true);
                if (this.kabupaten_id) await this.fetchKecamatan(true);
                if (this.kecamatan_id) await this.fetchKelurahan(true);
            },
            async fetchKabupaten(isInit = false) {
                if (!this.provinsi_id) return;
                this.loading.kabupaten = true;
                const res = await fetch(`/dashboard/get-kabupaten/${this.provinsi_id}`);
                this.kabupaten = await res.json();
                if (!isInit) {
                    this.kabupaten_id = '';
                    this.kecamatan_id = '';
                    this.kelurahan_id = '';
                    this.kecamatan = [];
                    this.kelurahan = [];
                }
                this.loading.kabupaten = false;
            },
            async fetchKecamatan(isInit = false) {
                if (!this.kabupaten_id) return;
                this.loading.kecamatan = true;
                const res = await fetch(`/dashboard/get-kecamatan/${this.kabupaten_id}`);
                this.kecamatan = await res.json();
                if (!isInit) {
                    this.kecamatan_id = '';
                    this.kelurahan_id = '';
                    this.kelurahan = [];
                }
                this.loading.kecamatan = false;
            },
            async fetchKelurahan(isInit = false) {
                if (!this.kecamatan_id) return;
                this.loading.kelurahan = true;
                const res = await fetch(`/dashboard/get-kelurahan/${this.kecamatan_id}`);
                this.kelurahan = await res.json();
                if (!isInit) this.kelurahan_id = '';
                this.loading.kelurahan = false;
            },
        }));
    });
</script>
@endpush
