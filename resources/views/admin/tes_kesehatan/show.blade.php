@extends('admin_dashboard.layout.master')
@section('title', 'Detail Tes Kesehatan (Anamnesa)')

@section('content')
@php
    $pemeriksaan = $anamnesa->pemeriksaan;
    $includeUrine = (bool) $anamnesa->user?->jurusan?->isD3Kebidanan();
    $officialExamSections = \App\Models\TesKesehatanPemeriksaan::officialExamSections($includeUrine);
    $anamnesaQuestions = \App\Models\TesKesehatanAnamnesa::officerAnamnesaQuestions();
    $yesNoLabel = fn ($value) => $value === 1 || $value === true ? 'Ya' : ($value === 0 || $value === false ? 'Tidak' : '-');
@endphp
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary mb-0">Detail Tes Kesehatan (Anamnesa)</h4>
        <a href="{{ route('admin.tes-kesehatan.index') }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back"></i> Kembali
        </a>
    </div>

    {{-- =======================
    DATA ANAMNESA
    ======================= --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-label-primary d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-primary">
                    <i class="bx bx-user me-2"></i> Data Anamnesa Mahasiswa
                </h6>
                <a href="{{ route('admin.tes-kesehatan.cetak', $anamnesa->id) }}" target="_blank"
                    class="btn btn-sm btn-outline-danger">
                    <i class="bx bx-printer me-1"></i> Cetak PDF
                </a>
            </div>

            <div class="card-body">
                {{-- ✅ Data Mahasiswa --}}
                <div class="table-responsive mb-4 mt-4">
                    <table class="table table-sm table-borderless align-middle mb-0">
                        <tbody>
                            <tr>
                                <th style="width: 220px" class="text-muted fw-semibold">Nama Mahasiswa</th>
                                <td>{{ $anamnesa->user->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Email</th>
                                <td>{{ $anamnesa->user->email ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Surat Kesehatan Klinik/RS</th>
                                <td>
                                    @if($anamnesa->surat_kesehatan_path)
                                        <a href="{{ asset('storage/' . $anamnesa->surat_kesehatan_path) }}" target="_blank" rel="noopener">Lihat File</a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Tanggal Pengisian</th>
                                <td>
                                    {{ $anamnesa->tanggal_pengisian
                                    ? \Carbon\Carbon::parse($anamnesa->tanggal_pengisian)->translatedFormat('d F Y')
                                    : 'Belum diisi' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="border-top pt-3 mt-3"></div>

                <div class="table-responsive mb-4">
                    <h6 class="fw-bold text-primary mb-2"><i class="bx bx-plus-medical me-1"></i> Anamnesa Lengkap</h6>
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 48px">No</th>
                                <th>Pernyataan</th>
                                <th style="width: 110px">Jawaban</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($anamnesaQuestions as $index => $question)
                                @php
                                    $field = $question['field'];
                                    $noteField = "{$field}_keterangan";
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $question['label'] }}</td>
                                    <td>{{ $yesNoLabel(data_get($anamnesa, $field)) }}</td>
                                    <td>{{ data_get($anamnesa, $noteField) ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>


                <div class="row">
                    <div class="col-lg-6">
                        {{-- ✅ Riwayat Kesehatan --}}
                        <div class="table-responsive mb-4">
                            <h6 class="fw-bold text-primary mb-2"><i class="bx bx-plus-medical me-1"></i> Riwayat
                                Kesehatan</h6>
                            <table class="table table-sm align-middle mb-0">
                                <tbody>
                                    @php
                                    function yn($val) {
                                    return $val === 1 ? 'Ya' : ($val === 0 ? 'Tidak' : '-');
                                    }
                                    @endphp

                                    <tr>
                                        <th>Riwayat Penyakit Keluarga</th>
                                        <td>{{ yn($anamnesa->riwayat_penyakit_keluarga) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Riwayat Penyakit Pribadi</th>
                                        <td>{{ yn($anamnesa->riwayat_penyakit_pribadi) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Riwayat Operasi</th>
                                        <td>{{ yn($anamnesa->riwayat_operasi) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Konsumsi Obat Rutin</th>
                                        <td>{{ yn($anamnesa->konsumsi_obat_rutin) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Penyakit Menular</th>
                                        <td>{{ yn($anamnesa->penyakit_menular) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Masalah Kulit</th>
                                        <td>{{ yn($anamnesa->masalah_kulit) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Epilepsi</th>
                                        <td>{{ yn($anamnesa->epilepsi) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Cedera Kepala</th>
                                        <td>{{ yn($anamnesa->cedera_kepala) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Batuk Kronis</th>
                                        <td>{{ yn($anamnesa->batuk_kronis) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Gangguan Pencernaan</th>
                                        <td>{{ yn($anamnesa->gangguan_pencernaan) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Gangguan Keseimbangan</th>
                                        <td>{{ yn($anamnesa->gangguan_keseimbangan) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        {{-- ✅ Kondisi Psikologis & Fisik --}}
                        <div class="table-responsive mb-4">
                            <h6 class="fw-bold text-primary mb-2"><i class="bx bx-brain me-1"></i> Kondisi Psikologis &
                                Fisik
                            </h6>
                            <table class="table table-sm align-middle mb-0">
                                <tbody>
                                    <tr>
                                        <th>Claustrophobia</th>
                                        <td>{{ yn($anamnesa->claustrophobia) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Takut Darah</th>
                                        <td>{{ yn($anamnesa->takut_darah) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Kacamata</th>
                                        <td>{{ yn($anamnesa->kacamata) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Alat Bantu Tulang</th>
                                        <td>{{ yn($anamnesa->alat_bantu_tulang) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Lemah Otot</th>
                                        <td>{{ yn($anamnesa->lemah_otot) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Pikiran Bunuh Diri</th>
                                        <td>{{ yn($anamnesa->pikiran_bunuh_diri) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Riwayat Konsultasi Psikolog</th>
                                        <td>{{ yn($anamnesa->riwayat_psikolog) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>





                {{-- ✅ Keterangan Tambahan --}}
                <div class="table-responsive mb-4">
                    <h6 class="fw-bold text-primary mb-2"><i class="bx bx-comment-detail me-1"></i> Keterangan Tambahan
                    </h6>
                    <table class="table table-sm align-middle mb-0">
                        <tbody>
                            <tr>
                                <th>Keterangan</th>
                                <td>{{ $anamnesa->keterangan ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- ✅ Status Pemeriksaan --}}
                <div class="border-top pt-3 mt-3">
                    <form action="{{ route('admin.tes-kesehatan.update-status', $anamnesa->id) }}" method="POST"
                        class="row g-2 align-items-center">
                        @csrf
                        @method('PUT')

                        <div class="col-auto">
                            <label class="fw-semibold mb-0 text-muted">
                                <i class="bx bx-heart me-1"></i> Status Pemeriksaan:
                            </label>
                        </div>

                        <div class="col-auto">
                            <select name="status" class="form-select form-select-sm">
                                <option value="belum diperiksa" {{ $anamnesa->status === 'belum diperiksa' ? 'selected'
                                    : ''
                                    }}>Belum Diperiksa</option>
                                <option value="lulus" {{ $anamnesa->status === 'lulus' ? 'selected' : '' }}>Lulus
                                </option>
                                <option value="tidak lulus" {{ $anamnesa->status === 'tidak lulus' ? 'selected' : ''
                                    }}>Tidak
                                    Lulus</option>
                                <option value="perlu tindak lanjut" {{ $anamnesa->status === 'perlu tindak lanjut' ?
                                    'selected'
                                    : '' }}>Perlu Tindak Lanjut</option>
                            </select>
                        </div>

                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bx bx-save me-1"></i> Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($pemeriksaan)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-label-primary d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-primary mb-0">
                <i class="bx bx-stethoscope me-2"></i> Hasil Pemeriksaan Fisik
            </h6>
            <span class="badge bg-label-success">Sudah Diperiksa</span>
        </div>
        <div class="card-body">
            <div class="table-responsive mb-4">
                <table class="table table-sm table-borderless align-middle mb-0">
                    <tbody>
                        <tr>
                            <th style="width: 220px" class="text-muted fw-semibold">Nama Pemeriksa</th>
                            <td>{{ $pemeriksaan->nama_pemeriksa ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold">Tanggal Pemeriksaan</th>
                            <td>{{ $pemeriksaan->tanggal_pemeriksaan ? \Carbon\Carbon::parse($pemeriksaan->tanggal_pemeriksaan)->translatedFormat('d F Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold">Tinggi Badan</th>
                            <td>{{ $pemeriksaan->tinggi_badan ? $pemeriksaan->tinggi_badan . ' cm' : '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold">Berat Badan</th>
                            <td>{{ $pemeriksaan->berat_badan ? $pemeriksaan->berat_badan . ' kg' : '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold">Tekanan Darah</th>
                            <td>{{ $pemeriksaan->tekanan_darah ? $pemeriksaan->tekanan_darah . ' mmHg' : '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @foreach($officialExamSections as $sectionTitle => $items)
            <div class="table-responsive mb-4">
                <h6 class="fw-bold text-primary mb-2">{{ $sectionTitle }}</h6>
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Jenis Pemeriksaan</th>
                            <th style="width: 140px">Hasil</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $field => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td>{{ $pemeriksaan->conditionLabel(data_get($pemeriksaan, "{$field}_kondisi")) }}</td>
                            <td>{{ data_get($pemeriksaan, "{$field}_keterangan") ?: '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endforeach

            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <tbody>
                        <tr>
                            <th style="width: 220px">Catatan Pemeriksa</th>
                            <td>{{ $pemeriksaan->catatan ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Rekomendasi</th>
                            <td class="fw-semibold">{{ ucfirst($pemeriksaan->rekomendasi ?? '-') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Notifikasi sukses --}}
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Notifikasi error --}}
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        Terjadi kesalahan pada input data:
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    {{-- =======================
    FORM PEMERIKSAAN
    ======================= --}}
    @if($showPhysicalExamForm ?? true)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-label-primary d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-primary mb-0">
                <i class="bx bx-stethoscope me-2"></i> Form Pemeriksaan Fisik
            </h6>
        </div>

        <div class="card-body">
            {{-- Notifikasi --}}
            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="bx bx-error me-1"></i> Terjadi Kesalahan:</strong>
                <ul class="mb-0 small ps-3">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <form action="{{ route('admin.tes-kesehatan.store-pemeriksaan', $anamnesa->id) }}" method="POST"
                class="needs-validation" novalidate>
                @csrf

                {{-- Identitas Pemeriksa --}}
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted mb-1">Nama Pemeriksa *</label>
                        <input type="text" name="nama_pemeriksa" class="form-control" value="{{ auth()->user()->name }}"
                            readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted mb-1">Tanggal Pemeriksaan *</label>
                        <input type="date" name="tanggal_pemeriksaan" class="form-control"
                            value="{{ old('tanggal_pemeriksaan', $anamnesa->pemeriksaan->tanggal_pemeriksaan ?? now()->toDateString()) }}"
                            required>
                    </div>
                </div>

                {{-- Data Fisik --}}
                <div class="border-top pt-3 mt-4">
                    <h6 class="fw-semibold text-primary mb-3">Data Fisik</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Tinggi Badan (cm)</label>
                            <input type="number" step="0.1" name="tinggi_badan" class="form-control"
                                value="{{ old('tinggi_badan', $anamnesa->pemeriksaan->tinggi_badan ?? '') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Berat Badan (kg)</label>
                            <input type="number" step="0.1" name="berat_badan" class="form-control"
                                value="{{ old('berat_badan', $anamnesa->pemeriksaan->berat_badan ?? '') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Tekanan Darah</label>
                            <input type="text" name="tekanan_darah" class="form-control"
                                value="{{ old('tekanan_darah', $anamnesa->pemeriksaan->tekanan_darah ?? '') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Kidal</label>
                            <select name="kidal" class="form-select">
                                <option value="">-- Pilih --</option>
                                <option value="Ya" {{ old('kidal', $anamnesa->pemeriksaan->kidal ?? '') == 'Ya' ?
                                    'selected'
                                    : '' }}>Ya</option>
                                <option value="Tidak" {{ old('kidal', $anamnesa->pemeriksaan->kidal ?? '') == 'Tidak' ?
                                    'selected' : '' }}>Tidak</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Pemeriksaan Tambahan --}}
                <div class="border-top pt-3 mt-4">
                    <h6 class="fw-semibold text-primary mb-3">Pemeriksaan Tambahan</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Konjungtiva</label>
                            <select name="konjungtiva" class="form-select">
                                <option value="">-- Pilih Kondisi --</option>
                                <option value="normal" {{ old('konjungtiva', $anamnesa->pemeriksaan->konjungtiva ?? '')
                                    ==
                                    'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="pucat" {{ old('konjungtiva', $anamnesa->pemeriksaan->konjungtiva ?? '')
                                    ==
                                    'pucat' ? 'selected' : '' }}>Pucat</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Ikhterik</label>
                            <select name="ikhterik" class="form-select">
                                <option value="">-- Pilih Kondisi --</option>
                                <option value="tidak_ikhterik" {{ old('ikhterik', $anamnesa->pemeriksaan->ikhterik ??
                                    '') ==
                                    'tidak_ikhterik' ? 'selected' : '' }}>Tidak Ikhterik</option>
                                <option value="ikhterik" {{ old('ikhterik', $anamnesa->pemeriksaan->ikhterik ?? '') ==
                                    'ikhterik' ? 'selected' : '' }}>Ikhterik</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Buta Warna</label>
                            <select name="buta_warna" class="form-select">
                                <option value="">-- Pilih --</option>
                                <option value="Normal" {{ old('buta_warna', $anamnesa->pemeriksaan->buta_warna ?? '') ==
                                    'Normal' ? 'selected' : '' }}>Normal</option>
                                <option value="Parsial" {{ old('buta_warna', $anamnesa->pemeriksaan->buta_warna ?? '')
                                    ==
                                    'Parsial' ? 'selected' : '' }}>Parsial</option>
                                <option value="Total" {{ old('buta_warna', $anamnesa->pemeriksaan->buta_warna ?? '') ==
                                    'Total' ? 'selected' : '' }}>Total</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Respon Pendengaran</label>
                            <select name="respon_pendengaran" class="form-select">
                                <option value="">-- Pilih --</option>
                                <option value="Normal" {{ old('respon_pendengaran', $anamnesa->
                                    pemeriksaan->respon_pendengaran ?? '') == 'Normal' ? 'selected' : '' }}>Normal
                                </option>
                                <option value="Kurang" {{ old('respon_pendengaran', $anamnesa->
                                    pemeriksaan->respon_pendengaran ?? '') == 'Kurang' ? 'selected' : '' }}>Kurang
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Kelengkapan Jari Atas</label>
                            <select name="kelengkapan_jari_atas" class="form-select">
                                <option value="">-- Pilih --</option>
                                <option value="Lengkap" {{ old('kelengkapan_jari_atas', $anamnesa->
                                    pemeriksaan->kelengkapan_jari_atas ?? '') == 'Lengkap' ? 'selected' : '' }}>Lengkap
                                </option>
                                <option value="Tidak Lengkap" {{ old('kelengkapan_jari_atas', $anamnesa->
                                    pemeriksaan->kelengkapan_jari_atas ?? '') == 'Tidak Lengkap' ? 'selected' : ''
                                    }}>Tidak
                                    Lengkap</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tremor</label>
                            <select name="tremor" class="form-select">
                                <option value="">-- Pilih --</option>
                                <option value="Ada" {{ old('tremor', $anamnesa->pemeriksaan->tremor ?? '') == 'Ada' ?
                                    'selected' : '' }}>Ada</option>
                                <option value="Tidak Ada" {{ old('tremor', $anamnesa->pemeriksaan->tremor ?? '') ==
                                    'Tidak
                                    Ada' ? 'selected' : '' }}>Tidak Ada</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Bekas Luka / Sayatan</label>
                            <input type="text" name="bekas_luka_sayatan" class="form-control"
                                value="{{ old('bekas_luka_sayatan', $anamnesa->pemeriksaan->bekas_luka_sayatan ?? '') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Bunyi Jantung</label>
                            <input type="text" name="bunyi_jantung" class="form-control"
                                value="{{ old('bunyi_jantung', $anamnesa->pemeriksaan->bunyi_jantung ?? '') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Bunyi Paru</label>
                            <input type="text" name="bunyi_paru" class="form-control"
                                value="{{ old('bunyi_paru', $anamnesa->pemeriksaan->bunyi_paru ?? '') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Catatan Pemeriksa</label>
                            <textarea name="catatan" rows="2"
                                class="form-control">{{ old('catatan', $anamnesa->pemeriksaan->catatan ?? '') }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Rekomendasi</label>
                            <select name="rekomendasi" class="form-select">
                                <option value="">-- Pilih --</option>
                                <option value="layak" {{ old('rekomendasi', $anamnesa->pemeriksaan->rekomendasi ?? '')
                                    ===
                                    'layak' ? 'selected' : '' }}>Layak</option>
                                <option value="tidak layak" {{ old('rekomendasi', $anamnesa->pemeriksaan->rekomendasi ??
                                    '')
                                    === 'tidak layak' ? 'selected' : '' }}>Tidak Layak</option>
                                <option value="perlu pemeriksaan lanjutan" {{ old('rekomendasi', $anamnesa->
                                    pemeriksaan->rekomendasi ?? '') === 'perlu pemeriksaan lanjutan' ? 'selected' : ''
                                    }}>Perlu Pemeriksaan Lanjutan</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-12 text-end mt-4">
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bx bx-save me-1"></i> Simpan Pemeriksaan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
