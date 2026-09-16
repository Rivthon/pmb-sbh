@extends('admin_dashboard.layout.master')

@section('title', 'Pilih Sesi Wawancara')

@section('content')
@php
    $mode = $mode ?? 'both';
    $showOffline = in_array($mode, ['both', 'offline'], true);
    $showOnline = in_array($mode, ['both', 'online'], true);
    $pageTitle = match ($mode) {
        'offline' => 'Wawancara Offline',
        'online' => 'Wawancara Online',
        default => 'Pilih Sesi Wawancara',
    };
    $pageSubtitle = match ($mode) {
        'offline' => 'Pilih sesi untuk memulai pemanggilan dan review wawancara offline.',
        'online' => 'Pilih sesi untuk mereview form wawancara online sesuai prodi.',
        default => 'Pilih sesi seleksi yang sedang digunakan untuk membuka meja wawancara Anda.',
    };
@endphp

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <span class="badge bg-label-primary mb-2">{{ $admin->jurusan?->nama_jurusan ?? 'Program studi belum ditentukan' }}</span>
        <h4 class="fw-bold mb-1">{{ $pageTitle }}</h4>
        <p class="text-muted mb-0">{{ $pageSubtitle }}</p>
    </div>
</div>

@if(!$admin->jurusan_id)
<div class="alert alert-warning d-flex gap-2" role="alert">
    <i class="bx bx-error-circle fs-4"></i>
    <div><strong>Program studi belum ditentukan.</strong> Hubungi admin PMB untuk melengkapi akun sebelum melakukan wawancara.</div>
</div>
@endif

<div class="row g-3">
    @forelse($sessions as $session)
        @php($desk = $session->rooms->first())
        @php($offlineSummary = $offlineInterviewSummaries[$session->id] ?? ['remaining' => 0, 'waiting' => 0, 'processing' => 0, 'completed' => 0, 'total' => 0])
        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                        <span class="badge bg-label-primary">{{ $session->code }}</span>
                        <span class="badge bg-label-{{ $session->status === \App\Models\PmbTestSession::STATUS_OPEN ? 'success' : 'secondary' }}">{{ \App\Models\PmbTestSession::statusOptions()[$session->status] ?? ucfirst($session->status) }}</span>
                    </div>
                    <h5 class="mb-1">{{ $session->name }}</h5>
                    <div class="text-muted small mb-3">{{ optional($session->starts_at)->translatedFormat('d F Y H:i') ?? 'Jadwal belum ditentukan' }} WIB</div>
                    <div class="border-top pt-3 mt-auto">
                        <div class="row g-3">
                            @if($showOffline)
                            <div class="col-12">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-start gap-2 mb-2">
                                        <span class="badge bg-label-primary"><i class="bx bx-conversation"></i></span>
                                        <div>
                                            <div class="fw-semibold">Wawancara Offline</div>
                                            <div class="text-muted small">Meja fisik: {{ $desk?->name ?? 'Otomatis sesuai prodi' }}</div>
                                        </div>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <div class="bg-label-warning rounded p-2 h-100">
                                                <div class="small text-muted">Belum Wawancara</div>
                                                <div class="fw-bold">{{ $offlineSummary['remaining'] }}</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-label-success rounded p-2 h-100">
                                                <div class="small text-muted">Selesai</div>
                                                <div class="fw-bold">{{ $offlineSummary['completed'] }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    @if($admin->jurusan_id)
                                    <a href="{{ route('admin.pmb-queues.officer.wawancara', $session) }}" class="btn btn-primary w-100">
                                        <i class="bx bx-conversation me-1"></i> Buka Wawancara Offline
                                    </a>
                                    @else
                                    <button class="btn btn-label-secondary w-100" type="button" disabled>
                                        Prodi Dosen Belum Diatur
                                    </button>
                                    <div class="form-text mt-2">Akun dosen perlu memiliki program studi agar sistem bisa mengambil peserta offline sesuai prodi.</div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            @if($showOnline)
                            <div class="col-12">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-start gap-2 mb-2">
                                        <span class="badge bg-label-info"><i class="bx bx-video"></i></span>
                                        <div>
                                            <div class="fw-semibold">Wawancara Online</div>
                                            <div class="text-muted small">Pilih peserta online yang sudah submit form sesuai prodi.</div>
                                        </div>
                                    </div>

                                    @if($admin->jurusan_id)
                                    <a href="{{ route('admin.pmb-queues.officer.wawancara-online', $session) }}" class="btn btn-outline-primary w-100">
                                        <i class="bx bx-video me-1"></i> Buka Wawancara Online
                                    </a>
                                    @else
                                    <button class="btn btn-label-secondary w-100" type="button" disabled>Prodi Dosen Belum Diatur</button>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card"><div class="card-body text-center py-5">
                <i class="bx bx-calendar-x fs-1 text-muted mb-3"></i>
                <h5>Belum ada sesi wawancara aktif</h5>
                <p class="text-muted mb-0">Sesi akan muncul di halaman ini setelah status check-in dibuka.</p>
            </div></div>
        </div>
    @endforelse
</div>
@endsection
