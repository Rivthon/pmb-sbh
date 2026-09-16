@extends('admin_dashboard.layout.master')

@section('title', 'Sesi Seleksi PMB')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Sesi Seleksi PMB</h4>
        <p class="text-muted mb-0">Kelola peserta online/offline, check-in kampus, ruangan, dan pemanggilan peserta PMB.</p>
    </div>
    @can(\App\Support\AdminPermissions::PMB_CREATE)
    <a href="{{ route('admin.pmb-queues.create') }}" class="btn btn-primary">
        <i class="bx bx-plus me-1"></i> Buat Sesi
    </a>
    @endcan
</div>

<div class="card mb-4">
    <div class="card-body">
        <form class="row g-3 align-items-end" method="GET">
            <div class="col-md-4">
                <label class="form-label">Periode</label>
                <select name="periode_id" class="form-select">
                    <option value="">Semua Periode</option>
                    @foreach($periodes as $periode)
                        <option value="{{ $periode->id }}" @selected(request('periode_id') == $periode->id)>
                            {{ $periode->deskripsi ?? 'Periode #' . $periode->id }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary flex-fill" type="submit">
                    <i class="bx bx-search me-1"></i> Filter
                </button>
                <a href="{{ route('admin.pmb-queues.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    @forelse($sessions as $session)
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 pmb-queue-session-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <div>
                            <span class="badge bg-label-primary mb-2">{{ $session->code }}</span>
                            <h5 class="mb-1">{{ $session->name }}</h5>
                            <div class="text-muted small">
                                {{ $session->periode->deskripsi ?? 'Periode belum dipilih' }}
                                @if($session->gelombang)
                                    - {{ $session->gelombang->nama_gelombang }}
                                @endif
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <span class="badge bg-label-secondary">
                                    Offline: {{ \App\Models\PmbTestSession::typeOptions()[$session->type] ?? $session->type }}
                                </span>
                                @if($session->tesTulis)
                                    <span class="badge bg-label-primary">
                                        Soal Online: {{ $session->tesTulis->nama_tes }}
                                    </span>
                                @else
                                    <span class="badge bg-label-info">
                                        Tanpa Ujian Online Sistem
                                    </span>
                                @endif
                            </div>
                        </div>
                        <span class="badge bg-label-{{ $session->status === 'open' ? 'success' : ($session->status === 'completed' ? 'dark' : 'secondary') }}">
                            {{ $statusOptions[$session->status] ?? $session->status }}
                        </span>
                    </div>

                    <div class="pmb-queue-metrics mb-3">
                        <div>
                            <strong>{{ $session->queues_count }}</strong>
                            <span>Peserta</span>
                        </div>
                        <div>
                            <strong>{{ $session->checked_in_count }}</strong>
                            <span>Hadir</span>
                        </div>
                        <div>
                            <strong>{{ $session->completed_count }}</strong>
                            <span>Selesai</span>
                        </div>
                    </div>

                    <div class="small text-muted mb-3">
                        <i class="bx bx-calendar me-1"></i>
                        {{ optional($session->starts_at)->translatedFormat('d M Y H:i') ?? 'Jadwal belum diatur' }}
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.pmb-queues.show', $session) }}" class="btn btn-sm btn-primary">
                            <i class="bx bx-show me-1"></i> Monitor
                        </a>
                        <a href="{{ route('admin.pmb-queues.board', $session) }}" class="btn btn-sm btn-outline-dark" target="_blank">
                            <i class="bx bx-tv me-1"></i> Board
                        </a>
                        @can(\App\Support\AdminPermissions::PMB_EDIT)
                        <a href="{{ route('admin.pmb-queues.edit', $session) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bx bx-edit me-1"></i> Edit
                        </a>
                        @endcan
                        @canany([\App\Support\AdminPermissions::PMB_EDIT, \App\Support\AdminPermissions::PMB_DELETE])
                        <form action="{{ route('admin.pmb-queues.destroy', $session) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus sesi antrian ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bx bx-trash me-1"></i> Hapus
                            </button>
                        </form>
                        @endcanany
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bx bx-calendar-x display-5 text-muted"></i>
                    <h5 class="mt-3">Belum ada sesi antrian</h5>
                    <p class="text-muted mb-0">Buat sesi seleksi untuk mengatur peserta online dan offline.</p>
                </div>
            </div>
        </div>
    @endforelse
</div>

<div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 mt-4">
    <div class="text-muted small">
        Menampilkan {{ $sessions->firstItem() ?? 0 }}-{{ $sessions->lastItem() ?? 0 }} dari {{ $sessions->total() }} sesi
    </div>
    <div>
        {{ $sessions->links() }}
    </div>
</div>
@endsection
