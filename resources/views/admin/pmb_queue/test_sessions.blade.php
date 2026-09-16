@extends('admin_dashboard.layout.master')

@section('title', 'Pilih Sesi Tes Tulis')

@section('content')
<div class="mb-4">
    <span class="badge bg-label-primary mb-2">Petugas Tes Tulis</span>
    <h4 class="fw-bold mb-1">Pilih Sesi Tes Tulis</h4>
    <p class="text-muted mb-0">Buka sesi seleksi yang sedang digunakan untuk memantau tes tulis peserta di kampus.</p>
</div>

<div class="row g-3">
    @forelse($sessions as $session)
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                    <span class="badge bg-label-primary">{{ $session->code }}</span>
                    <span class="badge bg-label-{{ $session->status === \App\Models\PmbTestSession::STATUS_OPEN ? 'success' : 'secondary' }}">
                        {{ \App\Models\PmbTestSession::statusOptions()[$session->status] ?? ucfirst($session->status) }}
                    </span>
                </div>
                <h5 class="mb-1">{{ $session->name }}</h5>
                <div class="text-muted small mb-3">
                    {{ optional($session->starts_at)->translatedFormat('d F Y H:i') ?? 'Jadwal belum ditentukan' }} WIB
                </div>
                <div class="border-top pt-3 mt-auto">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Sedang tes</span>
                        <strong>{{ $session->active_test_count }}</strong>
                    </div>
                    <a href="{{ route('admin.pmb-queues.officer.tes-tulis', $session) }}" class="btn btn-primary w-100">
                        <i class="bx bx-pencil me-1"></i> Buka Tes Tulis
                    </a>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bx bx-calendar-x fs-1 text-muted mb-3"></i>
                <h5>Belum ada sesi tes tulis</h5>
                <p class="text-muted mb-0">Sesi akan muncul jika tahap Tes Tulis aktif pada sesi seleksi.</p>
            </div>
        </div>
    </div>
    @endforelse
</div>
@endsection
