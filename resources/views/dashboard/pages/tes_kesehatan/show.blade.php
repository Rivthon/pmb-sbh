@extends('dashboard.layout.master')
@section('title', 'Hasil Tes Kesehatan')

@section('content')
@php
    $statusMeta = match ($anamnesa->status) {
        'lulus' => ['label' => 'Lulus Pemeriksaan', 'tone' => 'success', 'icon' => 'bx-check-circle'],
        'tidak lulus' => ['label' => 'Tidak Lulus', 'tone' => 'danger', 'icon' => 'bx-x-circle'],
        'perlu tindak lanjut' => ['label' => 'Perlu Tindak Lanjut', 'tone' => 'info', 'icon' => 'bx-refresh'],
        default => ['label' => 'Belum Diperiksa', 'tone' => 'warning', 'icon' => 'bx-time-five'],
    };
    $items = [
        ['field' => 'riwayat_penyakit_keluarga', 'label' => 'Riwayat Penyakit Keluarga', 'value' => $anamnesa->riwayat_penyakit_keluarga],
        ['field' => 'riwayat_penyakit_pribadi', 'label' => 'Riwayat Penyakit Pribadi', 'value' => $anamnesa->riwayat_penyakit_pribadi],
        ['field' => 'riwayat_operasi', 'label' => 'Riwayat Operasi', 'value' => $anamnesa->riwayat_operasi],
        ['field' => 'konsumsi_obat_rutin', 'label' => 'Konsumsi Obat Rutin', 'value' => $anamnesa->konsumsi_obat_rutin],
        ['field' => 'penyakit_menular', 'label' => 'Penyakit Menular', 'value' => $anamnesa->penyakit_menular],
        ['field' => 'masalah_kulit', 'label' => 'Masalah Kulit', 'value' => $anamnesa->masalah_kulit],
        ['field' => 'tumor_benjolan', 'label' => 'Tumor / Benjolan / Kista / Kanker', 'value' => $anamnesa->tumor_benjolan],
        ['field' => 'epilepsi', 'label' => 'Epilepsi / Kejang', 'value' => $anamnesa->epilepsi],
        ['field' => 'cedera_kepala', 'label' => 'Cedera Kepala / Gegar Otak', 'value' => $anamnesa->cedera_kepala],
        ['field' => 'batuk_kronis', 'label' => 'Batuk Kronis', 'value' => $anamnesa->batuk_kronis],
        ['field' => 'gangguan_pencernaan', 'label' => 'Gangguan Pencernaan', 'value' => $anamnesa->gangguan_pencernaan],
        ['field' => 'gangguan_keseimbangan', 'label' => 'Gangguan Keseimbangan / Koordinasi', 'value' => $anamnesa->gangguan_keseimbangan],
        ['field' => 'claustrophobia', 'label' => 'Takut Ruang Sempit / Tertutup', 'value' => $anamnesa->claustrophobia],
        ['field' => 'takut_darah', 'label' => 'Takut Melihat Darah', 'value' => $anamnesa->takut_darah],
        ['field' => 'kacamata', 'label' => 'Kacamata / Alat Bantu Penglihatan', 'value' => $anamnesa->kacamata],
        ['field' => 'gagap', 'label' => 'Gagap / Gangguan Bicara', 'value' => $anamnesa->gagap],
        ['field' => 'alat_bantu_tulang', 'label' => 'Penyangga Tulang / Sendi', 'value' => $anamnesa->alat_bantu_tulang],
        ['field' => 'lemah_otot', 'label' => 'Lemah Otot / Gangguan Saraf', 'value' => $anamnesa->lemah_otot],
        ['field' => 'pikiran_bunuh_diri', 'label' => 'Pikiran / Percobaan Bunuh Diri', 'value' => $anamnesa->pikiran_bunuh_diri],
        ['field' => 'kelainan_darah', 'label' => 'Kelainan Darah', 'value' => $anamnesa->kelainan_darah],
        ['field' => 'riwayat_psikolog', 'label' => 'Riwayat Psikolog / Psikiater', 'value' => $anamnesa->riwayat_psikolog],
    ];
    $healthLetterUrl = $anamnesa->surat_kesehatan_path ? asset('storage/' . $anamnesa->surat_kesehatan_path) : null;
    $healthLetterName = $anamnesa->surat_kesehatan_path ? basename($anamnesa->surat_kesehatan_path) : null;
@endphp

<div class="student-page-shell">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h4 class="fw-bold mb-1">Hasil Tes Kesehatan</h4>
            <p class="text-muted mb-0">Berikut ringkasan anamnesa yang sudah Anda kirimkan.</p>
        </div>
        <div class="d-flex flex-column flex-sm-row gap-2">
            @include('dashboard.components.student-status-badge', [
                'label' => $statusMeta['label'],
                'tone' => $statusMeta['tone'],
                'icon' => $statusMeta['icon'],
            ])
            <a href="{{ route('dashboard.tes-kesehatan.anamnesa.cetak') }}" target="_blank" class="btn btn-primary">
                <i class="bx bx-printer me-1"></i> Cetak Form
            </a>
        </div>
    </div>

    <div class="student-summary-band">
        <div>
            <span>Nama Peserta</span>
            <strong>{{ $anamnesa->user->name ?? '-' }}</strong>
        </div>
        <div>
            <span>Tanggal Pengisian</span>
            <strong>{{ $anamnesa->tanggal_pengisian ? \Carbon\Carbon::parse($anamnesa->tanggal_pengisian)->translatedFormat('d F Y') : '-' }}</strong>
        </div>
        <div>
            <span>Status Pemeriksaan</span>
            <strong>{{ $statusMeta['label'] }}</strong>
        </div>
        <div>
            <span>Surat Kesehatan</span>
            <strong>
                {{ $anamnesa->surat_kesehatan_path ? 'Sudah Upload' : 'Belum Upload' }}
                @if($queue->isHealthLetterLate($anamnesa->surat_kesehatan_uploaded_at)) (Terlambat) @endif
            </strong>
            @if($healthLetterUrl)
                <a href="{{ $healthLetterUrl }}" target="_blank" rel="noopener" class="small d-inline-flex align-items-center gap-1 mt-1">
                    <i class="bx bx-show"></i> Lihat file
                </a>
            @endif
        </div>
    </div>

    <div class="student-assessment-card">
        <div class="student-assessment-card__head">
            <div>
                <span class="badge bg-label-warning mb-2">Surat Kesehatan</span>
                <h5 class="mb-1">Upload Surat Klinik/RS</h5>
                <p class="text-muted mb-0">
                    @if($queue->healthLetterDueAt())
                        Tenggat {{ $queue->healthLetterDueAt()->translatedFormat('d F Y H:i') }}. Upload setelah tenggat tetap diterima.
                    @else
                        Tenggat H+7 mulai dihitung setelah panitia menekan Diberitahukan.
                    @endif
                </p>
            </div>
        </div>
        @if($healthLetterUrl)
            <div class="alert alert-success d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div>
                    <strong>Surat kesehatan sudah diunggah.</strong>
                    <div class="small">
                        {{ $healthLetterName }}
                        @if($anamnesa->surat_kesehatan_uploaded_at)
                            - {{ $anamnesa->surat_kesehatan_uploaded_at->translatedFormat('d F Y H:i') }}
                        @endif
                    </div>
                </div>
                <a href="{{ $healthLetterUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success">
                    <i class="bx bx-show me-1"></i> Lihat File Surat
                </a>
            </div>
        @endif
        <form method="POST" action="{{ route('dashboard.tes-kesehatan.surat.store') }}" enctype="multipart/form-data" class="d-flex flex-column flex-md-row gap-2">
            @csrf
            <input type="file" name="surat_kesehatan" class="form-control @error('surat_kesehatan') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
            <button class="btn btn-primary flex-shrink-0" type="submit"><i class="bx bx-upload me-1"></i> {{ $healthLetterUrl ? 'Ganti Surat' : 'Upload Surat' }}</button>
        </form>
        @error('surat_kesehatan')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
    </div>

    <div class="student-assessment-card">
        <div class="student-assessment-card__head">
            <div>
                <span class="badge bg-label-info mb-2">Anamnesa</span>
                <h5 class="mb-1">Jawaban Riwayat Kesehatan</h5>
                <p class="text-muted mb-0">Jawaban Ya ditandai lebih menonjol agar mudah diperiksa kembali.</p>
            </div>
        </div>

        <div class="student-answer-grid">
            @foreach($items as $item)
                @php($note = data_get($anamnesa, "{$item['field']}_keterangan"))
                <div class="student-answer-item {{ $item['value'] ? 'is-yes' : 'is-no' }}">
                    <span>{{ $item['label'] }}</span>
                    <strong>{{ $item['value'] ? 'Ya' : 'Tidak' }}</strong>
                    @if($item['value'] && $note)
                        <small class="text-muted d-block mt-2">{{ $note }}</small>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="student-answer-note mt-3">
            <span>Keterangan Tambahan</span>
            <p>{{ $anamnesa->keterangan ?: '-' }}</p>
        </div>

        @if($anamnesa->surat_kesehatan_path)
            <div class="student-answer-note mt-3">
                <span>Surat Kesehatan Klinik/RS</span>
                <p class="mb-0">
                    <a href="{{ asset('storage/' . $anamnesa->surat_kesehatan_path) }}" target="_blank" rel="noopener">
                        Lihat surat kesehatan yang diunggah
                    </a>
                </p>
            </div>
        @endif
    </div>

    @if ($anamnesa->pemeriksaan)
        <div class="student-assessment-card">
            <div class="student-assessment-card__head">
                <div>
                    <span class="badge bg-label-success mb-2">Pemeriksaan Petugas</span>
                    <h5 class="mb-1">Catatan Pemeriksaan</h5>
                </div>
            </div>
            <div class="student-summary-band mb-0">
                <div>
                    <span>Pemeriksa</span>
                    <strong>{{ $anamnesa->pemeriksaan->nama_pemeriksa ?? '-' }}</strong>
                </div>
                <div>
                    <span>Rekomendasi</span>
                    <strong>{{ ucfirst(str_replace('_', ' ', $anamnesa->pemeriksaan->rekomendasi ?? '-')) }}</strong>
                </div>
                <div>
                    <span>Catatan</span>
                    <strong>{{ $anamnesa->pemeriksaan->catatan ?? '-' }}</strong>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
