@extends('admin_dashboard.layout.master')

@section('title', 'Scan Kedatangan Peserta')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Scan Kedatangan Peserta</h4>
        <p class="text-muted mb-0">Pilih sesi, lalu scan barcode pada kartu tes yang dibawa peserta.</p>
    </div>

    <div class="row g-4">
        @forelse($sessions as $session)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body">
                        <span class="badge bg-label-primary mb-2">{{ $session->code }}</span>
                        <h5 class="mb-2">{{ $session->name }}</h5>
                        <p class="text-muted small mb-3">
                            {{ optional($session->starts_at)->translatedFormat('d F Y H:i') ?? 'Jadwal belum ditentukan' }} WIB<br>
                            {{ $session->queues_count }} peserta terdaftar
                        </p>
                        <a href="{{ route('admin.pmb-queues.scan', $session) }}" class="btn btn-primary w-100">
                            <i class="bx bx-qr-scan me-1"></i> Buka Scanner
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card"><div class="card-body text-center py-5 text-muted">Belum ada sesi yang bisa diproses.</div></div>
            </div>
        @endforelse
    </div>
</div>
@endsection
