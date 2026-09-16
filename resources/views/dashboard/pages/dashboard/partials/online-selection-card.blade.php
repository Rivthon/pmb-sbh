@if($hasQueue && $isOnlineSelection)
@php
    $steps = $latestQueue->stageSteps->keyBy(fn ($step) => $step->stage?->key);
    $interviewStep = $steps->get(\App\Models\PmbQueueStage::KEY_WAWANCARA_KAPRODI);
    $healthDue = $latestQueue->healthLetterDueAt();
    $anamnesa = \App\Models\TesKesehatanAnamnesa::where('user_id', $user->id)->first();
    $wawancara = \App\Models\WawancaraPmb::where('calon_mahasiswa_id', $user->id)->first();
    $onlineProgress = [
        [
            'label' => 'Tes Tulis',
            'status' => !$latestQueue->requiresTesTulis() ? 'Dilewati' : (($steps->get(\App\Models\PmbQueueStage::KEY_TES_TULIS)?->status === \App\Models\PmbQueueStageStep::STATUS_COMPLETED) ? 'Selesai' : 'Belum Selesai'),
            'icon' => 'bx-edit-alt',
        ],
        [
            'label' => 'Anamnesa',
            'status' => $anamnesa?->tanggal_pengisian ? 'Selesai' : 'Belum Diisi',
            'icon' => 'bx-plus-medical',
        ],
        [
            'label' => 'Surat Kesehatan',
            'status' => $anamnesa?->surat_kesehatan_path ? 'Sudah Upload' : 'Belum Upload',
            'icon' => 'bx-file',
        ],
        [
            'label' => 'Form Wawancara',
            'status' => $wawancara && $wawancara->status !== 'draft' ? 'Sudah Dikirim' : 'Belum Diisi',
            'icon' => 'bx-conversation',
        ],
        [
            'label' => 'Review Dosen',
            'status' => in_array($wawancara?->status, ['reviewed', 'locked'], true) ? 'Selesai' : 'Belum Selesai',
            'icon' => 'bx-user-check',
        ],
    ];
    $scheduleItems = [
        [
            'label' => 'Tes Tulis Online',
            'value' => optional($latestQueue->session->online_test_starts_at)->translatedFormat('d F Y H:i') ?? 'Belum dijadwalkan',
            'meta' => 'Tutup ' . (optional($latestQueue->session->online_test_ends_at)->translatedFormat('d F Y H:i') ?? 'sesuai arahan panitia'),
            'icon' => 'bx-edit-alt',
            'tone' => 'primary',
        ],
        [
            'label' => 'Kesehatan Online',
            'value' => optional($latestQueue->session->health_starts_at)->translatedFormat('d F Y H:i') ?? 'Belum dijadwalkan',
            'meta' => 'Tutup ' . ($healthDue?->translatedFormat('d F Y H:i') ?? 'menunggu pemberitahuan'),
            'icon' => 'bx-plus-medical',
            'tone' => 'info',
            'late' => $latestQueue->isHealthLetterLate($anamnesa?->surat_kesehatan_uploaded_at),
        ],
        [
            'label' => 'Wawancara Online',
            'value' => optional($latestQueue->session->interview_starts_at)->translatedFormat('d F Y H:i') ?? 'Belum dijadwalkan',
            'meta' => 'Tutup ' . (optional($latestQueue->session->interview_ends_at)->translatedFormat('d F Y H:i') ?? 'sesuai arahan panitia'),
            'icon' => 'bx-conversation',
            'tone' => 'warning',
        ],
        [
            'label' => 'Dosen / Breakout',
            'value' => ($interviewStep?->officer?->name ?? 'Belum ditentukan') . ' / ' . ($interviewStep?->room?->name ?? '-'),
            'meta' => 'Urutan ' . ($interviewStep?->stage_queue_number ?? '-'),
            'icon' => 'bx-user-voice',
            'tone' => 'secondary',
        ],
    ];
@endphp

<div class="card selection-card" id="seleksi-tes-pmb">
    <div class="card-body">
        <div class="selection-card-hero mb-4">
            <div class="selection-card-title">
                <span class="selection-card-icon">
                    <i class="bx bx-laptop"></i>
                </span>
                <div>
                    <span class="badge bg-label-primary mb-2">Seleksi Online</span>
                    <h5 class="mb-1">{{ $latestQueue->session->name }}</h5>
                    <p class="text-muted mb-0">Jalur {{ $latestQueue->testFlowLabel() }}. Tidak memakai QR, nomor antrean, atau check-in kampus.</p>
                </div>
            </div>
            <span class="badge bg-label-{{ $latestQueue->overall_status === \App\Models\PmbOfflineQueue::OVERALL_COMPLETED ? 'success' : 'info' }} align-self-start">
                <i class="bx {{ $latestQueue->overall_status === \App\Models\PmbOfflineQueue::OVERALL_COMPLETED ? 'bx-check-circle' : 'bx-time-five' }} me-1"></i>
                {{ $latestQueue->overall_status === \App\Models\PmbOfflineQueue::OVERALL_COMPLETED ? 'Seleksi Lengkap' : 'Sedang Berjalan' }}
            </span>
        </div>

        <div class="selection-info-grid mb-4">
            @foreach($scheduleItems as $item)
                <div class="selection-info-card">
                    <span class="selection-mini-icon bg-label-{{ $item['tone'] }} text-{{ $item['tone'] }}">
                        <i class="bx {{ $item['icon'] }}"></i>
                    </span>
                    <div>
                        <span class="text-muted small">{{ $item['label'] }}</span>
                        <div class="fw-semibold text-body">{{ $item['value'] }}</div>
                        <div class="small text-muted">{{ $item['meta'] }}</div>
                        @if(!empty($item['late']))
                            <span class="badge bg-label-danger mt-1">Terlambat</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="selection-progress-grid mb-4">
            @foreach($onlineProgress as $item)
                @php
                    $statusTone = in_array($item['status'], ['Selesai', 'Sudah Upload', 'Sudah Dikirim', 'Dilewati'], true) ? 'success' : 'warning';
                @endphp
                <div class="selection-progress-card">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                        <span class="selection-mini-icon bg-label-{{ $statusTone }} text-{{ $statusTone }}">
                            <i class="bx {{ $item['icon'] }}"></i>
                        </span>
                        <i class="bx {{ $statusTone === 'success' ? 'bx-check-circle text-success' : 'bx-time-five text-warning' }}"></i>
                    </div>
                    <div class="small text-muted">{{ $item['label'] }}</div>
                    <div class="fw-semibold">{{ $item['status'] }}</div>
                </div>
            @endforeach
        </div>

        @unless($latestQueue->session->isOnlineSelectionOpen())
            <div class="alert alert-info d-flex align-items-start gap-2">
                <i class="bx bx-info-circle fs-5 mt-1"></i>
                <div>Tes tulis online dibuka {{ optional($latestQueue->session->online_test_starts_at)->translatedFormat('d F Y H:i') ?? 'sesuai jadwal panitia' }}.</div>
            </div>
        @endunless

        <div class="selection-action-row">
            @if($latestQueue->requiresTesTulis() && $latestQueue->session?->tes_tulis_id)
                <a class="btn btn-outline-primary" href="{{ route('dashboard.user.tes-tulis.index') }}">
                    <i class="bx bx-edit-alt"></i>
                    Tes Tulis
                </a>
            @endif
            <a class="btn btn-outline-primary" href="{{ route('dashboard.tes-kesehatan.anamnesa.create') }}">
                <i class="bx bx-plus-medical"></i>
                Anamnesa & Surat
            </a>
            <a class="btn btn-outline-primary" href="{{ route('dashboard.wawancara.index') }}">
                <i class="bx bx-conversation"></i>
                Form Wawancara
            </a>
            @if($latestQueue->session->isZoomVisible())
                <a class="btn btn-primary" href="{{ $latestQueue->session->zoom_url }}" target="_blank" rel="noopener">
                    <i class="bx bx-video"></i>
                    Masuk Zoom
                </a>
            @endif
        </div>
    </div>
</div>

<nav class="selection-mobile-actions" aria-label="Aksi cepat seleksi online">
    @if($latestQueue->requiresTesTulis() && $latestQueue->session?->tes_tulis_id)
        <a class="btn btn-outline-primary" href="{{ route('dashboard.user.tes-tulis.index') }}">
            <i class="bx bx-edit-alt"></i>
            Tes Tulis
        </a>
    @endif
    <a class="btn btn-outline-primary" href="{{ route('dashboard.tes-kesehatan.anamnesa.create') }}">
        <i class="bx bx-plus-medical"></i>
        Kesehatan
    </a>
    <a class="btn btn-outline-primary" href="{{ route('dashboard.wawancara.index') }}">
        <i class="bx bx-conversation"></i>
        Wawancara
    </a>
    @if($latestQueue->session->isZoomVisible())
        <a class="btn btn-primary" href="{{ $latestQueue->session->zoom_url }}" target="_blank" rel="noopener">
            <i class="bx bx-video"></i>
            Zoom
        </a>
    @endif
</nav>
@endif
