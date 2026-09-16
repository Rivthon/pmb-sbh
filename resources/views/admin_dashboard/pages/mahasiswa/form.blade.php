@extends('admin_dashboard.layout.master')
@section('title', $mahasiswa ? 'Ubah Calon Mahasiswa' : 'Tambah Calon Mahasiswa Baru')

@section('content')

{{-- Page Hero Header --}}
@include('admin_dashboard.components.page-hero', [
    'title' => $mahasiswa ? 'Ubah Data Calon Mahasiswa' : 'Tambah Calon Mahasiswa Baru',
    'subtitle' => $mahasiswa ? 'Ubah biodata lengkap, kelola berkas unggahan, akademik, dan status verifikasi.' : 'Formulir pendaftaran calon mahasiswa baru untuk dimasukkan ke sistem akademik.',
    'icon' => 'user-plus',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Daftar PMB', 'url' => route('admin.mahasiswa-baru.index')],
        ['label' => $mahasiswa ? 'Ubah Data' : 'Tambah Baru']
    ]
])

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bx bx-check-circle fs-4"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bx bx-error-circle fs-4"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Validation Summary Alert --}}
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-start gap-2 mb-4" role="alert" id="errorSummaryAlert">
        <i class="bx bx-error-circle fs-4 mt-0.5"></i>
        <div>
            <h6 class="alert-heading mb-1 fw-bold text-danger">Gagal Menyimpan Data!</h6>
            <p class="mb-2" style="font-size: 0.875rem;">Silakan periksa kembali isian formulir Anda. Terdapat beberapa kesalahan input pada tab yang ditandai warna merah:</p>
            <ul class="mb-0 ps-3" style="font-size: 0.8125rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Main Form Overhaul --}}
<form action="{{ $action_url }}" method="POST" enctype="multipart/form-data" id="mahasiswaForm" class="row">
    @csrf
    @if($method == 'PUT')
        @method('PUT')
    @endif

    {{-- Left Side: Tab Navigations --}}
    <div class="col-12 col-lg-3 mb-4">
        <div class="card admin-card p-2">
            <div class="card-header border-0 pb-1">
                <h6 class="fw-bold mb-0 text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em;">Langkah Formulir</h6>
            </div>
            <ul class="nav nav-pills flex-column gap-1" id="wizardPills" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link w-100 text-start d-flex align-items-center gap-2 active {{ $errors->hasAny(['name', 'email', 'phone', 'password']) ? 'text-danger fw-bold border-start border-3 border-danger' : '' }}" 
                        id="akun-tab" data-bs-toggle="tab" data-bs-target="#tab-akun" type="button" role="tab" aria-controls="tab-akun" aria-selected="true">
                        <i class="bx bx-lock-alt"></i>
                        <span>1. Informasi Akun</span>
                        @if($errors->hasAny(['name', 'email', 'phone', 'password']))
                            <i class="bx bx-error-circle text-danger ms-auto fs-5"></i>
                        @endif
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link w-100 text-start d-flex align-items-center gap-2 {{ $errors->hasAny(['nik', 'nisn', 'tempat_lahir', 'tgl_lahir', 'jenis_kelamin', 'agama_id', 'provinsi_id', 'kabupaten_id', 'kecamatan_id', 'kelurahan_id', 'address']) ? 'text-danger fw-bold border-start border-3 border-danger' : '' }}" 
                        id="pribadi-tab" data-bs-toggle="tab" data-bs-target="#tab-pribadi" type="button" role="tab" aria-controls="tab-pribadi" aria-selected="false">
                        <i class="bx bx-user"></i>
                        <span>2. Data Pribadi</span>
                        @if($errors->hasAny(['nik', 'nisn', 'tempat_lahir', 'tgl_lahir', 'jenis_kelamin', 'agama_id', 'provinsi_id', 'kabupaten_id', 'kecamatan_id', 'kelurahan_id', 'address']))
                            <i class="bx bx-error-circle text-danger ms-auto fs-5"></i>
                        @endif
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link w-100 text-start d-flex align-items-center gap-2 {{ $errors->hasAny(['periode_id', 'gelombang_id', 'jurusan_id', 'asal_sekolah']) ? 'text-danger fw-bold border-start border-3 border-danger' : '' }}" 
                        id="akademik-tab" data-bs-toggle="tab" data-bs-target="#tab-akademik" type="button" role="tab" aria-controls="tab-akademik" aria-selected="false">
                        <i class="bx bx-graduation"></i>
                        <span>3. Data Akademik</span>
                        @if($errors->hasAny(['periode_id', 'gelombang_id', 'jurusan_id', 'asal_sekolah']))
                            <i class="bx bx-error-circle text-danger ms-auto fs-5"></i>
                        @endif
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link w-100 text-start d-flex align-items-center gap-2 {{ $errors->hasAny(['nama_ayah', 'pek_ayah_id', 'nama_ibu', 'pek_ibu_id', 'penghasilan_id', 'no_telp_ortu', 'nama_wali']) ? 'text-danger fw-bold border-start border-3 border-danger' : '' }}" 
                        id="ortua-tab" data-bs-toggle="tab" data-bs-target="#tab-ortua" type="button" role="tab" aria-controls="tab-ortua" aria-selected="false">
                        <i class="bx bx-group"></i>
                        <span>4. Orang Tua / Wali</span>
                        @if($errors->hasAny(['nama_ayah', 'pek_ayah_id', 'nama_ibu', 'pek_ibu_id', 'penghasilan_id', 'no_telp_ortu', 'nama_wali']))
                            <i class="bx bx-error-circle text-danger ms-auto fs-5"></i>
                        @endif
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link w-100 text-start d-flex align-items-center gap-2 {{ $errors->hasAny(['status_biodata', 'status_berkas', 'status_pemb']) ? 'text-danger fw-bold border-start border-3 border-danger' : '' }}" 
                        id="status-tab" data-bs-toggle="tab" data-bs-target="#tab-status" type="button" role="tab" aria-controls="tab-status" aria-selected="false">
                        <i class="bx bx-toggle-right"></i>
                        <span>5. Status PMB</span>
                        @if($errors->hasAny(['status_biodata', 'status_berkas', 'status_pemb']))
                            <i class="bx bx-error-circle text-danger ms-auto fs-5"></i>
                        @endif
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link w-100 text-start d-flex align-items-center gap-2 {{ $errors->hasAny(['image', 'img_ktp', 'img_kk', 'img_ijazah', 'img_bukti']) ? 'text-danger fw-bold border-start border-3 border-danger' : '' }}" 
                        id="dokumen-tab" data-bs-toggle="tab" data-bs-target="#tab-dokumen" type="button" role="tab" aria-controls="tab-dokumen" aria-selected="false">
                        <i class="bx bx-file"></i>
                        <span>6. Berkas & Dokumen</span>
                        @if($errors->hasAny(['image', 'img_ktp', 'img_kk', 'img_ijazah', 'img_bukti']))
                            <i class="bx bx-error-circle text-danger ms-auto fs-5"></i>
                        @endif
                    </button>
                </li>
            </ul>
        </div>
    </div>

    {{-- Right Side: Form Sections Tab Content --}}
    <div class="col-12 col-lg-9">
        <div class="tab-content p-0" id="wizardPillsContent">
            
            {{-- TAB 1: Informasi Akun --}}
            <div class="tab-pane fade show active animate-fade-in" id="tab-akun" role="tabpanel" aria-labelledby="akun-tab">
                @component('admin_dashboard.components.admin-card', [
                    'title' => 'Informasi Akun',
                    'subtitle' => 'Konfigurasi akun login calon mahasiswa baru.',
                    'icon' => 'bx bx-lock-alt'
                ])
                    <div class="row g-3">
                        <div class="col-md-12">
                            @include('admin_dashboard.components.form-input', [
                                'name' => 'name',
                                'label' => 'Nama Lengkap',
                                'required' => true,
                                'value' => $mahasiswa?->name,
                                'placeholder' => 'Masukkan nama lengkap mahasiswa sesuai ijazah',
                                'help' => 'Gunakan huruf kapital sesuai akta/ijazah.'
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-input', [
                                'name' => 'email',
                                'label' => 'Alamat Email',
                                'type' => 'email',
                                'required' => true,
                                'value' => $mahasiswa?->email,
                                'placeholder' => 'nama@contoh.com',
                                'help' => 'Harus unik, digunakan untuk login aplikasi.'
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-input', [
                                'name' => 'phone',
                                'label' => 'Nomor HP (WhatsApp)',
                                'type' => 'text',
                                'required' => true,
                                'inputmode' => 'numeric',
                                'value' => $mahasiswa?->phone,
                                'placeholder' => 'Contoh: 08123456789',
                                'help' => 'Gunakan nomor aktif untuk sinkronisasi notifikasi WA.'
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-input', [
                                'name' => 'password',
                                'label' => 'Kata Sandi (Password)',
                                'type' => 'password',
                                'required' => ($mahasiswa ? false : true),
                                'placeholder' => $mahasiswa ? 'Kosongkan jika tidak ingin mengubah' : 'Masukkan password akun',
                                'help' => 'Minimal 8 karakter alfanumerik.'
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-input', [
                                'name' => 'password_confirmation',
                                'label' => 'Konfirmasi Kata Sandi',
                                'type' => 'password',
                                'required' => ($mahasiswa ? false : true),
                                'placeholder' => 'Masukkan ulang password akun'
                            ])
                        </div>
                    </div>
                @endcomponent
            </div>

            {{-- TAB 2: Data Pribadi --}}
            <div class="tab-pane fade animate-fade-in" id="tab-pribadi" role="tabpanel" aria-labelledby="pribadi-tab">
                @component('admin_dashboard.components.admin-card', [
                    'title' => 'Data Pribadi & Alamat',
                    'subtitle' => 'Data identitas kependudukan dan tempat tinggal calon mahasiswa.',
                    'icon' => 'bx bx-user'
                ])
                    <div class="row g-3">
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-input', [
                                'name' => 'nik',
                                'label' => 'NIK (Nomor Induk Kependudukan)',
                                'type' => 'text',
                                'inputmode' => 'numeric',
                                'maxlength' => 16,
                                'required' => true,
                                'value' => $mahasiswa?->nik,
                                'placeholder' => '16 digit NIK kartu keluarga',
                                'help' => 'Persis 16 digit angka sesuai KTP/KK.'
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-input', [
                                'name' => 'nisn',
                                'label' => 'NISN (Nomor Induk Siswa Nasional)',
                                'type' => 'text',
                                'inputmode' => 'numeric',
                                'maxlength' => 10,
                                'required' => true,
                                'value' => $mahasiswa?->nisn,
                                'placeholder' => '10 digit NISN aktif sekolah',
                                'help' => 'Persis 10 digit angka.'
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-input', [
                                'name' => 'tempat_lahir',
                                'label' => 'Tempat Lahir',
                                'required' => true,
                                'value' => $mahasiswa?->tempat_lahir,
                                'placeholder' => 'Kota/Kabupaten tempat lahir'
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-input', [
                                'name' => 'tgl_lahir',
                                'label' => 'Tanggal Lahir',
                                'type' => 'date',
                                'required' => true,
                                'value' => $mahasiswa?->tgl_lahir
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-select', [
                                'name' => 'jenis_kelamin',
                                'label' => 'Jenis Kelamin',
                                'required' => true,
                                'selected' => $mahasiswa?->jenis_kelamin,
                                'options' => [
                                    ['value' => 'L', 'label' => 'Laki-Laki'],
                                    ['value' => 'P', 'label' => 'Perempuan']
                                ]
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-select', [
                                'name' => 'agama_id',
                                'label' => 'Agama',
                                'required' => true,
                                'selected' => $mahasiswa?->agama_id,
                                'options' => $agama->map(fn($item) => ['value' => $item->id, 'label' => $item->nama_agama])
                            ])
                        </div>

                        <div class="col-12 mt-3 pt-3 border-top">
                            <h6 class="fw-semibold text-dark mb-3"><i class="bx bx-map text-primary me-1"></i> Data Alamat Domisili</h6>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label for="provinsi_id" class="form-label">Provinsi <span class="text-danger">*</span></label>
                                <select name="provinsi_id" id="provinsi_id" class="form-select @error('provinsi_id') is-invalid @enderror" required>
                                    <option value="">Pilih Provinsi</option>
                                    @foreach ($provinsi as $prov)
                                        <option value="{{ $prov->id_prov }}" {{ old('provinsi_id', $mahasiswa?->provinsi_id) == $prov->id_prov ? 'selected' : '' }}>
                                            {{ $prov->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('provinsi_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label for="kabupaten_id" class="form-label">Kabupaten / Kota <span class="text-danger">*</span></label>
                                <select name="kabupaten_id" id="kabupaten_id" class="form-select @error('kabupaten_id') is-invalid @enderror" required>
                                    <option value="">Pilih Kabupaten</option>
                                    @foreach ($kabupaten as $kab)
                                        <option value="{{ $kab->id_kab }}" {{ old('kabupaten_id', $mahasiswa?->kabupaten_id) == $kab->id_kab ? 'selected' : '' }}>
                                            {{ $kab->nama_kab }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kabupaten_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label for="kecamatan_id" class="form-label">Kecamatan <span class="text-danger">*</span></label>
                                <select name="kecamatan_id" id="kecamatan_id" class="form-select @error('kecamatan_id') is-invalid @enderror" required>
                                    <option value="">Pilih Kecamatan</option>
                                    @foreach ($kecamatan as $kec)
                                        <option value="{{ $kec->id_kec }}" {{ old('kecamatan_id', $mahasiswa?->kecamatan_id) == $kec->id_kec ? 'selected' : '' }}>
                                            {{ $kec->nama_kec }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kecamatan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label for="kelurahan_id" class="form-label">Kelurahan / Desa <span class="text-danger">*</span></label>
                                <select name="kelurahan_id" id="kelurahan_id" class="form-select @error('kelurahan_id') is-invalid @enderror" required>
                                    <option value="">Pilih Kelurahan</option>
                                    @foreach ($kelurahan as $kel)
                                        <option value="{{ $kel->id_kel }}" {{ old('kelurahan_id', $mahasiswa?->kelurahan_id) == $kel->id_kel ? 'selected' : '' }}>
                                            {{ $kel->nama_kel }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kelurahan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-12">
                            @include('admin_dashboard.components.form-textarea', [
                                'name' => 'address',
                                'label' => 'Alamat Detail Jalan / RT / RW',
                                'required' => true,
                                'value' => $mahasiswa?->address,
                                'rows' => 3,
                                'placeholder' => 'Masukkan alamat domisili lengkap beserta RT/RW, nomor rumah, dan jalan'
                            ])
                        </div>
                    </div>
                @endcomponent
            </div>

            {{-- TAB 3: Data Akademik --}}
            <div class="tab-pane fade animate-fade-in" id="tab-akademik" role="tabpanel" aria-labelledby="akademik-tab">
                @component('admin_dashboard.components.admin-card', [
                    'title' => 'Data Akademik Calon Mahasiswa',
                    'subtitle' => 'Konfigurasi prodi pilihan, periode, gelombang, dan riwayat sekolah.',
                    'icon' => 'bx bx-graduation'
                ])
                    <div class="row g-3">
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-select', [
                                'name' => 'periode_id',
                                'label' => 'Periode Akademik',
                                'required' => true,
                                'selected' => $mahasiswa?->periode_id,
                                'options' => $periodes->map(fn($item) => ['value' => $item->id, 'label' => $item->deskripsi])
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-select', [
                                'name' => 'gelombang_id',
                                'label' => 'Gelombang Pendaftaran',
                                'required' => true,
                                'selected' => $mahasiswa?->gelombang_id,
                                'options' => $gelombangs->map(fn($item) => ['value' => $item->id, 'label' => $item->nama_gelombang])
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-select', [
                                'name' => 'jurusan_id',
                                'label' => 'Program Studi Pilihan',
                                'required' => true,
                                'selected' => $mahasiswa?->jurusan_id,
                                'options' => $jurusan->map(fn($item) => ['value' => $item->id, 'label' => $item->nama_jurusan])
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-input', [
                                'name' => 'asal_sekolah',
                                'label' => 'Asal Sekolah',
                                'value' => $mahasiswa?->asal_sekolah,
                                'placeholder' => 'SMA / SMK / MA Asal'
                            ])
                        </div>
                    </div>
                @endcomponent
            </div>

            {{-- TAB 4: Data Orang Tua --}}
            <div class="tab-pane fade animate-fade-in" id="tab-ortua" role="tabpanel" aria-labelledby="ortua-tab">
                @component('admin_dashboard.components.admin-card', [
                    'title' => 'Data Orang Tua & Wali',
                    'subtitle' => 'Data identitas sosial ekonomi orang tua kandung / wali calon mahasiswa.',
                    'icon' => 'bx bx-group'
                ])
                    <div class="row g-3">
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-input', [
                                'name' => 'nama_ayah',
                                'label' => 'Nama Ayah Kandung',
                                'value' => $mahasiswa?->nama_ayah,
                                'placeholder' => 'Masukkan nama lengkap ayah'
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-select', [
                                'name' => 'pek_ayah_id',
                                'label' => 'Pekerjaan Ayah',
                                'selected' => $mahasiswa?->pek_ayah_id,
                                'options' => $pekerjaanAyah->map(fn($item) => ['value' => $item->id, 'label' => $item->nama_pek_ayah])
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-input', [
                                'name' => 'nama_ibu',
                                'label' => 'Nama Ibu Kandung',
                                'value' => $mahasiswa?->nama_ibu,
                                'placeholder' => 'Masukkan nama lengkap ibu'
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-select', [
                                'name' => 'pek_ibu_id',
                                'label' => 'Pekerjaan Ibu',
                                'selected' => $mahasiswa?->pek_ibu_id,
                                'options' => $pekerjaanIbu->map(fn($item) => ['value' => $item->id, 'label' => $item->nama_pek_ibu])
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-select', [
                                'name' => 'penghasilan_id',
                                'label' => 'Penghasilan Orang Tua (Gabungan)',
                                'selected' => $mahasiswa?->penghasilan_id,
                                'options' => $penghasilan->map(fn($item) => ['value' => $item->id, 'label' => $item->nama_peng])
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-input', [
                                'name' => 'no_telp_ortu',
                                'label' => 'Nomor HP / Telepon Orang Tua',
                                'type' => 'text',
                                'inputmode' => 'numeric',
                                'value' => $mahasiswa?->no_telp_ortu,
                                'placeholder' => 'Contoh: 08123456789'
                            ])
                        </div>
                        <div class="col-md-12">
                            @include('admin_dashboard.components.form-input', [
                                'name' => 'nama_wali',
                                'label' => 'Nama Wali (Jika Ada)',
                                'value' => $mahasiswa?->nama_wali,
                                'placeholder' => 'Kosongkan jika tidak ada wali'
                            ])
                        </div>
                    </div>
                @endcomponent
            </div>

            {{-- TAB 5: Status PMB --}}
            <div class="tab-pane fade animate-fade-in" id="tab-status" role="tabpanel" aria-labelledby="status-tab">
                @component('admin_dashboard.components.admin-card', [
                    'title' => 'Status Pendaftaran & Kelulusan',
                    'subtitle' => 'Konfigurasi alur kelayakan verifikasi pendaftaran calon mahasiswa.',
                    'icon' => 'bx bx-toggle-right'
                ])
                    <div class="row g-3">
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-select', [
                                'name' => 'status_biodata',
                                'label' => 'Status Kelengkapan Biodata',
                                'selected' => $mahasiswa?->status_biodata ?? 0,
                                'options' => [
                                    ['value' => 0, 'label' => 'Belum Lengkap / Draft'],
                                    ['value' => 1, 'label' => 'Lengkap / Terisi']
                                ],
                                'help' => 'Draft berarti pengisian biodata mandiri peserta belum selesai.'
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-select', [
                                'name' => 'status_berkas',
                                'label' => 'Status Verifikasi Berkas',
                                'selected' => $mahasiswa?->status_berkas ?? 0,
                                'options' => [
                                    ['value' => 0, 'label' => 'Belum Diverifikasi'],
                                    ['value' => 1, 'label' => 'Terverifikasi / Valid'],
                                    ['value' => 2, 'label' => 'Ditolak / Tidak Valid']
                                ]
                            ])
                        </div>
                        <div class="col-md-12">
                            @include('admin_dashboard.components.form-select', [
                                'name' => 'status_pemb',
                                'label' => 'Status Pembayaran & Alur PMB',
                                'selected' => $mahasiswa?->status_pemb,
                                'options' => collect($pmbStatusOptions)->map(fn($item) => ['value' => $item['value'], 'label' => $item['label']])
                            ])
                        </div>
                    </div>
                @endcomponent
            </div>

            {{-- TAB 6: Berkas & Dokumen --}}
            <div class="tab-pane fade animate-fade-in" id="tab-dokumen" role="tabpanel" aria-labelledby="dokumen-tab">
                @component('admin_dashboard.components.admin-card', [
                    'title' => 'Dokumen & Berkas Pendukung',
                    'subtitle' => 'Unggah dan pratinjau berkas pendaftaran calon mahasiswa.',
                    'icon' => 'bx bx-file'
                ])
                    <div class="row g-4">
                        <!-- 1. Foto Profil -->
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-input', [
                                'name' => 'image',
                                'label' => 'Foto Profil Calon Mahasiswa',
                                'type' => 'file',
                                'accept' => 'image/png, image/jpeg, image/jpg',
                                'help' => 'Format JPG, JPEG, PNG. Maksimal 2MB.'
                            ])
                            @if($mahasiswa && $mahasiswa->image)
                                <div class="mt-2 p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $mahasiswa->getProfileImageURL() }}" alt="Preview" class="rounded bg-white shadow-sm" style="width: 50px; height: 50px; object-fit: cover;" onerror="this.src='{{ asset('dashboard_assets/assets/img/avatars/1.png') }}'">
                                        <div>
                                            <small class="d-block text-muted">Foto Profil Lama</small>
                                            <a href="{{ $mahasiswa->getProfileImageURL() }}" target="_blank" class="fw-semibold text-primary" style="font-size: 0.8125rem;">Lihat Full <i class="bx bx-link-external"></i></a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- 2. Dokumen KTP -->
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-input', [
                                'name' => 'img_ktp',
                                'label' => 'Unggah File KTP',
                                'type' => 'file',
                                'accept' => 'image/png, image/jpeg, image/jpg',
                                'help' => 'Format JPG, JPEG, PNG. Maksimal 2MB.'
                            ])
                            @if($mahasiswa && $mahasiswa->img_ktp)
                                <div class="mt-2 p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bx bx-id-card fs-3 text-primary"></i>
                                        <div>
                                            <small class="d-block text-muted">Berkas KTP Lama</small>
                                            <a href="{{ $mahasiswa->pmbDocumentUrl('img_ktp') }}" target="_blank" class="fw-semibold text-primary" style="font-size: 0.8125rem;">Lihat Berkas <i class="bx bx-link-external"></i></a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- 3. Dokumen KK -->
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-input', [
                                'name' => 'img_kk',
                                'label' => 'Unggah Kartu Keluarga (KK)',
                                'type' => 'file',
                                'accept' => 'image/png, image/jpeg, image/jpg',
                                'help' => 'Format JPG, JPEG, PNG. Maksimal 2MB.'
                            ])
                            @if($mahasiswa && $mahasiswa->img_kk)
                                <div class="mt-2 p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bx bx-group fs-3 text-success"></i>
                                        <div>
                                            <small class="d-block text-muted">Berkas KK Lama</small>
                                            <a href="{{ $mahasiswa->pmbDocumentUrl('img_kk') }}" target="_blank" class="fw-semibold text-primary" style="font-size: 0.8125rem;">Lihat Berkas <i class="bx bx-link-external"></i></a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- 4. Dokumen Ijazah -->
                        <div class="col-md-6">
                            @include('admin_dashboard.components.form-input', [
                                'name' => 'img_ijazah',
                                'label' => 'Unggah File Ijazah / SKL (Opsional)',
                                'type' => 'file',
                                'accept' => 'image/png, image/jpeg, image/jpg',
                                'help' => 'Opsional. Format JPG, JPEG, PNG. Maksimal 2MB.'
                            ])
                            @if($mahasiswa && $mahasiswa->img_ijazah)
                                <div class="mt-2 p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bx bx-file-blank fs-3 text-warning"></i>
                                        <div>
                                            <small class="d-block text-muted">Berkas Ijazah / SKL Lama</small>
                                            <a href="{{ $mahasiswa->pmbDocumentUrl('img_ijazah') }}" target="_blank" class="fw-semibold text-primary" style="font-size: 0.8125rem;">Lihat Berkas <i class="bx bx-link-external"></i></a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- 5. Bukti Pembayaran -->
                        <div class="col-md-12">
                            @include('admin_dashboard.components.form-input', [
                                'name' => 'img_bukti',
                                'label' => 'Unggah Bukti Transaksi Pembayaran',
                                'type' => 'file',
                                'accept' => 'image/png, image/jpeg, image/jpg',
                                'help' => 'Format JPG, JPEG, PNG. Maksimal 2MB.'
                            ])
                            @if($mahasiswa && $mahasiswa->img_bukti)
                                <div class="mt-2 p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bx bx-wallet fs-3 text-danger"></i>
                                        <div>
                                            <small class="d-block text-muted">Bukti Pembayaran Lama</small>
                                            <a href="{{ $mahasiswa->pmbDocumentUrl('img_bukti') }}" target="_blank" class="fw-semibold text-primary" style="font-size: 0.8125rem;">Lihat Bukti Bayar <i class="bx bx-link-external"></i></a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endcomponent
            </div>

            {{-- Form Buttons Card Footer --}}
            <div class="card admin-card mb-4 mt-2">
                <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3 bg-light rounded">
                    <a href="{{ route('admin.mahasiswa-baru.index') }}" class="btn btn-outline-secondary btn-action" id="btnBatal">
                        <i class="bx bx-arrow-back"></i> Batal & Kembali
                    </a>
                    <button type="submit" class="btn btn-primary btn-action px-4 shadow-sm" id="btnSubmit">
                        <i class="bx bx-save"></i>
                        <span>{{ $mahasiswa ? 'Simpan Perubahan' : 'Simpan Calon Mahasiswa' }}</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</form>

@endsection

@push('script')
<script>
$(document).ready(function() {
    // --- Dynamic Gelombang Selection ---
    $('[name="periode_id"]').on('change', function() {
        let periodeId = $(this).val();
        let $gelombangSelect = $('[name="gelombang_id"]');
        let currentGelombangId = $gelombangSelect.val() || '{{ old("gelombang_id", $mahasiswa?->gelombang_id) }}';
        
        if (periodeId) {
            $gelombangSelect.prop('disabled', true);
            $.ajax({
                url: '/admin/gelombang/by-periode/' + periodeId,
                type: 'GET',
                success: function(data) {
                    $gelombangSelect.empty().append('<option value="">Pilih...</option>');
                    $.each(data, function(key, value) {
                        let badgeText = value.status === 1 ? ' (Aktif)' : '';
                        let isSelected = String(value.id) === String(currentGelombangId) ? 'selected' : '';
                        $gelombangSelect.append('<option value="' + value.id + '" ' + isSelected + '>' + value.nama_gelombang + badgeText + '</option>');
                    });
                    $gelombangSelect.prop('disabled', false);
                },
                error: function() {
                    $gelombangSelect.prop('disabled', false);
                }
            });
        } else {
            $gelombangSelect.empty().append('<option value="">Pilih...</option>');
        }
    });

    // --- Dynamic Address Selection (Preserved & Optimized) ---
    $('#provinsi_id').on('change', function() {
        let provinsi_id = $(this).val();
        if (provinsi_id) {
            $.ajax({
                url: '/admin/get-kabupaten/' + provinsi_id,
                type: 'GET',
                success: function(data) {
                    $('#kabupaten_id').empty().append('<option value="">Pilih Kabupaten</option>');
                    $('#kecamatan_id').empty().append('<option value="">Pilih Kecamatan</option>');
                    $('#kelurahan_id').empty().append('<option value="">Pilih Kelurahan</option>');
                    $.each(data, function(key, value) {
                        $('#kabupaten_id').append('<option value="' + value.id_kab + '">' + value.nama_kab + '</option>');
                    });
                }
            });
        } else {
            $('#kabupaten_id').empty().append('<option value="">Pilih Kabupaten</option>');
            $('#kecamatan_id').empty().append('<option value="">Pilih Kecamatan</option>');
            $('#kelurahan_id').empty().append('<option value="">Pilih Kelurahan</option>');
        }
    });

    $('#kabupaten_id').on('change', function() {
        let kabupaten_id = $(this).val();
        if (kabupaten_id) {
            $.ajax({
                url: '/admin/get-kecamatan/' + kabupaten_id,
                type: 'GET',
                success: function(data) {
                    $('#kecamatan_id').empty().append('<option value="">Pilih Kecamatan</option>');
                    $('#kelurahan_id').empty().append('<option value="">Pilih Kelurahan</option>');
                    $.each(data, function(key, value) {
                        $('#kecamatan_id').append('<option value="' + value.id_kec + '">' + value.nama_kec + '</option>');
                    });
                }
            });
        } else {
            $('#kecamatan_id').empty().append('<option value="">Pilih Kecamatan</option>');
            $('#kelurahan_id').empty().append('<option value="">Pilih Kelurahan</option>');
        }
    });

    $('#kecamatan_id').on('change', function() {
        let kecamatan_id = $(this).val();
        if (kecamatan_id) {
            $.ajax({
                url: '/admin/get-kelurahan/' + kecamatan_id,
                type: 'GET',
                success: function(data) {
                    $('#kelurahan_id').empty().append('<option value="">Pilih Kelurahan</option>');
                    $.each(data, function(key, value) {
                        $('#kelurahan_id').append('<option value="' + value.id_kel + '">' + value.nama_kel + '</option>');
                    });
                }
            });
        } else {
            $('#kelurahan_id').empty().append('<option value="">Pilih Kelurahan</option>');
        }
    });

    // --- Input Restrictions for NIK & NISN (Digit limiting) ---
    const nikInput = document.getElementById('nik-'); // uniqid generated pattern check
    const nisnInput = document.getElementById('nisn-');
    
    // Fallback selectors in case unique id is used
    const $nik = $('input[name="nik"]');
    const $nisn = $('input[name="nisn"]');

    $nik.on('input', function() {
        let val = $(this).val().replace(/\D/g, ''); // only allow digits
        if (val.length > 16) val = val.slice(0, 16);
        $(this).val(val);
    });

    $nisn.on('input', function() {
        let val = $(this).val().replace(/\D/g, '');
        if (val.length > 10) val = val.slice(0, 10);
        $(this).val(val);
    });

    // --- Tab Auto Switch & Focus on Validation Errors ---
    const firstError = document.querySelector('.is-invalid, .invalid-feedback');
    if (firstError) {
        const tabPane = firstError.closest('.tab-pane');
        if (tabPane) {
            const tabId = tabPane.id;
            const tabTrigger = document.querySelector(`[data-bs-target="#${tabId}"]`);
            if (tabTrigger) {
                const tab = new bootstrap.Tab(tabTrigger);
                tab.show();
            }
        }
        
        // Smooth scroll to the error summary or first invalid field
        const scrollTarget = document.getElementById('errorSummaryAlert') || firstError;
        if (scrollTarget) {
            scrollTarget.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    // --- Form Dirty Check / Page Leaving Alert ---
    let isFormDirty = false;
    $('#mahasiswaForm input, #mahasiswaForm select, #mahasiswaForm textarea').on('change input', function() {
        isFormDirty = true;
    });

    $('#mahasiswaForm').on('submit', function() {
        isFormDirty = false; // reset warning on submit
    });

    window.addEventListener('beforeunload', function(e) {
        if (isFormDirty) {
            e.preventDefault();
            e.returnValue = 'Anda memiliki perubahan data yang belum disimpan. Apakah Anda yakin ingin meninggalkan halaman ini?';
            return e.returnValue;
        }
    });

    // --- Loading Button Spinner UX ---
    $('#mahasiswaForm').on('submit', function(e) {
        const $btn = $('#btnSubmit');
        if ($btn.length) {
            $btn.prop('disabled', true);
            $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Menyimpan data...');
        }
    });
});
</script>
@endpush
