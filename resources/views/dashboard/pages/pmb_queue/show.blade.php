@extends('dashboard.layout.master')

@section('title', 'Kartu Tes PMB')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card overflow-hidden">
        <div class="card-header bg-primary text-white d-flex flex-column flex-md-row justify-content-between gap-3">
            <div>
                <h4 class="mb-1 text-white">Kartu Tes Offline PMB</h4>
                <p class="mb-0 opacity-75">{{ $queue->session->name }}</p>
            </div>
            <a href="{{ route('dashboard.pmb-queue.print', $queue) }}" target="_blank" class="btn btn-light text-primary fw-semibold">
                <i class="bx bx-printer me-1"></i> Cetak Kartu
            </a>
        </div>
        <div class="card-body">
            @php
                $checkInUrl = $queue->signedCheckInUrl();
                $isQueueCompleted = $queue->status === \App\Models\PmbOfflineQueue::STATUS_COMPLETED
                    || $queue->overall_status === \App\Models\PmbOfflineQueue::OVERALL_COMPLETED;
                $currentStep = $queue->stageSteps->firstWhere('stage_id', $queue->current_stage_id);
                $lastCompletedStep = $queue->stageSteps
                    ->where('status', \App\Models\PmbQueueStageStep::STATUS_COMPLETED)
                    ->sortByDesc(fn ($step) => $step->stage->sort_order ?? 0)
                    ->first();
                try {
                    $qrDataUri = class_exists(\chillerlan\QRCode\QRCode::class)
                        ? (new \chillerlan\QRCode\QRCode())->render($checkInUrl)
                        : 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($checkInUrl);
                } catch (\Throwable $e) {
                    $qrDataUri = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($checkInUrl);
                }
            @endphp
            <div class="row g-4 align-items-center">
                <div class="col-md-4 text-center">
                    <div class="border rounded p-3 bg-light">
                        @if($qrDataUri)
                            <img src="{{ $qrDataUri }}" alt="QR Kartu Tes PMB" class="img-fluid mb-3" style="max-width: 200px; border-radius: 8px;">
                        @else
                            <div class="p-3 mb-3 border rounded bg-white text-muted small text-break">
                                {{ $checkInUrl }}
                            </div>
                        @endif
                        <div class="display-4 fw-bold text-primary">{{ $queue->queue_code ?? 'QR' }}</div>
                        <div class="text-muted small mt-2">Tunjukkan kartu ini kepada panitia saat tiba di kampus.</div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="text-muted small">Nama</div>
                            <div class="fw-semibold">{{ $queue->user->name }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Program Studi</div>
                            <div class="fw-semibold">{{ $queue->user->jurusan->nama_jurusan ?? '-' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Tanggal Tes</div>
                            <div class="fw-semibold">{{ optional($queue->session->starts_at)->translatedFormat('d F Y') ?? '-' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Mulai Tes</div>
                            <div class="fw-semibold">{{ optional($queue->session->starts_at)->format('H:i') ? optional($queue->session->starts_at)->format('H:i') . ' WIB - sampai selesai' : '-' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Status</div>
                            <span class="badge bg-label-{{ $queue->statusTone() }}">{{ $queue->statusLabel() }}</span>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Jalur Tes</div>
                            <span class="badge bg-label-{{ $queue->requiresTesTulis() ? 'primary' : 'info' }}">{{ $queue->testFlowLabel() }}</span>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Tahap Saat Ini</div>
                            <div class="fw-semibold">
                                @if($isQueueCompleted)
                                    Selesai semua tahap
                                @else
                                    {{ $queue->currentStage->name ?? 'Menunggu kedatangan' }}
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Lokasi/Loket Tujuan</div>
                            <div class="fw-semibold">
                                @if($isQueueCompleted)
                                    {{ $lastCompletedStep?->room?->name ?? 'Selesai' }}
                                @else
                                    {{ $queue->room->name ?? $currentStep?->room?->name ?? '-' }}
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">Kode Validasi</div>
                            <div class="font-monospace small border rounded p-2 bg-light text-break">{{ $queue->uuid }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Tes yang Akan Diikuti</h5>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4">
                @foreach($queue->plannedTestFlowItems() as $item)
                    <div class="col-md-3 col-6">
                        <div class="border rounded p-3 h-100 {{ $item['label'] === 'Tes Tulis' && !$queue->requiresTesTulis() ? 'bg-label-info' : 'bg-light' }}">
                            <div class="fw-semibold">{{ $item['label'] }}</div>
                            <span class="badge bg-label-{{ $item['tone'] }}">{{ $item['status'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <h5 class="mb-3">Progress Tahapan PMB Offline</h5>
            <div class="row g-3">
                <div class="col-md-2 col-6">
                    <div class="border rounded p-3 h-100 bg-light">
                        <div class="fw-semibold">Kedatangan</div>
                        <span class="badge bg-label-{{ $queue->checked_in_at ? 'success' : 'secondary' }}">
                            {{ $queue->checked_in_at ? 'Sudah Hadir' : 'Belum Hadir' }}
                        </span>
                    </div>
                </div>
                @forelse($queue->stageSteps->sortBy(fn ($step) => $step->stage->sort_order ?? 999) as $step)
                    <div class="col-md-2 col-6">
                        <div class="border rounded p-3 h-100 {{ $queue->current_stage_id === $step->stage_id ? 'border-primary' : '' }}">
                            <div class="fw-semibold">{{ $step->stage->name ?? '-' }}</div>
                            <span class="badge bg-label-{{ $step->statusTone() }}">{{ $step->statusLabel() }}</span>
                            <div class="small text-muted mt-2">
                                Nomor: {{ $queue->queue_code ?? '-' }}<br>
                                Lokasi: {{ $step->room->name ?? '-' }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-md-8">
                        <div class="border rounded p-3 text-muted">Tahapan akan tampil setelah kedatangan dicatat panitia.</div>
                    </div>
                @endforelse
                <div class="col-md-2 col-6">
                    <div class="border rounded p-3 h-100 bg-light">
                        <div class="fw-semibold">Selesai</div>
                        <span class="badge bg-label-{{ $queue->overall_status === \App\Models\PmbOfflineQueue::OVERALL_COMPLETED ? 'success' : 'secondary' }}">
                            {{ $queue->overall_status === \App\Models\PmbOfflineQueue::OVERALL_COMPLETED ? 'Selesai Semua Tahap' : 'Dalam Proses' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-4 mb-0">
                Ikuti arahan panitia sesuai tahap yang sedang aktif. Jika status ditahan atau bermasalah, hubungi panitia PMB.
            </div>
        </div>
    </div>
</div>
@endsection
