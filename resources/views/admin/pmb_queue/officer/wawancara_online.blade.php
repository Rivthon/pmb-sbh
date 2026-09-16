@extends('admin_dashboard.layout.master')

@section('title', 'Wawancara Online')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Wawancara Online</h4>
        <p class="text-muted mb-0">{{ $session->name }}. Pilih peserta yang sudah mengirim form dan sesuai program studi Anda.</p>
    </div>
    <a href="{{ route('admin.pmb-queues.interview.online.sessions') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Sesi Online</a>
</div>

@if(!$admin->jurusan_id)
<div class="alert alert-warning d-flex gap-2" role="alert">
    <i class="bx bx-error-circle fs-4"></i>
    <div><strong>Program studi dosen belum diatur.</strong> Hubungi admin PMB agar peserta online sesuai prodi dapat muncul.</div>
</div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Urut</th><th>Peserta</th><th>Dosen</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @forelse($steps as $step)
                @php
                    $wawancara = $wawancaraByUser->get($step->queue->user_id);
                    $claimedByCurrentLecturer = (int) $step->assigned_officer_id === (int) auth('admin')->id();
                    $label = match (true) {
                        !$wawancara || $wawancara->status === 'draft' => 'Belum Mengisi Form',
                        !$step->assigned_officer_id && $wawancara->status === 'submitted' => 'Belum Dipilih Dosen',
                        $step->status === \App\Models\PmbQueueStageStep::STATUS_REENTRY => 'Masuk Kembali',
                        $step->status === \App\Models\PmbQueueStageStep::STATUS_PROCESSING => 'Sedang Wawancara',
                        $step->status === \App\Models\PmbQueueStageStep::STATUS_COMPLETED => 'Selesai',
                        default => 'Siap Wawancara',
                    };
                @endphp
                <tr>
                    <td><span class="badge bg-label-primary">{{ $step->stage_queue_number ?? '-' }}</span></td>
                    <td><div class="fw-semibold">{{ $step->queue->user->name ?? '-' }}</div><div class="text-muted small">{{ $step->queue->user->jurusan->nama_jurusan ?? '-' }}</div></td>
                    <td>{{ $step->officer?->name ?? 'Belum dipilih' }}</td>
                    <td><span class="badge bg-label-{{ $step->statusTone() }}">{{ $label }}</span></td>
                    <td class="text-end">
                        <div class="d-flex flex-wrap justify-content-end gap-1">
                            @if($wawancara && $wawancara->status === 'submitted' && (!$step->assigned_officer_id || $claimedByCurrentLecturer) && in_array($step->status, [\App\Models\PmbQueueStageStep::STATUS_WAITING, \App\Models\PmbQueueStageStep::STATUS_REENTRY], true))
                                <form method="POST" action="{{ route('admin.pmb-queues.online.interview-start', $step->queue) }}">@csrf<button class="btn btn-sm btn-primary">Mulai & Review</button></form>
                            @endif
                            @if($wawancara && $wawancara->status === 'submitted' && $claimedByCurrentLecturer && $step->status === \App\Models\PmbQueueStageStep::STATUS_PROCESSING)
                                <a class="btn btn-sm btn-primary" href="{{ route('admin.wawancara.review.form', ['wawancara' => $wawancara, 'queue_uuid' => $step->queue->uuid]) }}">Review</a>
                            @endif
                            @if($wawancara && in_array($wawancara->status, ['reviewed', 'locked'], true) && $claimedByCurrentLecturer)
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.wawancara.show', $wawancara) }}">Lihat Hasil</a>
                            @endif
                            @if($wawancara && $wawancara->status === 'reviewed' && $claimedByCurrentLecturer)
                                <form method="POST" action="{{ route('admin.pmb-queues.online.interview-reentry', $step->queue) }}">@csrf<button class="btn btn-sm btn-outline-warning">Izinkan Masuk Kembali</button></form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-5">Belum ada peserta online yang sudah mengirim form wawancara untuk program studi Anda.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
