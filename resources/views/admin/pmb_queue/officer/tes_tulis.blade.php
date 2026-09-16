@extends('admin_dashboard.layout.master')

@section('title', 'Petugas Tes Tulis')

@section('content')
<div class="pmb-officer-page pmb-written-test-page">
    <div class="pmb-officer-hero d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
        <div>
            <span class="badge bg-label-primary mb-2">{{ $session->code }}</span>
            <h4 class="fw-bold mb-1">Petugas Tes Tulis</h4>
            <p class="text-muted mb-0">{{ $session->name }} @if($session->starts_at) - {{ $session->starts_at->translatedFormat('d M Y H:i') }} @endif</p>
        </div>
        <div class="pmb-officer-actions d-flex flex-wrap gap-2">
            <a href="{{ route('admin.pmb-queues.board', $session) }}" target="_blank" class="btn btn-dark">
                <i class="bx bx-tv me-1"></i> TV
            </a>
            <a href="{{ route('admin.pmb-queues.written-test.sessions') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Pilih Sesi
            </a>
        </div>
    </div>

    <div class="row g-2 g-md-3 mb-4 pmb-officer-stats">
        <div class="col-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Menunggu</div>
                    <div class="h3 mb-0">{{ $stats['waiting'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Sedang Tes Tulis</div>
                    <div class="h3 mb-0">{{ $stats['processing'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">Selesai</div>
                    <div class="h3 mb-0">{{ $stats['completed'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card pmb-officer-panel">
        <div class="card-header d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div>
                <h5 class="mb-1">Peserta Tes Tulis Aktif</h5>
                <p class="text-muted mb-0">Peserta check-in otomatis masuk Tes Tulis. Penyelesaian bisa per orang atau sekaligus.</p>
            </div>
            @if($processingSteps->isNotEmpty())
            <form method="POST" action="{{ route('admin.pmb-queues.bulk-status', $session) }}" onsubmit="return confirm('Selesaikan Tes Tulis untuk semua peserta aktif?')">
                @csrf
                <input type="hidden" name="action" value="complete_test_tulis">
                @foreach($processingSteps as $step)
                <input type="hidden" name="queue_ids[]" value="{{ $step->queue_id }}">
                @endforeach
                <button type="submit" class="btn btn-primary pmb-bulk-complete-btn">
                    <i class="bx bx-check-double me-1"></i> Selesaikan Semua
                </button>
            </form>
            @endif
        </div>
        <div class="table-responsive d-none d-md-block">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nomor</th>
                        <th>Peserta</th>
                        <th>Prodi</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($processingSteps as $step)
                    <tr>
                        <td class="fw-semibold">{{ $step->queue->queue_code ?? $step->stage_queue_code ?? '-' }}</td>
                        <td>
                            <div class="fw-semibold">{{ $step->queue->user->name ?? '-' }}</div>
                        </td>
                        <td>{{ $step->queue->user->jurusan->nama_jurusan ?? '-' }}</td>
                        <td><span class="badge bg-label-{{ $step->statusTone() }}">{{ $step->statusLabel() }}</span></td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.pmb-queues.queue-status', $step->queue) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="action" value="complete_test_tulis">
                                <input type="hidden" name="step_id" value="{{ $step->id }}">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bx bx-check me-1"></i> Selesai
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">Belum ada peserta Tes Tulis aktif.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pmb-written-mobile-list d-md-none">
            @forelse($processingSteps as $step)
            <div class="pmb-written-mobile-item">
                <div class="pmb-written-mobile-top">
                    <span class="badge bg-label-primary">{{ $step->queue->queue_code ?? $step->stage_queue_code ?? '-' }}</span>
                    <span class="badge bg-label-{{ $step->statusTone() }}">{{ $step->statusLabel() }}</span>
                </div>
                <div class="pmb-written-mobile-name">{{ $step->queue->user->name ?? '-' }}</div>
                <div class="pmb-written-mobile-prodi">{{ $step->queue->user->jurusan->nama_jurusan ?? '-' }}</div>
                <form method="POST" action="{{ route('admin.pmb-queues.queue-status', $step->queue) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="action" value="complete_test_tulis">
                    <input type="hidden" name="step_id" value="{{ $step->id }}">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-check me-1"></i> Selesai
                    </button>
                </form>
            </div>
            @empty
            <div class="text-center text-muted py-5 px-3">Belum ada peserta Tes Tulis aktif.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
