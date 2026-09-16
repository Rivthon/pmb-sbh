@extends('admin_dashboard.layout.master')

@section('title', 'Tambah Peserta Antrian')

@section('content')
<div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Tambah Peserta Antrian</h4>
        <p class="text-muted mb-0">Pilih peserta verified yang belum masuk sesi antrian.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if($selectedSession)
            <a href="{{ route('admin.pmb-queues.show', $selectedSession) }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali ke Sesi
            </a>
        @endif
        <a href="{{ route('admin.pmb-queues.index') }}" class="btn btn-outline-dark">
            <i class="bx bx-list-ul me-1"></i> Daftar Sesi
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-lg-3">
                <label class="form-label">Sesi Antrian</label>
                <select name="session_id" class="form-select" required>
                    @foreach($sessions as $sessionOption)
                        <option value="{{ $sessionOption->id }}" @selected($selectedSession?->id === $sessionOption->id)>
                            {{ $sessionOption->code }} - {{ $sessionOption->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Periode</label>
                <select name="periode_id" class="form-select">
                    <option value="">Dari Sesi / Aktif</option>
                    @foreach($periodeList as $periode)
                        <option value="{{ $periode->id }}" @selected(request('periode_id') == $periode->id)>
                            {{ $periode->deskripsi ?? 'Periode #' . $periode->id }}
                            @if($periode->status_periode === 'aktif') (Aktif) @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Gelombang</label>
                <select name="gelombang_id" class="form-select">
                    <option value="">Semua Gelombang</option>
                    @foreach($gelombangList as $gelombang)
                        <option value="{{ $gelombang->id }}" @selected(request('gelombang_id') == $gelombang->id)>
                            {{ $gelombang->nama_gelombang }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Program Studi</label>
                <select name="jurusan_id" class="form-select">
                    <option value="">Semua</option>
                    @foreach($jurusanList as $jurusan)
                        <option value="{{ $jurusan->id }}" @selected(request('jurusan_id') == $jurusan->id)>{{ $jurusan->nama_jurusan }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label">Cari</label>
                <div class="input-group">
                    <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nama, email, kode, HP">
                    <button class="btn btn-primary" type="submit"><i class="bx bx-filter-alt"></i></button>
                    <a href="{{ route('admin.pmb-queues.participants.add', ['session_id' => $selectedSession?->id]) }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i></a>
                </div>
            </div>
        </form>
    </div>
    @if($selectedSession)
        <div class="card-body border-top py-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="text-muted small me-1">Filter aktif:</span>
                <span class="badge bg-label-primary">{{ $selectedSession->code }}</span>
                @php
                    $activePeriode = $effectivePeriodeId ? $periodeList->firstWhere('id', $effectivePeriodeId) : null;
                    $activeGelombang = $effectiveGelombangId ? $gelombangList->firstWhere('id', $effectiveGelombangId) : null;
                @endphp
                @if($activePeriode)
                    <span class="badge bg-label-info">
                        <i class="bx bx-calendar me-1"></i>{{ $activePeriode->deskripsi ?? 'Periode #' . $activePeriode->id }}
                        @if(!$selectedSession->periode_id && $activePeriode->status_periode === 'aktif')
                            (auto: aktif)
                        @endif
                    </span>
                @else
                    <span class="badge bg-label-warning">Periode belum dipilih</span>
                @endif
                @if($activeGelombang)
                    <span class="badge bg-label-info">
                        <i class="bx bx-layer me-1"></i>{{ $activeGelombang->nama_gelombang }}
                    </span>
                @else
                    <span class="badge bg-label-secondary">Semua gelombang</span>
                @endif
                <span class="badge bg-label-success">Hanya akun verified</span>
            </div>
        </div>
    @endif
</div>

@if(!$selectedSession)
    <div class="alert alert-warning">Belum ada sesi antrian. Buat sesi terlebih dahulu.</div>
@else
    <div id="participants-table-wrapper">
        @include('admin.pmb_queue.partials.participants_table')
    </div>
@endif
@endsection

@push('script')
<script>
    // Handle AJAX table loading
    function loadTable(url) {
        const wrapper = document.getElementById('participants-table-wrapper');
        if (!wrapper) return;

        wrapper.style.opacity = '0.5';
        wrapper.style.pointerEvents = 'none';

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Response error');
            return response.text();
        })
        .then(html => {
            wrapper.innerHTML = html;
            wrapper.style.opacity = '1';
            wrapper.style.pointerEvents = 'auto';
            window.history.pushState({ path: url }, '', url);
        })
        .catch(error => {
            console.error('Error loading table:', error);
            wrapper.style.opacity = '1';
            wrapper.style.pointerEvents = 'auto';
            alert('Gagal memuat data peserta.');
        });
    }

    // Intercept pagination link clicks
    document.addEventListener('click', function (event) {
        const link = event.target.closest('.participants-pagination a');
        if (link) {
            event.preventDefault();
            const url = link.getAttribute('href');
            if (url) {
                loadTable(url);
            }
        }
    });

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function () {
        const wrapper = document.getElementById('participants-table-wrapper');
        if (wrapper) {
            loadTable(window.location.href);
        }
    });

    // Select all checkbox delegation
    document.addEventListener('change', function (event) {
        if (event.target && event.target.id === 'selectAllParticipants') {
            document.querySelectorAll('.participant-checkbox').forEach((checkbox) => {
                checkbox.checked = event.target.checked;
            });
        }
    });

    // Form submit delegation
    document.addEventListener('submit', function (event) {
        const form = event.target.closest('#participantAddForm');
        if (form) {
            if (!document.querySelector('.participant-checkbox:checked')) {
                event.preventDefault();
                alert('Pilih minimal satu peserta.');
            }
        }
    });
</script>
@endpush
