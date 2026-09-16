@extends('dashboard.layout.master')

@section('title', 'Antrian Tes PMB')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Antrian Tes Offline PMB</h4>
        <p class="text-muted mb-0">Pantau nomor antrian, jadwal, dan ruangan tes Anda.</p>
    </div>

    <div class="row g-4">
        @forelse($queues as $queue)
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between gap-3 mb-3">
                            <div>
                                <span class="badge bg-label-primary mb-2">{{ $queue->session->code }}</span>
                                <h5 class="mb-1">{{ $queue->session->name }}</h5>
                                <div class="text-muted small">{{ optional($queue->session->starts_at)->translatedFormat('d M Y H:i') ?? 'Jadwal belum ditentukan' }}</div>
                            </div>
                            <span class="badge bg-label-{{ $queue->statusTone() }}">{{ $queue->statusLabel() }}</span>
                        </div>

                        <div class="d-flex align-items-center justify-content-between border rounded p-3 mb-3">
                            <div>
                                <div class="text-muted small">Nomor Antrian</div>
                                <div class="display-6 fw-bold mb-0">{{ $queue->queue_code ?? '-' }}</div>
                            </div>
                            <div class="text-end">
                                <div class="text-muted small">Ruangan</div>
                                <div class="fw-semibold">{{ $queue->room->name ?? 'Menunggu arahan panitia' }}</div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-sm-flex">
                            <a href="{{ route('dashboard.pmb-queue.show', $queue) }}" class="btn btn-primary flex-fill">
                                Lihat Kartu Tes
                            </a>
                            <a href="{{ route('dashboard.pmb-queue.print', $queue) }}" target="_blank" class="btn btn-outline-primary flex-fill">
                                <i class="bx bx-printer me-1"></i> Cetak Kartu
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bx bx-qr-scan display-5 text-muted"></i>
                        <h5 class="mt-3">Belum ada sesi antrian</h5>
                        <p class="text-muted mb-0">Kartu tes akan muncul setelah panitia menambahkan Anda ke sesi tes offline.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $queues->links() }}</div>
</div>
@endsection
