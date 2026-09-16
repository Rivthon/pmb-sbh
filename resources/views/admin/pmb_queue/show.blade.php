@extends('admin_dashboard.layout.master')

@section('title', 'Monitor Antrian PMB')

@section('content')
@php
    $onlineManagerOnly = ($canManageOnlineSelection ?? false) && !($canManageQueue ?? false);
    $activeView = in_array(request('view'), ['monitoring', 'peserta'], true) ? request('view') : 'monitoring';
    $sessionScheduleLabel = null;

    if ($session->starts_at) {
        $sessionScheduleLabel = $session->starts_at->format('H:i') === '00:00'
            ? $session->starts_at->translatedFormat('d M Y')
            : $session->starts_at->translatedFormat('d M Y H:i');
    }
@endphp
<style>
    html { scroll-behavior: smooth; }
    .pmb-session-compact-header {
        background: #fff;
        border: 1px solid rgba(67, 89, 113, .12);
        border-radius: .5rem;
        padding: 1rem 1.25rem;
        box-shadow: 0 .2rem .75rem rgba(67, 89, 113, .06);
    }
    .pmb-session-compact-header h4 { font-size: 1.25rem; }
    .pmb-session-actions {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, auto));
        justify-content: end;
        gap: .5rem;
    }
    .pmb-session-actions .btn {
        min-height: 38px;
        padding-inline: .875rem;
    }
    .pmb-page-nav {
        background: #fff;
        border: 1px solid rgba(67, 89, 113, .12);
        border-radius: .5rem;
        padding: .4rem;
    }
    .pmb-page-nav .btn { min-height: 34px; }
    .pmb-queue-stats-compact .card,
    .pmb-stage-stats-compact .card {
        box-shadow: 0 .2rem .75rem rgba(67, 89, 113, .06);
    }
    .pmb-queue-stats-compact .card-body,
    .pmb-stage-stats-compact .card-body {
        min-height: 0;
        padding: .75rem 1rem;
    }
    .pmb-queue-stats-compact .badge {
        flex: 0 0 auto;
        font-size: .95rem;
    }
    .pmb-queue-stats-compact .small,
    .pmb-stage-stats-compact .small {
        line-height: 1.2;
    }
    .pmb-checkin-card .card-header,
    .pmb-checkin-card .card-body {
        padding: 1rem 1.25rem;
    }
    .pmb-checkin-card .card-header {
        border-bottom: 0;
        padding-bottom: .35rem;
    }
    .pmb-checkin-card .input-group-lg > .form-control,
    .pmb-checkin-card .input-group-lg > .btn {
        min-height: 46px;
        font-size: .95rem;
    }
    .pmb-checkin-card .form-text { margin-top: .5rem; }
    #alur-otomatis,
    #loket,
    #daftar-peserta { scroll-margin-top: 88px; }
    .pmb-call-console-card { box-shadow: 0 .35rem 1rem rgba(67, 89, 113, .08); }
    .pmb-call-panel {
        border: 1px solid rgba(67, 89, 113, .12);
        border-radius: .5rem;
        padding: 1rem;
        background: #fff;
        min-width: 0;
    }
    .pmb-call-form { min-width: min(100%, 280px); }
    .pmb-call-form .input-group { flex-wrap: nowrap; }
    .pmb-call-room-select {
        min-width: 0;
        max-width: 100%;
    }
    .pmb-next-list {
        display: grid;
        gap: .65rem;
        max-height: 360px;
        overflow-y: auto;
        padding-right: .15rem;
    }
    .pmb-next-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .75rem;
        border: 1px solid rgba(67, 89, 113, .12);
        border-radius: .5rem;
        padding: .65rem .75rem;
        background: #f8f9fa;
        min-width: 0;
    }
    .pmb-next-main {
        min-width: 0;
        flex: 1 1 auto;
    }
    .pmb-next-main .fw-semibold,
    .pmb-next-main .text-muted {
        overflow-wrap: anywhere;
    }
    .pmb-next-action {
        flex: 0 0 auto;
    }
    .pmb-interview-lane {
        border: 1px dashed rgba(67, 89, 113, .2);
        border-radius: .5rem;
        padding: .75rem;
        background: #fbfbfc;
        min-width: 0;
    }
    @media (max-width: 575.98px) {
        .pmb-session-compact-header { padding: 1rem; }
        .pmb-session-actions {
            grid-template-columns: 1fr 1fr;
            width: 100%;
        }
        .pmb-session-actions .btn {
            justify-content: center;
            width: 100%;
        }
        .pmb-page-nav {
            display: grid !important;
            grid-template-columns: 1fr;
        }
        .pmb-page-nav .btn {
            justify-content: center;
            width: 100%;
        }
        .pmb-checkin-card .card-header,
        .pmb-checkin-card .card-body {
            padding-inline: 1rem;
        }
        .pmb-call-form,
        .pmb-call-form .input-group,
        .pmb-call-form .btn,
        .pmb-call-room-select {
            width: 100%;
        }
        .pmb-call-form .input-group {
            display: grid;
            gap: .5rem;
        }
        .pmb-call-form .input-group > .form-select,
        .pmb-call-form .input-group > .btn {
            border-radius: .375rem !important;
        }
        .pmb-next-item {
            align-items: stretch;
            flex-direction: column;
        }
        .pmb-next-action,
        .pmb-next-action .btn {
            width: 100%;
        }
    }
</style>

<div class="pmb-session-compact-header d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-3">
    <div>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <span class="badge bg-label-primary">{{ $session->code }}</span>
            <span class="badge bg-label-secondary">{{ \App\Models\PmbTestSession::typeOptions()[$session->type] ?? $session->type }}</span>
        </div>
        <h4 class="fw-bold mb-1">Sesi Seleksi PMB</h4>
        <div class="text-muted small d-flex flex-wrap align-items-center gap-2">
            <span>{{ $session->name }}</span>
            @if($session->gelombang)
                <span class="d-none d-sm-inline">|</span>
                <span>{{ $session->gelombang->nama_gelombang }}</span>
            @endif
            @if($sessionScheduleLabel)
                <span class="d-none d-sm-inline">|</span>
                <span>{{ $sessionScheduleLabel }}</span>
            @endif
        </div>
    </div>
    <div class="pmb-session-actions">
        @if($onlineManagerOnly)
        <a href="{{ route('admin.pmb-queues.edit', $session) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bx bx-slider-alt me-1"></i> Atur Jadwal
        </a>
        <a href="{{ route('admin.tes-tulis.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="bx bx-pencil me-1"></i> Tes Tulis & Soal
        </a>
        <a href="{{ route('admin.hasil-tes.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="bx bx-bar-chart-alt-2 me-1"></i> Hasil Tes Tulis
        </a>
        <a href="{{ route('admin.tes-kesehatan.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="bx bx-health me-1"></i> Kesehatan Online
        </a>
        <a href="{{ route('admin.wawancara.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="bx bx-conversation me-1"></i> Wawancara Online
        </a>
        @else
        <a href="{{ route('admin.pmb-queues.board', $session) }}" target="_blank" class="btn btn-sm btn-dark">
            <i class="bx bx-tv me-1"></i> Board
        </a>
            @if($canOperateTesTulis ?? false)
            <a href="{{ route('admin.pmb-queues.officer.tes-tulis', $session) }}" class="btn btn-sm btn-outline-primary">
                <i class="bx bx-pencil me-1"></i> Petugas Tes Tulis
            </a>
            @endif
            @if($canOperateHealth ?? false)
            <a href="{{ route('admin.pmb-queues.officer.kesehatan', $session) }}" class="btn btn-sm btn-outline-primary">
                <i class="bx bx-plus-medical me-1"></i> Petugas Kesehatan
            </a>
            @endif
            @if($canOperateInterview ?? false)
            <a href="{{ route('admin.pmb-queues.officer.wawancara', $session) }}" class="btn btn-sm btn-outline-primary">
                <i class="bx bx-conversation me-1"></i> Dosen Wawancara
            </a>
            @endif
            @can(\App\Support\AdminPermissions::PMB_EXPORT)
            <a href="{{ route('admin.pmb-queues.export', $session) }}" class="btn btn-sm btn-outline-success">
                <i class="bx bx-download me-1"></i> Export
            </a>
            @endcan
            @if(($canManageQueue ?? false) || ($canManageOnlineSelection ?? false))
            <a href="{{ route('admin.pmb-queues.edit', $session) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bx bx-slider-alt me-1"></i> Atur Jadwal
            </a>
            @endif
        @endif
    </div>
</div>

<div class="pmb-page-nav d-flex flex-wrap gap-2 mb-3">
    @if($onlineManagerOnly)
    <a href="{{ route('admin.pmb-queues.edit', $session) }}" class="btn btn-sm btn-primary">
        <i class="bx bx-slider-alt me-1"></i> Atur Jadwal
    </a>
    <a href="{{ route('admin.tes-tulis.index') }}" class="btn btn-sm btn-outline-primary">
        <i class="bx bx-pencil me-1"></i> Tes Tulis & Soal
    </a>
    <a href="{{ route('admin.hasil-tes.index') }}" class="btn btn-sm btn-outline-primary">
        <i class="bx bx-bar-chart-alt-2 me-1"></i> Hasil Tes
    </a>
    <a href="{{ route('admin.tes-kesehatan.index') }}" class="btn btn-sm btn-outline-primary">
        <i class="bx bx-health me-1"></i> Kesehatan
    </a>
    <a href="{{ route('admin.wawancara.index') }}" class="btn btn-sm btn-outline-primary">
        <i class="bx bx-conversation me-1"></i> Wawancara
    </a>
    <a href="{{ route('admin.pmb-queues.show', [$session, 'view' => 'peserta']) }}" class="btn btn-sm {{ $activeView === 'peserta' ? 'btn-secondary' : 'btn-outline-secondary' }}">
        <i class="bx bx-list-ul me-1"></i> Daftar Peserta
    </a>
    @else
    <a href="{{ route('admin.pmb-queues.show', [$session, 'view' => 'monitoring']) }}" class="btn btn-sm {{ $activeView === 'monitoring' ? 'btn-primary' : 'btn-outline-primary' }}">
        <i class="bx bx-git-branch me-1"></i> Monitoring
    </a>
    <a href="{{ route('admin.pmb-queues.show', [$session, 'view' => 'peserta']) }}" class="btn btn-sm {{ $activeView === 'peserta' ? 'btn-primary' : 'btn-outline-secondary' }}">
        <i class="bx bx-list-ul me-1"></i> Daftar Peserta
    </a>
    @endif
</div>

@if($activeView === 'monitoring')
<div id="pmbQueueStats">
    @include('admin.pmb_queue.partials.stats')
</div>

@unless($onlineManagerOnly)
<section id="alur-otomatis" class="pmb-call-console-wrap mb-4">
    <div id="pmbQueueCallConsole">
        @include('admin.pmb_queue.partials.call_console')
    </div>
</section>
@endunless

@unless($onlineManagerOnly)
<section id="loket" class="row g-4 mb-4">
    <div class="col-xl-6 col-12">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="mb-0">Tes Kesehatan</h5>
                    <small class="text-muted">1 queue bersama - Ruang Tes Kesehatan, Lantai 2</small>
                </div>
                <div class="d-flex gap-1">
                    @if($canManageQueue ?? false)
                        <button class="btn btn-sm btn-outline-primary pmb-add-room-btn" data-stage-id="{{ optional($stages->where('is_active', true)->firstWhere('key', \App\Models\PmbQueueStage::KEY_TES_KESEHATAN))->id }}" data-stage-name="Tes Kesehatan" type="button" title="Tambah Loket">
                            <i class="bx bx-plus"></i>
                        </button>
                    @endif
                    <a href="{{ route('admin.pmb-queues.board', $session) }}" target="_blank" class="btn btn-sm btn-outline-dark">
                        <i class="bx bx-tv"></i>
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($canOperateHealth ?? false)
                    <div class="alert alert-success py-2 mb-3">
                        Sistem otomatis memanggil peserta berikutnya ketika akun petugas ini kosong.
                    </div>
                @endif

                @if($canManageQueue ?? false)
                {{-- ✅ Inline Form Room Kesehatan --}}
                <div class="pmb-room-form-container d-none mb-3" id="pmbRoomFormCollapse-health">
                    <div class="card border p-3 bg-light-50">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-semibold mb-0 pmb-room-form-title">Tambah Loket</h6>
                            <button type="button" class="btn-close pmb-room-form-close" aria-label="Close"></button>
                        </div>
                        <form class="pmb-room-inline-form row g-2">
                            @csrf
                            <input type="hidden" name="_method" class="pmb-room-method" value="POST">
                            <input type="hidden" name="stage_id" value="{{ optional($stages->where('is_active', true)->firstWhere('key', \App\Models\PmbQueueStage::KEY_TES_KESEHATAN))->id }}">
                            
                            <div class="col-12">
                                <div class="alert alert-danger d-none pmb-room-alert p-2 small mb-2"></div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fs-8 mb-1">Nama Ruangan/Loket</label>
                                <input type="text" name="name" class="form-control form-control-sm pmb-room-name" maxlength="120" placeholder="Contoh: Ruang Kesehatan 1">
                                <div class="form-text">Boleh dikosongkan untuk otomatis memakai nama petugas.</div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fs-8 mb-1 required">Akun Petugas Medis</label>
                                <select name="assigned_admin_id" class="form-select form-select-sm pmb-room-officer" required>
                                    <option value="">Pilih akun petugas</option>
                                    @foreach($healthOfficers as $officer)
                                        <option value="{{ $officer->id }}">{{ $officer->name }} - {{ $officer->email }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Akun petugas dipakai untuk login dan pemanggilan peserta.</div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fs-8 mb-1 required">Status</label>
                                <select name="status" class="form-select form-select-sm pmb-room-status" required>
                                    @foreach($roomStatusOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-12 d-flex justify-content-end gap-1 mt-2">
                                <button type="button" class="btn btn-xs btn-label-secondary pmb-room-form-cancel">Batal</button>
                                <button type="submit" class="btn btn-xs btn-primary pmb-room-form-submit">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                <div id="pmbQueueLanesHealth">
                    @include('admin.pmb_queue.partials.lanes', ['stageKey' => \App\Models\PmbQueueStage::KEY_TES_KESEHATAN])
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6 col-12">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="mb-0">Wawancara</h5>
                    <small class="text-muted">Setiap dosen memiliki meja dan hanya memanggil peserta sesuai program studi</small>
                </div>
                <div class="d-flex gap-1">
                    @if($canManageQueue ?? false)
                        <button class="btn btn-sm btn-outline-primary pmb-add-room-btn" data-stage-id="{{ optional($stages->where('is_active', true)->firstWhere('key', \App\Models\PmbQueueStage::KEY_WAWANCARA_KAPRODI))->id }}" data-stage-name="Wawancara" type="button" title="Tambah Loket">
                            <i class="bx bx-plus"></i>
                        </button>
                    @endif
                    <a href="{{ route('admin.pmb-queues.board', $session) }}" target="_blank" class="btn btn-sm btn-outline-dark">
                        <i class="bx bx-tv"></i>
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($canOperateInterview ?? false)
                    <div class="alert alert-success py-2 mb-3">
                        Dosen menekan tombol Siap dari meja masing-masing untuk memanggil peserta berikutnya.
                    </div>
                @endif

                @if($canManageQueue ?? false)
                {{-- ✅ Inline Form Room Wawancara --}}
                <div class="pmb-room-form-container d-none mb-3" id="pmbRoomFormCollapse-interview">
                    <div class="card border p-3 bg-light-50">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-semibold mb-0 pmb-room-form-title">Tambah Loket</h6>
                            <button type="button" class="btn-close pmb-room-form-close" aria-label="Close"></button>
                        </div>
                        <form class="pmb-room-inline-form row g-2">
                            @csrf
                            <input type="hidden" name="_method" class="pmb-room-method" value="POST">
                            <input type="hidden" name="stage_id" value="{{ optional($stages->where('is_active', true)->firstWhere('key', \App\Models\PmbQueueStage::KEY_WAWANCARA_KAPRODI))->id }}">
                            
                            <div class="col-12">
                                <div class="alert alert-danger d-none pmb-room-alert p-2 small mb-2"></div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fs-8 mb-1 required">Akun Dosen Pewawancara</label>
                                <select name="assigned_admin_id" class="form-select form-select-sm pmb-room-officer" required>
                                    <option value="">Pilih akun dosen</option>
                                    @foreach($interviewers as $officer)
                                        <option value="{{ $officer->id }}" @disabled(!$officer->jurusan_id)>
                                            {{ $officer->name }} - {{ $officer->jurusan?->nama_jurusan ?? 'Prodi belum diatur' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Nomor meja dibuat otomatis dan program studi mengikuti akun dosen.</div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fs-8 mb-1 required">Status</label>
                                <select name="status" class="form-select form-select-sm pmb-room-status" required>
                                    @foreach($roomStatusOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-12 d-flex justify-content-end gap-1 mt-2">
                                <button type="button" class="btn btn-xs btn-label-secondary pmb-room-form-cancel">Batal</button>
                                <button type="submit" class="btn btn-xs btn-primary pmb-room-form-submit">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                <div id="pmbQueueLanesInterview">
                    @include('admin.pmb_queue.partials.lanes', ['stageKey' => \App\Models\PmbQueueStage::KEY_WAWANCARA_KAPRODI])
                </div>
            </div>
        </div>
    </div>
</section>
@endunless

@if($canManageQueue ?? false)
<details class="card mb-4 pmb-support-panel">
    <summary class="card-header d-flex align-items-center justify-content-between">
        <span class="fw-semibold">Data Pendukung</span>
        <span class="badge bg-label-secondary">Opsional</span>
    </summary>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-xl-7">
                <h6 class="fw-semibold mb-2">Tambah Peserta</h6>
                <p class="text-muted mb-3">Gunakan halaman tabel supaya filter nama, email, kode peserta, program studi, periode, dan gelombang lebih jelas.</p>
                <a href="{{ route('admin.pmb-queues.participants.add', ['session_id' => $session->id]) }}" class="btn btn-primary">
                    <i class="bx bx-table me-1"></i> Buka Tabel Peserta
                </a>
            </div>
            <div class="col-xl-5">
                <h6 class="fw-semibold mb-3">Tambah Lokasi / Loket</h6>
                <form method="POST" action="{{ route('admin.pmb-queues.rooms.store', $session) }}" class="row g-2">
                    @csrf
                    <div class="col-12">
                        <input type="text" name="name" class="form-control" placeholder="Nama petugas / lokasi" required>
                    </div>
                    <div class="col-4">
                        <select name="status" class="form-select">
                            @foreach($roomStatusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <select name="stage_id" class="form-select">
                            <option value="">Tidak dikaitkan tahap</option>
                            @foreach($stages->where('is_active', true) as $stage)
                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-outline-primary w-100" type="submit">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</details>
@endif
@endif

@if($activeView === 'peserta')
<div id="pmbQueueAjaxAlert" class="alert d-none mb-3" role="alert"></div>

<div id="daftar-peserta" class="card">
    <div class="card-header">
        <div class="d-flex flex-column flex-xl-row justify-content-between gap-3">
            <div>
                <h5 class="mb-1">Daftar Peserta</h5>
                <small class="text-muted">Monitoring peserta, status check-in, jalur tes, dan posisi tahap.</small>
            </div>
            @php
                $healthStageId = optional($stages->where('is_active', true)->firstWhere('key', \App\Models\PmbQueueStage::KEY_TES_KESEHATAN))->id;
                $interviewStageId = optional($stages->where('is_active', true)->firstWhere('key', \App\Models\PmbQueueStage::KEY_WAWANCARA_KAPRODI))->id;
            @endphp
            <form class="row g-2 align-items-end pmb-filter-form" method="GET">
                <input type="hidden" name="view" value="peserta">
                <div class="col-md-4">
                    <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari peserta">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Status</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="stage_id" class="form-select">
                        <option value="">Tahap</option>
                        @foreach($stages->where('is_active', true) as $stage)
                            <option value="{{ $stage->id }}" @selected(request('stage_id') == $stage->id)>{{ $stage->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="stage_status" class="form-select">
                        <option value="">Status tahap</option>
                        @foreach($stageStatusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('stage_status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-fill" type="submit"><i class="bx bx-filter-alt"></i></button>
                    <a href="{{ route('admin.pmb-queues.show', [$session, 'view' => 'peserta']) }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i></a>
                </div>
            </form>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-3">
            <a href="{{ route('admin.pmb-queues.show', array_filter([$session, 'view' => 'peserta', 'stage_id' => $healthStageId, 'stage_status' => \App\Models\PmbQueueStageStep::STATUS_WAITING])) }}" class="btn btn-sm btn-outline-warning">
                Menunggu Kesehatan
            </a>
            <a href="{{ route('admin.pmb-queues.show', array_filter([$session, 'view' => 'peserta', 'stage_id' => $healthStageId, 'stage_status' => \App\Models\PmbQueueStageStep::STATUS_PROCESSING])) }}" class="btn btn-sm btn-outline-info">
                Proses Kesehatan
            </a>
            <a href="{{ route('admin.pmb-queues.show', array_filter([$session, 'view' => 'peserta', 'stage_id' => $interviewStageId, 'stage_status' => \App\Models\PmbQueueStageStep::STATUS_WAITING])) }}" class="btn btn-sm btn-outline-warning">
                Menunggu Wawancara
            </a>
            <a href="{{ route('admin.pmb-queues.show', array_filter([$session, 'view' => 'peserta', 'stage_id' => $interviewStageId, 'stage_status' => \App\Models\PmbQueueStageStep::STATUS_PROCESSING])) }}" class="btn btn-sm btn-outline-info">
                Proses Wawancara
            </a>
            <a href="{{ route('admin.pmb-queues.show', [$session, 'view' => 'peserta', 'status' => \App\Models\PmbOfflineQueue::STATUS_REGISTERED]) }}" class="btn btn-sm btn-outline-secondary">
                Belum Check-in
            </a>
        </div>
    </div>
    @if(($canManageQueue ?? false) || ($canManageOnlineSelection ?? false))
    <div class="border-top px-3 py-3">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge bg-label-primary" id="pmbSelectedCount">0 dipilih</span>
            @if($canManageQueue ?? false)
            <button class="btn btn-sm btn-primary pmb-bulk-check-in" type="button">Bulk Check-in</button>
            @endif
            @if($canManageOnlineSelection ?? false)
            <button class="btn btn-sm btn-outline-warning pmb-bulk-health-notified" type="button">Diberitahukan H+7</button>
            @endif
            <span class="text-muted small">Aksi tahap diproses dari halaman petugas masing-masing.</span>
        </div>
    </div>
    @endif
    <div id="pmbQueueRows" class="card-body p-0">
        @include('admin.pmb_queue.partials.rows')
    </div>
</div>
@endif

@endsection

@push('script')
<script src="https://unpkg.com/html5-qrcode" defer></script>
<script>
    (function () {
        const endpoint = @json(route('admin.pmb-queues.stats', $session));
        const bulkEndpoint = @json(route('admin.pmb-queues.bulk-status', $session));
        const bulkCheckInEndpoint = @json(route('admin.pmb-queues.bulk-check-in', $session));
        const bulkHealthNotifiedEndpoint = @json(route('admin.pmb-queues.online.health-notified', $session));
        const roomStoreEndpoint = @json(route('admin.pmb-queues.rooms.store', $session));
        const roomUpdateUrlTemplate = "{{ route('admin.pmb-queues.rooms.update', ['room' => '__ROOM_UUID__']) }}";
        const csrfToken = @json(csrf_token());
        let params = new URLSearchParams(window.location.search);
        const input = document.getElementById('checkInCodeInput');
        const startButton = document.getElementById('startQrCamera');
        const stopButton = document.getElementById('stopQrCamera');
        const reader = document.getElementById('qrCameraReader');
        let scanner = null;

        input?.focus();

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, function (char) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
            });
        }

        function updateBulkButtonsState() {
            const checkboxes = document.querySelectorAll('.pmb-queue-checkbox:checked');
            const bulkCheckInBtn = document.querySelector('.pmb-bulk-check-in');
            const bulkHealthBtn = document.querySelector('.pmb-bulk-health-notified');
            const bulkActions = document.querySelectorAll('.pmb-bulk-action');

            if (checkboxes.length === 0) {
                bulkCheckInBtn?.setAttribute('disabled', 'disabled');
                bulkHealthBtn?.setAttribute('disabled', 'disabled');
                bulkActions.forEach(btn => btn.setAttribute('disabled', 'disabled'));
                return;
            }

            bulkHealthBtn?.removeAttribute('disabled');

            const stages = new Set();
            checkboxes.forEach(cb => {
                stages.add(cb.dataset.stage || 'not_checked_in');
            });

            if (stages.has('not_checked_in') || stages.has('')) {
                bulkCheckInBtn?.removeAttribute('disabled');
            } else {
                bulkCheckInBtn?.setAttribute('disabled', 'disabled');
            }

            bulkActions.forEach(btn => {
                const action = btn.dataset.action;
                let valid = false;

                if (action === 'complete_test_tulis' && stages.has('tes_tulis')) {
                    valid = true;
                } else if (action === 'hold_health_form' && (stages.has('tes_tulis') || stages.has('tes_kesehatan'))) {
                    valid = true;
                } else if (action === 'release_health_form' && stages.has('tes_kesehatan')) {
                    valid = true;
                } else if (action === 'complete_health' && stages.has('tes_kesehatan')) {
                    valid = true;
                } else if (action === 'complete' && stages.has('wawancara_kaprodi')) {
                    valid = true;
                }

                if (valid) {
                    btn.removeAttribute('disabled');
                } else {
                    btn.setAttribute('disabled', 'disabled');
                }
            });
        }

        function updateSelectedCount() {
            const count = document.querySelectorAll('.pmb-queue-checkbox:checked').length;
            const label = document.getElementById('pmbSelectedCount');
            if (label) label.textContent = count + ' dipilih';
            updateBulkButtonsState();
        }

        function showAjaxAlert(message, ok = true, errors = []) {
            const alert = document.getElementById('pmbQueueAjaxAlert');
            if (!alert) return;

            alert.className = 'alert mb-3 ' + (ok ? 'alert-success' : 'alert-warning');
            const details = errors.length ? '<div class="small mt-1">' + errors.slice(0, 5).map((item) => '- ' + escapeHtml(item)).join('<br>') + '</div>' : '';
            alert.innerHTML = escapeHtml(message || 'Data antrian diperbarui.') + details;
            alert.classList.remove('d-none');
        }

        function replaceQueueFragments(data) {
            if (data.stats) {
                const el = document.getElementById('pmbQueueStats');
                if (el) el.innerHTML = data.stats;
            }
            if (data.call_console) {
                const el = document.getElementById('pmbQueueCallConsole');
                if (el) el.innerHTML = data.call_console;
            }
            if (data.lanes_health) {
                const el = document.getElementById('pmbQueueLanesHealth');
                if (el) el.innerHTML = data.lanes_health;
            }
            if (data.lanes_interview) {
                const el = document.getElementById('pmbQueueLanesInterview');
                if (el) el.innerHTML = data.lanes_interview;
            }
            if (data.rows) {
                const el = document.getElementById('pmbQueueRows');
                if (el) el.innerHTML = data.rows;
            }
            updateSelectedCount();
            updateRoomDropdowns();
        }

        async function stopScanner() {
            if (!scanner) return;
            await scanner.stop();
            scanner.clear();
            scanner = null;
            reader.classList.add('d-none');
            stopButton.classList.add('d-none');
            startButton.classList.remove('d-none');
            input?.focus();
        }

        startButton?.addEventListener('click', async function () {
            if (!window.Html5Qrcode) {
                alert('Scanner kamera belum siap.');
                return;
            }

            reader.classList.remove('d-none');
            stopButton.classList.remove('d-none');
            startButton.classList.add('d-none');
            scanner = new Html5Qrcode('qrCameraReader');

            try {
                await scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 240, height: 240 } },
                    async function (decodedText) {
                        input.value = decodedText;
                        await stopScanner();
                        input.form.submit();
                    }
                );
            } catch (error) {
                console.warn('QR camera failed', error);
                await stopScanner();
                alert('Kamera tidak dapat dibuka.');
            }
        });

        stopButton?.addEventListener('click', stopScanner);

        async function refreshQueue() {
            try {
                const response = await fetch(endpoint + (params.toString() ? '?' + params.toString() : ''), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) return;
                replaceQueueFragments(await response.json());
            } catch (error) {
                console.warn('Queue refresh failed', error);
            }
        }

        const filterForm = document.querySelector('.pmb-filter-form');
        filterForm?.addEventListener('submit', async function (event) {
            event.preventDefault();
            const formData = new FormData(filterForm);
            params = new URLSearchParams();
            for (const [key, value] of formData.entries()) {
                if (value) {
                    params.set(key, value);
                }
            }
            window.history.replaceState({}, '', window.location.pathname + (params.toString() ? '?' + params.toString() : ''));
            await refreshQueue();
        });

        document.addEventListener('submit', async function (event) {
            const form = event.target.closest('form.pmb-ajax-action');
            if (!form) return;

            event.preventDefault();
            const button = form.querySelector('[type="submit"]');
            button?.setAttribute('disabled', 'disabled');

            try {
                const formData = new FormData(form);
                const methodInput = form.querySelector('input[name="_method"]');
                if (methodInput) {
                    formData.set('_method', methodInput.value);
                } else {
                    formData.delete('_method');
                }
                const response = await fetch(form.dataset.ajaxAction || form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });
                const data = await response.json();
                replaceQueueFragments(data);
                showAjaxAlert(data.message, response.ok && data.ok !== false, data.errors || []);
            } catch (error) {
                console.warn('Queue action failed', error);
                form.submit();
            } finally {
                button?.removeAttribute('disabled');
            }
        });

        document.addEventListener('change', function (event) {
            if (event.target.id === 'pmbQueueSelectAll') {
                document.querySelectorAll('.pmb-queue-checkbox').forEach((checkbox) => {
                    checkbox.checked = event.target.checked;
                });
            }

            if (event.target.id === 'pmbQueueSelectAll' || event.target.classList.contains('pmb-queue-checkbox')) {
                updateSelectedCount();
            }
        });

        document.addEventListener('click', async function (event) {
            const paginationLink = event.target.closest('#pmbQueueRows .pagination a');
            if (paginationLink) {
                event.preventDefault();
                const target = new URL(paginationLink.href, window.location.origin);
                params = new URLSearchParams(target.search);
                if (!params.has('view')) {
                    params.set('view', 'peserta');
                }
                window.history.replaceState({}, '', window.location.pathname + (params.toString() ? '?' + params.toString() : ''));
                await refreshQueue();
                return;
            }

            const checkInButton = event.target.closest('.pmb-bulk-check-in');
            if (checkInButton) {
                const checked = Array.from(document.querySelectorAll('.pmb-queue-checkbox:checked')).map((checkbox) => checkbox.value);
                if (!checked.length) {
                    showAjaxAlert('Pilih minimal satu peserta.', false);
                    return;
                }

                checkInButton.setAttribute('disabled', 'disabled');

                try {
                    const formData = new FormData();
                    checked.forEach((id) => formData.append('queue_ids[]', id));

                    const response = await fetch(bulkCheckInEndpoint, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });
                    const responseText = await response.text();
                    let data = {};

                    try {
                        data = responseText ? JSON.parse(responseText) : {};
                    } catch (parseError) {
                        const fallbackMessages = {
                            403: 'Akun Anda tidak memiliki akses untuk melakukan check-in.',
                            419: 'Sesi login sudah berakhir. Muat ulang halaman lalu coba kembali.',
                            500: 'Terjadi kesalahan pada server saat memproses check-in.',
                        };

                        throw new Error(fallbackMessages[response.status] || 'Respons server tidak dapat dibaca.');
                    }

                    replaceQueueFragments(data);
                    showAjaxAlert(
                        data.message || (response.ok ? 'Check-in berhasil diproses.' : 'Check-in gagal diproses.'),
                        response.ok && data.ok !== false,
                        data.errors || []
                    );
                } catch (error) {
                    console.warn('Bulk check-in failed', error);
                    showAjaxAlert(error.message || 'Bulk check-in gagal diproses.', false);
                } finally {
                    checkInButton.removeAttribute('disabled');
                }

                return;
            }

            const healthNotifiedButton = event.target.closest('.pmb-bulk-health-notified');
            if (healthNotifiedButton) {
                const checked = Array.from(document.querySelectorAll('.pmb-queue-checkbox:checked')).map((checkbox) => checkbox.value);
                if (!checked.length) {
                    showAjaxAlert('Pilih minimal satu peserta online.', false);
                    return;
                }
                const formData = new FormData();
                checked.forEach((id) => formData.append('queue_ids[]', id));
                const response = await fetch(bulkHealthNotifiedEndpoint, {
                    method: 'POST', body: formData,
                    headers: {'Accept': 'text/html', 'X-CSRF-TOKEN': csrfToken},
                });
                if (response.ok) {
                    showAjaxAlert('Waktu pemberitahuan tersimpan. Tenggat H+7 mulai dihitung.', true);
                    await refreshQueue();
                } else {
                    showAjaxAlert('Pemberitahuan hanya dapat diterapkan pada peserta online.', false);
                }
                return;
            }

            const button = event.target.closest('.pmb-bulk-action');
            if (!button) return;

            const checked = Array.from(document.querySelectorAll('.pmb-queue-checkbox:checked')).map((checkbox) => checkbox.value);
            if (!checked.length) {
                showAjaxAlert('Pilih minimal satu peserta.', false);
                return;
            }

            button.setAttribute('disabled', 'disabled');

            try {
                const formData = new FormData();
                formData.set('action', button.dataset.action);
                checked.forEach((id) => formData.append('queue_ids[]', id));

                const response = await fetch(bulkEndpoint, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });
                const data = await response.json();
                replaceQueueFragments(data);
                showAjaxAlert(data.message, response.ok && data.ok !== false, data.errors || []);
            } catch (error) {
                console.warn('Bulk queue action failed', error);
                showAjaxAlert('Bulk update gagal diproses.', false);
            } finally {
                button.removeAttribute('disabled');
            }
        });

        // Helper to close all room forms
        function closeAllRoomForms() {
            ['pmbRoomFormCollapse-health', 'pmbRoomFormCollapse-interview'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.classList.add('d-none');
                }
            });
        }

        // Handle Tambah Loket Click
        document.addEventListener('click', function (event) {
            const addBtn = event.target.closest('.pmb-add-room-btn');
            if (!addBtn) return;

            event.preventDefault();
            const stageId = addBtn.dataset.stageId;
            const stageName = addBtn.dataset.stageName;
            
            // Determine which form (health or interview)
            const isHealth = stageName.toLowerCase().includes('kesehatan');
            const collapseId = isHealth ? 'pmbRoomFormCollapse-health' : 'pmbRoomFormCollapse-interview';
            const collapseEl = document.getElementById(collapseId);
            if (!collapseEl) return;

            const form = collapseEl.querySelector('form');
            if (!form) return;

            // Reset form fields
            form.reset();
            form.querySelector('.pmb-room-method').value = 'POST';
            form.action = roomStoreEndpoint;

            collapseEl.querySelector('.pmb-room-form-title').textContent = 'Tambah Loket ' + stageName;
            form.querySelector('.pmb-room-alert')?.classList.add('d-none');

            // Close other forms and open this one
            closeAllRoomForms();
            collapseEl.classList.remove('d-none');
            
            // Focus first input
            setTimeout(() => form.querySelector('.pmb-room-officer, .pmb-room-name')?.focus(), 200);
        });

        // Handle Edit Loket Click (Event Delegation)
        document.addEventListener('click', function (event) {
            const editBtn = event.target.closest('.pmb-edit-room-trigger');
            if (!editBtn) return;

            event.preventDefault();
            
            let room = null;
            try {
                room = JSON.parse(editBtn.dataset.room);
            } catch (e) {
                console.error('Failed to parse room data', e);
                return;
            }

            // Determine which form based on edit button parent container
            const isHealth = editBtn.closest('#pmbQueueLanesHealth') !== null;
            const collapseId = isHealth ? 'pmbRoomFormCollapse-health' : 'pmbRoomFormCollapse-interview';
            const collapseEl = document.getElementById(collapseId);
            if (!collapseEl) return;

            const form = collapseEl.querySelector('form');
            if (!form) return;

            form.querySelector('.pmb-room-method').value = 'PUT';
            form.action = roomUpdateUrlTemplate.replace('__ROOM_UUID__', room.uuid);

            // Populate fields
            const nameInput = form.querySelector('.pmb-room-name');
            const officerInput = form.querySelector('.pmb-room-officer');
            if (nameInput) nameInput.value = room.name;
            if (officerInput) officerInput.value = room.assigned_admin_id || '';
            form.querySelector('.pmb-room-status').value = room.status;
            
            collapseEl.querySelector('.pmb-room-form-title').textContent = 'Edit Loket';
            form.querySelector('.pmb-room-alert')?.classList.add('d-none');

            // Close other forms and open this one
            closeAllRoomForms();
            collapseEl.classList.remove('d-none');

            // Focus first input
            setTimeout(() => form.querySelector('.pmb-room-officer, .pmb-room-name')?.focus(), 200);
        });

        // Handle Batal/Close Button Click
        document.addEventListener('click', function (event) {
            const btn = event.target.closest('.pmb-room-form-close, .pmb-room-form-cancel');
            if (!btn) return;

            event.preventDefault();
            const collapseEl = btn.closest('.pmb-room-form-container');
            if (collapseEl) {
                collapseEl.classList.add('d-none');
            }
        });

        // Handle Room Form Submit (Event Delegation)
        document.addEventListener('submit', async function (event) {
            const form = event.target.closest('.pmb-room-inline-form');
            if (!form) return;

            event.preventDefault();
            
            const submitBtn = form.querySelector('.pmb-room-form-submit');
            const alertEl = form.querySelector('.pmb-room-alert');
            const collapseEl = form.closest('.pmb-room-form-container');
            
            submitBtn?.setAttribute('disabled', 'disabled');
            alertEl?.classList.add('d-none');

            try {
                const formData = new FormData(form);
                formData.set('_method', form.querySelector('.pmb-room-method').value);

                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });

                const data = await response.json();

                if (response.ok && data.ok !== false) {
                    replaceQueueFragments(data);
                    showAjaxAlert(data.message || 'Loket berhasil disimpan.', true);
                    if (collapseEl) {
                        collapseEl.classList.add('d-none');
                    }
                } else {
                    let errMsg = data.message || 'Gagal menyimpan data loket.';
                    if (data.errors) {
                        const errorDetails = Object.values(data.errors).flat().join('<br>');
                        errMsg += '<div class="small mt-1">' + errorDetails + '</div>';
                    }
                    if (alertEl) {
                        alertEl.innerHTML = errMsg;
                        alertEl.classList.remove('d-none');
                    }
                }
            } catch (error) {
                console.error('Room save failed', error);
                if (alertEl) {
                    alertEl.textContent = 'Terjadi kesalahan sistem saat menyimpan loket.';
                    alertEl.classList.remove('d-none');
                }
            } finally {
                submitBtn?.removeAttribute('disabled');
            }
        });

        function updateRoomDropdowns() {
            const healthSelect = document.getElementById('pmbRoomSelectHealth');
            const interviewSelect = document.getElementById('pmbRoomSelectInterview');
            
            if (healthSelect) {
                const currentVal = healthSelect.value;
                healthSelect.innerHTML = '<option value="">Semua loket</option>';
                document.querySelectorAll('#pmbQueueLanesHealth .pmb-edit-room-trigger').forEach(btn => {
                    try {
                        const room = JSON.parse(btn.dataset.room);
                        const opt = document.createElement('option');
                        opt.value = room.id;
                        opt.textContent = room.name;
                        if (String(room.id) === currentVal) opt.selected = true;
                        healthSelect.appendChild(opt);
                    } catch (e) {}
                });
            }
            
            if (interviewSelect) {
                const currentVal = interviewSelect.value;
                interviewSelect.innerHTML = '<option value="">Semua loket</option>';
                document.querySelectorAll('#pmbQueueLanesInterview .pmb-edit-room-trigger').forEach(btn => {
                    try {
                        const room = JSON.parse(btn.dataset.room);
                        const opt = document.createElement('option');
                        opt.value = room.id;
                        opt.textContent = room.name;
                        if (String(room.id) === currentVal) opt.selected = true;
                        interviewSelect.appendChild(opt);
                    } catch (e) {}
                });
            }
        }

        updateSelectedCount();
        updateRoomDropdowns();
        window.setInterval(refreshQueue, 10000);
    })();
</script>
@endpush
