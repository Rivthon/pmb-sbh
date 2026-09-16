@extends('admin_dashboard.layout.master')

@section('title', 'Meja Wawancara PMB')

@section('content')
@php
    $participant = $currentStep?->queue?->user;
    $queueCode = $currentStep?->queue?->queue_code ?? $currentStep?->stage_queue_code ?? '-';
    $roomName = $currentStep?->room?->name ?? $interviewRoom?->name ?? 'Meja belum dipasangkan';
    $programLabel = auth('admin')->user()->jurusan?->nama_jurusan ?? 'prodi Anda';
    $anamnesaAlerts = collect(\App\Models\TesKesehatanAnamnesa::officerAnamnesaQuestions())
        ->filter(fn ($question) => data_get($healthAnamnesa, $question['field']) === 1 || data_get($healthAnamnesa, $question['field']) === true)
        ->map(function ($question) use ($healthAnamnesa) {
            $field = $question['field'];
            $question['note'] = data_get($healthAnamnesa, "{$field}_keterangan");

            return $question;
        })
        ->values();
@endphp

<style>
    .interview-hero {
        border-left: 4px solid #696cff;
        background: #fff;
        padding: 18px 20px;
    }
    .interview-code {
        font-size: clamp(34px, 4vw, 54px);
        line-height: 1;
        font-weight: 800;
        color: #30336b;
    }
    .interview-guide {
        display: grid;
        gap: 12px;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .interview-guide li {
        display: grid;
        grid-template-columns: 34px 1fr;
        gap: 10px;
        align-items: start;
    }
    .interview-guide-icon {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        color: #696cff;
        background: #eef0ff;
        font-size: 18px;
    }
    .interview-notes { min-height: 180px; resize: vertical; }
    .stat-value { font-size: 28px; line-height: 1; font-weight: 800; }
</style>

<div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
    <div>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <span class="badge bg-label-primary">{{ $session->code }}</span>
            <span class="badge bg-label-secondary">{{ $roomName }}</span>
        </div>
        <h4 class="fw-bold mb-1">Wawancara Offline PMB</h4>
        <p class="text-muted mb-0">{{ auth('admin')->user()->name }} sebagai dosen pewawancara {{ $programLabel }}.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('admin.pmb-queues.interview.offline.sessions') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Sesi Offline
        </a>
        <a href="{{ route('admin.pmb-queues.board', $session) }}" target="_blank" class="btn btn-dark">
            <i class="bx bx-tv me-1"></i> Buka Layar TV
        </a>
    </div>
</div>

@foreach(['success' => 'success', 'error' => 'danger', 'info' => 'info'] as $key => $tone)
    @if(session($key))
    <div class="alert alert-{{ $tone }} alert-dismissible fade show" role="alert">
        {{ session($key) }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
    @endif
@endforeach

@if($errors->any())
<div class="alert alert-danger">
    <div class="fw-semibold mb-1">Hasil wawancara belum dapat disimpan.</div>
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center justify-content-between gap-3">
                <div>
                    <div class="text-muted small mb-1">Sisa {{ $programLabel }}</div>
                    <div class="stat-value">{{ $stats['remaining'] }}</div>
                    <div class="text-muted small mt-1">dari {{ $stats['total_program'] }} peserta</div>
                </div>
                <i class="bx bx-user-voice fs-2 text-info"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center justify-content-between gap-3">
                <div>
                    <div class="text-muted small mb-1">Menunggu Dipanggil</div>
                    <div class="stat-value">{{ $stats['waiting'] }}</div>
                </div>
                <i class="bx bx-time-five fs-2 text-warning"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center justify-content-between gap-3">
                <div>
                    <div class="text-muted small mb-1">Sedang Wawancara</div>
                    <div class="stat-value">{{ $stats['processing'] }}</div>
                </div>
                <i class="bx bx-conversation fs-2 text-primary"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center justify-content-between gap-3">
                <div>
                    <div class="text-muted small mb-1">Selesai</div>
                    <div class="stat-value">{{ $stats['completed'] }}</div>
                </div>
                <i class="bx bx-check-circle fs-2 text-success"></i>
            </div>
        </div>
    </div>
</div>

@if(!$currentStep)
<div class="card">
    <div class="card-body text-center py-5">
        @if($configurationError)
            <i class="bx bx-error-circle fs-1 text-warning mb-3"></i>
            <h5 class="mb-2">Meja wawancara belum siap</h5>
            <p class="text-muted mb-0">{{ $configurationError }}</p>
        @else
            <i class="bx bx-user-check fs-1 text-primary mb-3"></i>
            <h5 class="mb-2">Siap Memulai Wawancara Offline</h5>
            <p class="text-muted mb-4">Klik mulai saat meja kosong. Sistem akan otomatis memanggil peserta paling awal dari {{ $programLabel }} untuk sesi ini.</p>
            <form method="POST" action="{{ route('admin.pmb-queues.officer.wawancara.ready', $session) }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bx bx-volume-full me-1"></i> Mulai Wawancara Offline
                </button>
            </form>
        @endif
    </div>
</div>
@else
<div class="interview-hero rounded mb-4">
    <div class="row g-3 align-items-center">
        <div class="col-lg-8">
            <div class="text-muted small text-uppercase mb-2">Peserta sedang diwawancara</div>
            <div class="interview-code mb-2">{{ $queueCode }}</div>
            <h4 class="fw-bold mb-1">{{ $participant?->name ?? '-' }}</h4>
            <div class="text-muted">{{ $participant?->jurusan?->nama_jurusan ?? '-' }}</div>
        </div>
        <div class="col-lg-4 text-lg-end">
            <span class="badge bg-label-{{ $currentStep->statusTone() }} mb-2">{{ $currentStep->statusLabel() }}</span>
            <div class="fw-semibold">{{ $roomName }}</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Data Peserta</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small">Nama</div>
                    <div class="fw-semibold">{{ $participant?->name ?? '-' }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Program Studi</div>
                    <div class="fw-semibold">{{ $participant?->jurusan?->nama_jurusan ?? '-' }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Asal Sekolah</div>
                    <div class="fw-semibold">{{ $participant?->asal_sekolah ?: '-' }}</div>
                </div>
                <div>
                    <div class="text-muted small">Email</div>
                    <div class="text-break">{{ $participant?->email ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Catatan Kesehatan</h5>
            </div>
            <div class="card-body">
                @if($healthExam)
                    <div class="mb-3">
                        <div class="text-muted small">Hasil Tes Kesehatan</div>
                        <div class="fw-semibold text-break">{{ $healthExam->healthSummary() }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">Rekomendasi Kesehatan</div>
                        <span class="badge bg-label-{{
                            $healthExam->rekomendasi === 'layak'
                                ? 'success'
                                : ($healthExam->rekomendasi === 'tidak layak' ? 'danger' : 'warning')
                        }}">
                            {{ $healthExam->recommendationLabel() }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small mb-2">Ringkasan Anamnesa</div>
                        @if($anamnesaAlerts->isNotEmpty())
                            <div class="d-grid gap-2">
                                @foreach($anamnesaAlerts as $alert)
                                    <div class="border rounded p-2">
                                        <div class="fw-semibold small">{{ $alert['label'] }}</div>
                                        <div class="text-muted small text-break">{{ $alert['note'] ?: 'Tanpa keterangan tambahan.' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-muted small">Tidak ada catatan anamnesa dengan jawaban Ya.</div>
                        @endif
                    </div>
                    <div>
                        <div class="text-muted small">Pemeriksa</div>
                        <div>{{ $healthExam->nama_pemeriksa ?: '-' }}</div>
                        <div class="text-muted small">{{ $healthExam->tanggal_pemeriksaan ? \Carbon\Carbon::parse($healthExam->tanggal_pemeriksaan)->translatedFormat('d M Y') : '-' }}</div>
                    </div>
                @else
                    <div class="text-muted">Catatan pemeriksaan kesehatan belum tersedia.</div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Fokus Wawancara</h5>
            </div>
            <div class="card-body">
                <ul class="interview-guide">
                    <li>
                        <span class="interview-guide-icon"><i class="bx bx-bulb"></i></span>
                        <div><div class="fw-semibold">Motivasi Kuliah</div><div class="text-muted small">Alasan memilih kampus dan program studi.</div></div>
                    </li>
                    <li>
                        <span class="interview-guide-icon"><i class="bx bx-book-reader"></i></span>
                        <div><div class="fw-semibold">Kesiapan Belajar</div><div class="text-muted small">Komitmen, dukungan keluarga, dan kesiapan studi.</div></div>
                    </li>
                    <li>
                        <span class="interview-guide-icon"><i class="bx bx-message-rounded-dots"></i></span>
                        <div><div class="fw-semibold">Komunikasi</div><div class="text-muted small">Kemampuan menjawab dan menyampaikan pendapat.</div></div>
                    </li>
                    <li>
                        <span class="interview-guide-icon"><i class="bx bx-target-lock"></i></span>
                        <div><div class="fw-semibold">Kesesuaian Prodi</div><div class="text-muted small">Kecocokan minat dan karakter dengan program studi.</div></div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        @if($wawancara && $wawancara->status === 'submitted')
        <form method="POST" action="{{ route('admin.wawancara.review', $wawancara) }}">
            @csrf
            <input type="hidden" name="queue_uuid" value="{{ $currentStep->queue->uuid }}">

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-1">Hasil Wawancara</h5>
                    <p class="text-muted mb-0">Catat kesimpulan dosen dan tentukan rekomendasi peserta. Setelah submit, sistem akan mengambil peserta berikutnya jika tersedia.</p>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label for="kesimpulan_kaprodi" class="form-label fw-semibold">Catatan dan Kesimpulan</label>
                        <textarea
                            id="kesimpulan_kaprodi"
                            name="kesimpulan_kaprodi"
                            class="form-control interview-notes @error('kesimpulan_kaprodi') is-invalid @enderror"
                            minlength="10"
                            required
                            placeholder="Tuliskan motivasi, kesiapan, catatan penting, dan kesimpulan wawancara"
                        >{{ old('kesimpulan_kaprodi', $wawancara->kesimpulan_kaprodi) }}</textarea>
                        @error('kesimpulan_kaprodi')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="rekomendasi" class="form-label fw-semibold">Rekomendasi Dosen</label>
                        <select id="rekomendasi" name="rekomendasi" class="form-select @error('rekomendasi') is-invalid @enderror" required>
                            <option value="">Pilih rekomendasi</option>
                            <option value="direkomendasikan" @selected(old('rekomendasi', $wawancara->rekomendasi) === 'direkomendasikan')>Direkomendasikan</option>
                            <option value="direkomendasikan_bersyarat" @selected(old('rekomendasi', $wawancara->rekomendasi) === 'direkomendasikan_bersyarat')>Direkomendasikan Bersyarat</option>
                            <option value="tidak_direkomendasikan" @selected(old('rekomendasi', $wawancara->rekomendasi) === 'tidak_direkomendasikan')>Tidak Direkomendasikan</option>
                        </select>
                        @error('rekomendasi')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-primary d-flex gap-2" role="alert">
                        <i class="bx bx-info-circle fs-4"></i>
                        <div>Submit hasil akan menyelesaikan wawancara peserta ini. Jika masih ada peserta menunggu di {{ $programLabel }}, sistem otomatis memanggil peserta berikutnya ke meja Anda.</div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bx bx-check me-1"></i> Submit & Panggil Berikutnya
                        </button>
                    </div>
                </div>
            </div>
        </form>
        @elseif($wawancara && $wawancara->status === 'reviewed')
        <div class="alert alert-success">
            Hasil peserta sudah tersimpan. Sistem sedang menyiapkan peserta berikutnya.
        </div>
        @elseif($wawancara && $wawancara->status === 'locked')
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bx bx-lock-alt fs-1 text-primary mb-3"></i>
                <h5>Hasil wawancara sudah dikunci</h5>
                <a href="{{ route('admin.wawancara.show', $wawancara) }}" class="btn btn-outline-primary mt-2">Lihat Hasil</a>
            </div>
        </div>
        @else
        <div class="alert alert-warning">Form hasil wawancara belum tersedia untuk peserta ini.</div>
        @endif
    </div>
</div>
@endif

@if(!$currentStep && !$configurationError)
<script>
    window.setTimeout(() => window.location.reload(), 15000);
</script>
@endif
@endsection
