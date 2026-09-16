@extends('admin_dashboard.layout.master')

@section('title', 'Detail Calon Mahasiswa')

@section('content')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Calon Mahasiswa Baru', 'url' => route('admin.mahasiswa-baru.index')],
        ['label' => 'Detail Profil']
    ];

    // Format phone number to international WhatsApp standard
    $phoneNum = preg_replace('/[^0-9]/', '', $mahasiswa->phone ?? '');
    if (str_starts_with($phoneNum, '0')) {
        $phoneNum = '62' . substr($phoneNum, 1);
    }
    $waUrl = $phoneNum ? "https://wa.me/{$phoneNum}?text=" . urlencode("Halo " . $mahasiswa->name . ", kami dari Panitia PMB STIKES BOGOR Husada ingin menyampaikan perihal pendaftaran Anda...") : '#';
@endphp

{{-- ✅ Page Hero --}}
@include('admin_dashboard.components.page-hero', [
    'title' => 'Detail Profil Calon Mahasiswa',
    'subtitle' => 'Informasi terperinci, berkas dokumen, dan kelayakan verifikasi pendaftaran calon mahasiswa.',
    'icon' => 'user-voice',
    'breadcrumbs' => $breadcrumbs,
    'actions' => '
        <a href="' . route('admin.mahasiswa-baru.edit', $mahasiswa->id) . '" class="btn btn-warning btn-action">
            <i class="bx bx-edit-alt"></i> Ubah Data
        </a>
        <a href="' . route('admin.mahasiswa-baru.index') . '" class="btn btn-secondary btn-action">
            <i class="bx bx-arrow-back"></i> Kembali
        </a>
    '
])

<div class="container-fluid px-0">
    <div class="row">
        
        {{-- ✅ LEFT COLUMN - Profile Avatar Summary & Quick Actions --}}
        <div class="col-lg-4 col-12 mb-4">
            {{-- Profile Card --}}
            @component('admin_dashboard.components.admin-card')
                <div class="text-center py-3 border-bottom pb-4">
                    {{-- Profile Avatar Frame --}}
                    <div class="d-flex justify-content-center mb-3">
                        <div class="position-relative" style="width: 130px; height: 130px;">
                            @if($mahasiswa->image)
                                <img src="{{ $mahasiswa->getProfileImageURL() }}" alt="Foto Profil" class="rounded-circle img-fluid border border-3 border-primary shadow-md h-100 w-100" style="object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-label-primary border border-3 border-primary d-flex align-items-center justify-content-center text-primary fw-bold shadow-md h-100 w-100" style="font-size: 3rem;">
                                    {{ strtoupper(substr($mahasiswa->name, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    {{-- Name and Code --}}
                    <h5 class="fw-bold mb-1 text-heading">{{ $mahasiswa->name }}</h5>
                    <span class="badge bg-label-primary text-uppercase font-monospace fs-7 fw-bold px-3 py-1.5 rounded-pill mb-3">
                        {{ $mahasiswa->code ?? 'BELUM ADA KODE' }}
                    </span>
                    <p class="text-muted fs-7 mb-0">{{ $mahasiswa->email }}</p>
                </div>

                {{-- Status Badges Stack --}}
                <div class="py-3 border-bottom">
                    <h6 class="fw-bold mb-3 text-heading fs-7"><i class="bx bx-toggle-right me-2 text-primary"></i>Ringkasan Status</h6>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted fs-7">Kelengkapan Biodata</span>
                            @if($mahasiswa->status_biodata === 1)
                                <span class="badge bg-label-success fs-8 fw-bold">Lengkap</span>
                            @else
                                <span class="badge bg-label-warning fs-8 fw-bold">Belum Lengkap / Draft</span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted fs-7">Verifikasi Berkas</span>
                            @if($mahasiswa->status_berkas === 1)
                                <span class="badge bg-label-success fs-8 fw-bold"><i class="bx bx-check me-1 fs-9"></i>Terverifikasi</span>
                            @elseif($mahasiswa->status_berkas === 2)
                                <span class="badge bg-label-danger fs-8 fw-bold"><i class="bx bx-x me-1 fs-9"></i>Ditolak</span>
                            @else
                                <span class="badge bg-label-secondary fs-8 fw-bold">Belum Diverifikasi</span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted fs-7">Pembayaran & Alur PMB</span>
                            @include('admin_dashboard.components.status-pmb-badge', ['value' => $mahasiswa->status_pemb])
                        </div>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="pt-3 mb-0">
                    <h6 class="fw-bold mb-3 text-heading fs-7"><i class="bx bx-bolt-circle me-2 text-success"></i>Tindakan Cepat</h6>
                    <div class="d-flex flex-column gap-2">
                        @if($phoneNum)
                            <a href="{{ $waUrl }}" target="_blank" class="btn btn-label-success w-100 text-start d-flex align-items-center gap-2">
                                <i class="bx bxl-whatsapp fs-5"></i> Kirim WhatsApp Calon
                            </a>
                        @else
                            <button type="button" class="btn btn-label-secondary w-100 text-start d-flex align-items-center gap-2" disabled>
                                <i class="bx bxl-whatsapp fs-5"></i> No HP Tidak Ada
                            </button>
                        @endif

                        <a href="mailto:{{ $mahasiswa->email }}" class="btn btn-label-primary w-100 text-start d-flex align-items-center gap-2">
                            <i class="bx bx-envelope fs-5"></i> Kirim Email Manual
                        </a>
                        
                        <div class="dropdown-divider my-2"></div>
                        
                        {{-- Trigger Kirim Info Lulus --}}
                        <form action="{{ route('admin.mahasiswa-baru.sendMessage', $mahasiswa->id) }}" method="POST" class="w-100">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning w-100 text-start d-flex align-items-center gap-2">
                                <i class="bx bx-send text-warning fs-5"></i> Kirim Notifikasi Kelulusan
                            </button>
                        </form>
                        
                        {{-- Trigger Kirim Info Gagal --}}
                        <form action="{{ route('admin.mahasiswa-baru.sendFailureMessage', $mahasiswa->id) }}" method="POST" class="w-100">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100 text-start d-flex align-items-center gap-2">
                                <i class="bx bx-x-circle text-danger fs-5"></i> Kirim Notifikasi Ketidaklulusan
                            </button>
                        </form>
                    </div>
                </div>
            @endcomponent
        </div>

        {{-- ✅ RIGHT COLUMN - Detailed Nav-Pills and Tabs --}}
        <div class="col-lg-8 col-12">
            <div class="card shadow-sm border border-light-50 mb-4">
                {{-- Tabs Headers --}}
                <div class="card-header bg-light border-bottom p-0">
                    <ul class="nav nav-tabs nav-fill border-0 py-1" id="profileTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active fw-bold py-3 text-heading" id="pribadi-tab" data-bs-toggle="tab" data-bs-target="#tab-pribadi" type="button" role="tab" aria-controls="tab-pribadi" aria-selected="true">
                                <i class="bx bx-user me-1"></i> Informasi Pribadi
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold py-3 text-heading" id="alamat-tab" data-bs-toggle="tab" data-bs-target="#tab-alamat" type="button" role="tab" aria-controls="tab-alamat" aria-selected="false">
                                <i class="bx bx-map me-1"></i> Alamat & Kontak
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold py-3 text-heading" id="keluarga-tab" data-bs-toggle="tab" data-bs-target="#tab-keluarga" type="button" role="tab" aria-controls="tab-keluarga" aria-selected="false">
                                <i class="bx bx-group me-1"></i> Keluarga
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold py-3 text-heading" id="berkas-tab" data-bs-toggle="tab" data-bs-target="#tab-berkas" type="button" role="tab" aria-controls="tab-berkas" aria-selected="false">
                                <i class="bx bx-file me-1"></i> Berkas Pendukung
                            </button>
                        </li>
                    </ul>
                </div>

                {{-- Tabs Contents --}}
                <div class="card-body p-4" style="min-height: 400px;">
                    <div class="tab-content p-0" id="profileTabsContent">
                        
                        {{-- 📝 TAB 1: Informasi Akademik & Pribadi --}}
                        <div class="tab-pane fade show active animate-fade-in" id="tab-pribadi" role="tabpanel" aria-labelledby="pribadi-tab">
                            <h6 class="fw-bold mb-4 text-heading border-bottom pb-2"><i class="bx bx-book me-2 text-primary"></i>Informasi Akademik</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Program Studi Pilihan</small>
                                    <span class="text-heading fw-semibold fs-6">{{ $mahasiswa->jurusan->nama_jurusan ?? '-' }}</span>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Periode Akademik</small>
                                    <span class="text-heading fw-semibold fs-6">{{ $mahasiswa->periode->deskripsi ?? '-' }}</span>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Gelombang PMB</small>
                                    <span class="text-heading fw-semibold fs-6">{{ $mahasiswa->gelombang->nama_gelombang ?? '-' }}</span>
                                </div>
                            </div>

                            <h6 class="fw-bold mb-4 text-heading border-bottom pb-2"><i class="bx bx-user me-2 text-primary"></i>Biodata Calon Mahasiswa</h6>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="d-flex flex-column gap-3">
                                        <div>
                                            <small class="text-muted d-block">Nama Lengkap</small>
                                            <span class="text-heading fw-semibold">{{ $mahasiswa->name }}</span>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Nomor Induk Kependudukan (NIK)</small>
                                            <span class="text-heading fw-semibold font-monospace">{{ $mahasiswa->nik ?? '-' }}</span>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Nomor Induk Siswa Nasional (NISN)</small>
                                            <span class="text-heading fw-semibold font-monospace">{{ $mahasiswa->nisn ?? '-' }}</span>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Agama</small>
                                            <span class="text-heading fw-semibold">{{ $mahasiswa->agama->nama_agama ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex flex-column gap-3">
                                        <div>
                                            <small class="text-muted d-block">Tempat / Tanggal Lahir</small>
                                            <span class="text-heading fw-semibold">
                                                {{ $mahasiswa->tempat_lahir ?? '-' }}, {{ $mahasiswa->tgl_lahir ? \Carbon\Carbon::parse($mahasiswa->tgl_lahir)->translatedFormat('d F Y') : '-' }}
                                            </span>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Jenis Kelamin</small>
                                            <span class="text-heading fw-semibold">
                                                @if($mahasiswa->jenis_kelamin === 'L') Laki-Laki @elseif($mahasiswa->jenis_kelamin === 'P') Perempuan @else - @endif
                                            </span>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Asal Sekolah</small>
                                            <span class="text-heading fw-semibold">{{ $mahasiswa->asal_sekolah ?? '-' }}</span>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Plain Password (Kredensial)</small>
                                            @if ($mahasiswa->password_plaintext)
                                                <code class="px-2 py-0.5 rounded bg-label-secondary text-secondary" style="font-family: monospace; font-size: 0.875rem;">{{ $mahasiswa->password_plaintext }}</code>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                            <form action="{{ route('admin.mahasiswa-baru.generate-password', $mahasiswa->id) }}" method="POST" class="mt-2">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('Buat password baru untuk mahasiswa ini?')">
                                                    <i class="bx bx-key me-1"></i> Generate Password Baru
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 📌 TAB 2: Alamat & Kontak --}}
                        <div class="tab-pane fade animate-fade-in" id="tab-alamat" role="tabpanel" aria-labelledby="alamat-tab">
                            <h6 class="fw-bold mb-4 text-heading border-bottom pb-2"><i class="bx bx-phone me-2 text-primary"></i>Detail Kontak</h6>
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Nomor HP / WhatsApp Mahasiswa</small>
                                    <span class="text-heading fw-semibold fs-6">{{ $mahasiswa->phone ?? '-' }}</span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Nomor Telepon Orang Tua / Wali</small>
                                    <span class="text-heading fw-semibold fs-6">{{ $mahasiswa->no_telp_ortu ?? '-' }}</span>
                                </div>
                            </div>

                            <h6 class="fw-bold mb-4 text-heading border-bottom pb-2"><i class="bx bx-map me-2 text-primary"></i>Alamat Tempat Tinggal</h6>
                            <div class="row g-4">
                                <div class="col-md-12">
                                    <small class="text-muted d-block">Alamat Lengkap</small>
                                    <span class="text-heading fw-semibold">{{ $mahasiswa->address ?? '-' }}</span>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex flex-column gap-3">
                                        <div>
                                            <small class="text-muted d-block">Provinsi</small>
                                            <span class="text-heading fw-semibold">{{ $mahasiswa->provinsi->nama ?? '-' }}</span>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Kabupaten / Kota</small>
                                            <span class="text-heading fw-semibold">{{ $mahasiswa->kabupaten->nama_kab ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex flex-column gap-3">
                                        <div>
                                            <small class="text-muted d-block">Kecamatan</small>
                                            <span class="text-heading fw-semibold">{{ $mahasiswa->kecamatan->nama_kec ?? '-' }}</span>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Kelurahan</small>
                                            <span class="text-heading fw-semibold">{{ $mahasiswa->kelurahan->nama_kel ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 👥 TAB 3: Keluarga --}}
                        <div class="tab-pane fade animate-fade-in" id="tab-keluarga" role="tabpanel" aria-labelledby="keluarga-tab">
                            <h6 class="fw-bold mb-4 text-heading border-bottom pb-2"><i class="bx bx-group me-2 text-primary"></i>Data Orang Tua</h6>
                            <div class="row g-4 mb-4">
                                {{-- Data Ayah --}}
                                <div class="col-md-6">
                                    <div class="border rounded p-3 bg-light-50 h-100">
                                        <h6 class="fw-bold text-primary mb-3"><i class="bx bx-male me-2"></i>Data Ayah</h6>
                                        <div class="d-flex flex-column gap-3">
                                            <div>
                                                <small class="text-muted d-block">Nama Ayah</small>
                                                <span class="text-heading fw-semibold">{{ $mahasiswa->nama_ayah ?? '-' }}</span>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Pekerjaan Ayah</small>
                                                <span class="text-heading fw-semibold">{{ $mahasiswa->pekerjaanAyah->nama_pek_ayah ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Data Ibu --}}
                                <div class="col-md-6">
                                    <div class="border rounded p-3 bg-light-50 h-100">
                                        <h6 class="fw-bold text-danger mb-3"><i class="bx bx-female me-2"></i>Data Ibu</h6>
                                        <div class="d-flex flex-column gap-3">
                                            <div>
                                                <small class="text-muted d-block">Nama Ibu</small>
                                                <span class="text-heading fw-semibold">{{ $mahasiswa->nama_ibu ?? '-' }}</span>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Pekerjaan Ibu</small>
                                                <span class="text-heading fw-semibold">{{ $mahasiswa->pekerjaanIbu->nama_pek_ibu ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h6 class="fw-bold mb-4 text-heading border-bottom pb-2"><i class="bx bx-user me-2 text-primary"></i>Data Wali & Penghasilan</h6>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Nama Wali <span class="fs-9">(Jika Ada)</span></small>
                                    <span class="text-heading fw-semibold">{{ $mahasiswa->nama_wali ?? '-' }}</span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Rentang Penghasilan Orang Tua</small>
                                    <span class="text-heading fw-semibold">{{ $mahasiswa->penghasilan->nama_peng ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- 📁 TAB 4: Berkas & Dokumen --}}
                        <div class="tab-pane fade animate-fade-in" id="tab-berkas" role="tabpanel" aria-labelledby="berkas-tab">
                            <h6 class="fw-bold mb-4 text-heading border-bottom pb-2"><i class="bx bx-folder me-2 text-primary"></i>Pratinjau Berkas & Bukti Pembayaran</h6>
                            
                            <div class="row g-3">
                                @php
                                    $documents = [
                                        'img_ktp' => ['label' => 'Kartu Tanda Penduduk (KTP)', 'icon' => 'bx-id-card', 'color' => 'primary'],
                                        'img_kk' => ['label' => 'Kartu Keluarga (KK)', 'icon' => 'bx-group', 'color' => 'info'],
                                        'img_ijazah' => ['label' => 'Ijazah / SKL (Opsional)', 'icon' => 'bx-graduation', 'color' => 'success'],
                                        'img_bukti' => ['label' => 'Bukti Pembayaran', 'icon' => 'bx-receipt', 'color' => 'warning'],
                                    ];
                                @endphp

                                @foreach($documents as $key => $doc)
                                    @php
                                        $fileUrl = $mahasiswa->pmbDocumentUrl($key);
                                    @endphp
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 h-100 d-flex flex-column justify-content-between bg-light-50 hover-shadow-sm transition-all">
                                            <div>
                                                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                                    <span class="fw-semibold text-heading fs-7">{{ $doc['label'] }}</span>
                                                    @if($fileUrl)
                                                        <span class="badge bg-label-success fs-9 py-0.5"><i class="bx bx-check-circle me-1 fs-9"></i>Terunggah</span>
                                                    @else
                                                        <span class="badge bg-label-danger fs-9 py-0.5"><i class="bx bx-x-circle me-1 fs-9"></i>Belum Ada</span>
                                                    @endif
                                                </div>

                                                {{-- Thumbnail Preview --}}
                                                <div class="d-flex justify-content-center bg-white border rounded py-3 mb-3 position-relative overflow-hidden cursor-pointer" style="height: 140px;" 
                                                     @if($fileUrl) onclick="openLightbox('{{ $fileUrl }}', '{{ $doc['label'] }}')" @endif>
                                                    @if($fileUrl)
                                                        <img src="{{ $fileUrl }}" alt="{{ $doc['label'] }}" class="img-fluid rounded" style="object-fit: contain; max-height: 100%;">
                                                        <div class="position-absolute h-100 w-100 top-0 start-0 bg-dark opacity-0 hover-opacity-20 d-flex align-items-center justify-content-center text-white transition-all">
                                                            <i class="bx bx-zoom-in fs-2"></i>
                                                        </div>
                                                    @else
                                                        <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                                                            <i class="bx {{ $doc['icon'] }} text-{{ $doc['color'] }} display-6 mb-2"></i>
                                                            <small class="fs-8">Berkas belum diunggah</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Document Actions --}}
                                            <div class="d-flex gap-2">
                                                @if($fileUrl)
                                                    <button type="button" class="btn btn-sm btn-label-primary flex-fill" onclick="openLightbox('{{ $fileUrl }}', '{{ $doc['label'] }}')">
                                                        <i class="bx bx-zoom-in me-1"></i> Preview
                                                    </button>
                                                    <a href="{{ $fileUrl }}" download class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Download Berkas">
                                                        <i class="bx bx-download"></i>
                                                    </a>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-label-secondary flex-fill" disabled>
                                                        <i class="bx bx-zoom-in me-1"></i> Preview
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                                                        <i class="bx bx-download"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ✅ Document Lightbox Modal --}}
<div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-semibold text-heading" id="lightboxTitle">Pratinjau Dokumen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4 bg-light-50">
                <img id="lightboxImage" src="" alt="Pratinjau Gambar" class="img-fluid rounded border shadow-sm" style="max-height: 75vh; object-fit: contain;">
            </div>
            <div class="modal-footer border-top bg-light-50">
                <a id="lightboxDownloadBtn" href="" download class="btn btn-primary"><i class="bx bx-download me-1"></i> Unduh Berkas</a>
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openLightbox(url, title) {
        document.getElementById('lightboxImage').src = url;
        document.getElementById('lightboxTitle').innerText = title;
        document.getElementById('lightboxDownloadBtn').href = url;
        
        const modal = new bootstrap.Modal(document.getElementById('lightboxModal'));
        modal.show();
    }
</script>
@endsection
