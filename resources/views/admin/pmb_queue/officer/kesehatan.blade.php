@extends('admin_dashboard.layout.master')

@section('title', 'Petugas Kesehatan')

@section('content')
@php
    $participant = $currentStep?->queue?->user;
    $exam = $anamnesa?->pemeriksaan;
    $isD3Kebidanan = (bool) $participant?->jurusan?->isD3Kebidanan();
    $queueCode = $currentStep?->queue?->queue_code ?? $currentStep?->stage_queue_code ?? '-';
    $roomName = $currentStep?->room?->displayName() ?? 'Meja Kesehatan';
    $conditionOptions = ['normal' => 'Normal', 'kelainan' => 'Kelainan'];
    $anamnesaQuestions = \App\Models\TesKesehatanAnamnesa::officerAnamnesaQuestions();
    $autosaveAnamnesaUrl = $anamnesa ? route('admin.tes-kesehatan.anamnesa-autosave', $anamnesa->id) : null;
    $examSections = [
        'Mata' => [
            ['field' => 'konjungtiva', 'label' => 'Konjungtiva'],
            ['field' => 'ikhterik', 'label' => 'Ikhterik'],
            ['field' => 'buta_warna', 'label' => 'Buta Warna'],
        ],
        'Telinga' => [
            ['field' => 'respon_pendengaran', 'label' => 'Respon Pendengaran'],
        ],
        'Ekstremitas Atas' => [
            ['field' => 'kelengkapan_jari_atas', 'label' => 'Kelengkapan Jari'],
            ['field' => 'tremor', 'label' => 'Tremor'],
            ['field' => 'bekas_luka_sayatan', 'label' => 'Bekas Luka Sayatan'],
            ['field' => 'kidal', 'label' => 'Kidal'],
        ],
        'Hidung - Mulut' => [
            ['field' => 'labioschizis', 'label' => 'Labioschizis'],
            ['field' => 'vokal', 'label' => 'Vokal'],
        ],
        'Rambut' => [
            ['field' => 'cat_rambut', 'label' => 'Cat Rambut'],
        ],
        'Tyroid' => [
            ['field' => 'tyroid', 'label' => 'Tyroid'],
        ],
        'Jantung' => [
            ['field' => 'bunyi_irama_jantung', 'label' => 'Bunyi dan Irama Jantung'],
        ],
        'Paru' => [
            ['field' => 'bunyi_paru', 'label' => 'Bunyi Paru'],
            ['field' => 'respirasi', 'label' => 'Respirasi'],
        ],
        'Ekstremitas Bawah' => [
            ['field' => 'simetris_bawah', 'label' => 'Simetris'],
            ['field' => 'kelengkapan_jari_bawah', 'label' => 'Kelengkapan Jari'],
            ['field' => 'motorik_cara_jalan', 'label' => 'Motoric / Pergerakan / Cara Jalan'],
            ['field' => 'bentuk_kaki', 'label' => 'Bentuk Kaki (X/O)'],
        ],
    ];
@endphp

@push('head')
<style>
    .health-officer-page .health-hero { gap: 1rem; }
    .health-officer-page .health-actions .btn { min-height: 44px; }
    .health-officer-page .health-stat-card .card-body { min-height: 116px; }
    .health-officer-page .health-stat-icon { flex: 0 0 auto; }
    .health-officer-page .health-cta-card .btn,
    .health-officer-page .health-submit-actions .btn { min-height: 48px; }
    .health-officer-page .health-section-title {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin-bottom: 1rem;
        color: #566a7f;
        font-weight: 700;
    }
    .health-officer-page .health-exam-item {
        border: 1px solid #d9dee3;
        border-radius: .5rem;
        padding: 1rem;
        height: 100%;
        background: #fff;
    }
    .health-officer-page .health-exam-item .btn { min-height: 38px; }
    .health-officer-page .health-anamnesa-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: .75rem;
    }
    .health-officer-page .health-anamnesa-item {
        border: 1px solid #d9dee3;
        border-radius: .5rem;
        padding: 1rem;
        background: #fff;
    }
    .health-officer-page .health-anamnesa-number {
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        background: #f0f2ff;
        color: #696cff;
        font-weight: 700;
        font-size: .8rem;
    }
    .health-officer-page .health-anamnesa-item .btn { min-height: 40px; }
    .health-officer-page .health-autosave-status {
        min-height: 1rem;
        font-size: .75rem;
    }

    @media (max-width: 575.98px) {
        .health-officer-page { margin-inline: -0.25rem; }
        .health-officer-page .health-hero { margin-bottom: 1rem !important; }
        .health-officer-page .health-hero h4 { font-size: 1.25rem; }
        .health-officer-page .health-actions {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
        }
        .health-officer-page .health-actions .btn {
            justify-content: center;
            padding-inline: 0.75rem;
            width: 100%;
        }
        .health-officer-page .health-stats {
            --bs-gutter-x: .75rem;
            --bs-gutter-y: .75rem;
            margin-bottom: 1rem !important;
        }
        .health-officer-page .health-stat-card .card-body {
            min-height: 104px;
            padding: 1rem;
        }
        .health-officer-page .health-stat-card .small {
            font-size: .72rem;
            line-height: 1.25;
        }
        .health-officer-page .health-stat-card .h3 {
            font-size: 1.45rem;
            line-height: 1.1;
        }
        .health-officer-page .health-stat-icon { font-size: 1.5rem !important; }
        .health-officer-page .health-cta-card .card-body { padding: 1.5rem 1rem !important; }
        .health-officer-page .health-cta-card p {
            font-size: .9rem;
            margin-bottom: 1rem !important;
        }
        .health-officer-page .health-cta-card .btn,
        .health-officer-page .health-submit-actions .btn { width: 100%; }
        .health-officer-page .health-current-number {
            font-size: 2rem;
            line-height: 1.1;
        }
        .health-officer-page .health-form-card .card-header,
        .health-officer-page .health-form-card .card-body { padding-inline: 1rem; }
        .health-officer-page .health-form-card .form-control,
        .health-officer-page .health-form-card .form-select { min-height: 46px; }
        .health-officer-page .health-anamnesa-grid { grid-template-columns: 1fr; }
        .health-officer-page .health-anamnesa-item { padding: .875rem; }
        .health-officer-page .health-anamnesa-item .btn { min-height: 46px; }
        .health-officer-page .health-submit-actions {
            flex-direction: column-reverse;
        }
    }
</style>
@endpush

<div class="health-officer-page">
    <div class="health-hero d-flex flex-column flex-xl-row justify-content-between align-items-xl-center mb-4">
        <div>
            <span class="badge bg-label-primary mb-2">{{ $session->code }}</span>
            <h4 class="fw-bold mb-1">Petugas Kesehatan</h4>
            <p class="text-muted mb-0">{{ $roomName }}</p>
        </div>
        <div class="health-actions d-flex flex-wrap gap-2">
            <a href="{{ route('admin.pmb-queues.board', $session) }}" target="_blank" class="btn btn-dark d-inline-flex align-items-center">
                <i class="bx bx-tv me-1"></i> TV
            </a>
            <a href="{{ route('admin.pmb-queues.show', $session) }}" class="btn btn-outline-secondary d-inline-flex align-items-center">
                <i class="bx bx-list-ul me-1"></i> Monitor
            </a>
        </div>
    </div>

    @foreach(['success' => 'success', 'error' => 'danger', 'info' => 'info'] as $key => $tone)
        @if(session($key))
        <div class="alert alert-{{ $tone }} alert-dismissible fade show" role="alert">
            {{ session($key) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
    @endforeach

    @if($errors->any())
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1">Data pemeriksaan belum lengkap.</div>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="health-stats row g-3 mb-4">
        <div class="col-6 col-md-6 col-xl-3">
            <div class="health-stat-card card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <div class="text-muted small">Belum Tes Kesehatan</div>
                            <div class="h3 mb-0">{{ $stats['remaining'] }}</div>
                            <div class="text-muted small mt-1">dari {{ $stats['total_program'] }} peserta</div>
                        </div>
                        <i class="health-stat-icon bx bx-user-plus fs-2 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-6 col-xl-3">
            <div class="health-stat-card card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <div class="text-muted small">Menunggu Tes Kesehatan</div>
                            <div class="h3 mb-0">{{ $stats['waiting'] }}</div>
                        </div>
                        <i class="health-stat-icon bx bx-time-five fs-2 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-6 col-xl-3">
            <div class="health-stat-card card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <div class="text-muted small">Sedang Diproses</div>
                            <div class="h3 mb-0">{{ $stats['processing'] }}</div>
                        </div>
                        <i class="health-stat-icon bx bx-plus-medical fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-6 col-xl-3">
            <div class="health-stat-card card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <div class="text-muted small">Selesai Tes Kesehatan</div>
                            <div class="h3 mb-0">{{ $stats['completed'] }}</div>
                        </div>
                        <i class="health-stat-icon bx bx-check-circle fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!$currentStep)
    <div class="health-cta-card card">
        <div class="card-body text-center py-5">
            <h5 class="mb-2">Siap Memanggil Peserta?</h5>
            <p class="text-muted mb-4">Klik tombol di bawah untuk mengambil peserta berikutnya. Sistem akan memanggil peserta secara otomatis.</p>
            <form method="POST" action="{{ route('admin.pmb-queues.officer.kesehatan.ready', $session) }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-lg d-inline-flex align-items-center justify-content-center">
                    <i class="bx bx-user-check me-1"></i>Panggil Peserta
                </button>
            </form>
        </div>
    </div>
    @else
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-lg-8">
                    <div class="health-current-number display-6 fw-bold mb-2">{{ $queueCode }}</div>
                    <h5 class="mb-1">{{ $participant?->name ?? '-' }}</h5>
                    <div class="text-muted">{{ $participant?->jurusan?->nama_jurusan ?? '-' }}</div>
                    <span class="badge bg-label-{{ $currentStep->statusTone() }} mt-3">{{ $currentStep->statusLabel() }}</span>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="fw-semibold">{{ auth('admin')->user()->name }}</div>
                    <div class="text-muted small">{{ $roomName }}</div>
                    <div class="text-muted small">Petugas pemeriksa</div>
                </div>
            </div>
        </div>
    </div>

    @if(!$anamnesa)
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bx bx-error-circle fs-1 text-warning mb-3"></i>
            <h5 class="mb-2">Data Pemeriksaan Belum Siap</h5>
            <p class="text-muted mb-0">Muat ulang halaman atau panggil peserta kembali untuk menyiapkan form pemeriksaan fisik.</p>
        </div>
    </div>
    @else
    <div class="health-form-card card">
        <div class="card-header">
            <h5 class="mb-1">Pemeriksaan Fisik</h5>
            <p class="text-muted mb-0">Isi hasil pemeriksaan sesuai formulir kesehatan resmi STIKES Bogor Husada.</p>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.tes-kesehatan.store-pemeriksaan', $anamnesa->id) }}" method="POST">
                @csrf
                <input type="hidden" data-anamnesa-autosave-url value="{{ $autosaveAnamnesaUrl }}">
                <input type="hidden" name="queue_uuid" value="{{ $currentStep->queue->uuid }}">
                <input type="hidden" name="nama_pemeriksa" value="{{ auth('admin')->user()->name }}">

                <div class="border-bottom pb-4 mb-4">
                    <div class="health-section-title"><i class="bx bx-id-card"></i> Identitas Pemeriksaan</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Pemeriksa</label>
                            <input type="text" class="form-control" value="{{ auth('admin')->user()->name }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Pemeriksaan</label>
                            <input type="date" name="tanggal_pemeriksaan" class="form-control" value="{{ old('tanggal_pemeriksaan', $exam->tanggal_pemeriksaan ?? now()->toDateString()) }}" required>
                        </div>
                    </div>
                </div>

                <div class="border-bottom pb-4 mb-4">
                    <div class="health-section-title"><i class="bx bx-clipboard"></i> Anamnesa</div>
                    <div class="health-anamnesa-grid">
                        @foreach($anamnesaQuestions as $index => $question)
                            @php
                                $field = $question['field'];
                                $noteField = "{$field}_keterangan";
                                $rawAnswer = old($field, data_get($anamnesa, $field));
                                $currentAnswer = $rawAnswer === null || $rawAnswer === '' ? null : (int) $rawAnswer;
                                $showNote = $currentAnswer === 1;
                            @endphp
                            <div class="health-anamnesa-item" data-anamnesa-item data-field="{{ $field }}">
                                <div class="d-flex align-items-start gap-2 mb-3">
                                    <span class="health-anamnesa-number">{{ $index + 1 }}</span>
                                    <label class="form-label fw-semibold mb-0">{{ $question['label'] }}</label>
                                </div>
                                <div class="d-grid d-sm-flex gap-2 mb-3">
                                    @foreach([1 => 'Ya', 0 => 'Tidak'] as $value => $label)
                                        @php
                                            $inputId = 'anamnesa_' . $field . '_' . $value;
                                        @endphp
                                        <input class="btn-check" type="radio" name="{{ $field }}" id="{{ $inputId }}" value="{{ $value }}" @checked($currentAnswer === $value)>
                                        <label class="btn btn-outline-{{ $value === 1 ? 'danger' : 'success' }}" for="{{ $inputId }}">{{ $label }}</label>
                                    @endforeach
                                </div>
                                <div data-anamnesa-note @class(['health-anamnesa-note', 'd-none' => !$showNote])>
                                    <label class="form-label">Keterangan</label>
                                    <input type="text" name="{{ $noteField }}" class="form-control" placeholder="Isi keterangan jika Ya" value="{{ old($noteField, data_get($anamnesa, $noteField)) }}" data-anamnesa-note-input>
                                </div>
                                <div class="health-autosave-status text-muted mt-2" data-anamnesa-autosave-status></div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="border-bottom pb-4 mb-4">
                    <div class="health-section-title"><i class="bx bx-body"></i> Pemeriksaan Fisik</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Tinggi Badan (cm)</label>
                            <input type="number" step="0.1" inputmode="decimal" name="tinggi_badan" class="form-control" value="{{ old('tinggi_badan', $exam->tinggi_badan ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Berat Badan (kg)</label>
                            <input type="number" step="0.1" inputmode="decimal" name="berat_badan" class="form-control" value="{{ old('berat_badan', $exam->berat_badan ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tekanan Darah (mmHg)</label>
                            @php
                                $bloodPressure = old('tekanan_darah', $exam->tekanan_darah ?? '');
                                $bloodPressureParts = str_contains((string) $bloodPressure, '/')
                                    ? explode('/', (string) $bloodPressure, 2)
                                    : [null, null];
                            @endphp
                            <input type="hidden" name="tekanan_darah" value="{{ $bloodPressure }}">
                            <div class="input-group">
                                <input type="number" name="tekanan_darah_sistolik" class="form-control" inputmode="numeric" min="0" max="300" placeholder="120" value="{{ old('tekanan_darah_sistolik', $bloodPressureParts[0]) }}" aria-label="Sistolik">
                                <span class="input-group-text">/</span>
                                <input type="number" name="tekanan_darah_diastolik" class="form-control" inputmode="numeric" min="0" max="200" placeholder="80" value="{{ old('tekanan_darah_diastolik', $bloodPressureParts[1]) }}" aria-label="Diastolik">
                            </div>
                        </div>
                    </div>
                </div>

                @foreach($examSections as $sectionTitle => $items)
                <div class="border-bottom pb-4 mb-4">
                    <div class="health-section-title"><i class="bx bx-plus-medical"></i> {{ $sectionTitle }}</div>
                    <div class="row g-3">
                        @foreach($items as $examItem)
                            @php
                                $field = $examItem['field'];
                                $conditionField = "{$field}_kondisi";
                                $noteField = "{$field}_keterangan";
                                $currentCondition = old($conditionField, data_get($exam, $conditionField));
                            @endphp
                            <div class="col-lg-6">
                                <div class="health-exam-item">
                                    <label class="form-label fw-semibold">{{ $examItem['label'] }}</label>
                                    <div class="d-grid d-sm-flex gap-2 mb-3">
                                        @foreach($conditionOptions as $value => $label)
                                            @php
                                                $inputId = $field . '_' . $value;
                                            @endphp
                                            <input class="btn-check" type="radio" name="{{ $conditionField }}" id="{{ $inputId }}" value="{{ $value }}" @checked($currentCondition === $value)>
                                            <label class="btn btn-outline-{{ $value === 'normal' ? 'success' : 'warning' }}" for="{{ $inputId }}">{{ $label }}</label>
                                        @endforeach
                                    </div>
                                    <input type="text" name="{{ $noteField }}" class="form-control" placeholder="Keterangan" value="{{ old($noteField, data_get($exam, $noteField)) }}">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

                @if($isD3Kebidanan)
                @php
                    $currentUrine = old('tes_urine_kondisi', data_get($exam, 'tes_urine_kondisi'));
                @endphp
                <div class="border-bottom pb-4 mb-4">
                    <div class="health-section-title"><i class="bx bx-test-tube"></i> Tes Urine (Kebidanan)</div>
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <div class="health-exam-item">
                                <label class="form-label fw-semibold">Tes Urine</label>
                                <div class="d-grid d-sm-flex gap-2 mb-3">
                                    @foreach($conditionOptions as $value => $label)
                                        @php
                                            $inputId = 'tes_urine_' . $value;
                                        @endphp
                                        <input class="btn-check" type="radio" name="tes_urine_kondisi" id="{{ $inputId }}" value="{{ $value }}" @checked($currentUrine === $value)>
                                        <label class="btn btn-outline-{{ $value === 'normal' ? 'success' : 'warning' }}" for="{{ $inputId }}">{{ $label }}</label>
                                    @endforeach
                                </div>
                                <input type="text" name="tes_urine_keterangan" class="form-control" placeholder="Keterangan" value="{{ old('tes_urine_keterangan', data_get($exam, 'tes_urine_keterangan')) }}">
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Rekomendasi</label>
                        <select name="rekomendasi" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            <option value="layak" @selected(old('rekomendasi', $exam->rekomendasi ?? '') === 'layak')>Dapat mengikuti pendidikan</option>
                            <option value="tidak layak" @selected(old('rekomendasi', $exam->rekomendasi ?? '') === 'tidak layak')>Tidak dapat mengikuti pendidikan</option>
                            <option value="perlu pemeriksaan lanjutan" @selected(old('rekomendasi', $exam->rekomendasi ?? '') === 'perlu pemeriksaan lanjutan')>Perlu Pemeriksaan Lanjutan</option>
                        </select>
                    </div>
                </div>

                <div class="health-submit-actions d-flex flex-wrap justify-content-end gap-2 mt-4">
                    <button type="submit" name="submit_action" value="save_only" class="btn btn-outline-secondary btn-lg d-inline-flex align-items-center justify-content-center">
                        <i class="bx bx-save me-1"></i> Simpan Saja
                    </button>
                    <button type="submit" name="submit_action" value="save_and_call_next" class="btn btn-success btn-lg d-inline-flex align-items-center justify-content-center">
                        <i class="bx bx-save me-1"></i> Simpan & Panggil Berikutnya
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
    @endif
</div>
@endsection

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const page = document.querySelector('.health-officer-page');

    if (!page) {
        return;
    }

    const autosaveUrlInput = page.querySelector('[data-anamnesa-autosave-url]');
    const autosaveUrl = autosaveUrlInput ? autosaveUrlInput.value : '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')
        ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        : '{{ csrf_token() }}';

    const setAutosaveStatus = function (item, message, className) {
        const status = item.querySelector('[data-anamnesa-autosave-status]');

        if (!status) {
            return;
        }

        status.classList.remove('text-muted', 'text-success', 'text-danger');
        status.classList.add(className || 'text-muted');
        status.textContent = message || '';
    };

    const syncAnamnesaNote = function (item) {
        const checked = item.querySelector('input[type="radio"]:checked');
        const noteWrap = item.querySelector('[data-anamnesa-note]');
        const noteInput = noteWrap ? noteWrap.querySelector('input') : null;
        const shouldShow = checked && checked.value === '1';

        if (!noteWrap) {
            return;
        }

        noteWrap.classList.toggle('d-none', !shouldShow);

        if (!shouldShow && noteInput) {
            noteInput.value = '';
        }
    };

    const autosaveAnamnesa = function (item) {
        const field = item.getAttribute('data-field');
        const checked = item.querySelector('input[type="radio"]:checked');
        const noteInput = item.querySelector('[data-anamnesa-note-input]');

        if (!autosaveUrl || !field || !checked) {
            return;
        }

        const formData = new FormData();
        formData.append('field', field);
        formData.append('value', checked.value);
        formData.append('keterangan', checked.value === '1' && noteInput ? noteInput.value : '');

        setAutosaveStatus(item, 'Menyimpan...', 'text-muted');

        fetch(autosaveUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Autosave failed');
                }

                return response.json();
            })
            .then(function () {
                setAutosaveStatus(item, 'Tersimpan', 'text-success');
            })
            .catch(function () {
                setAutosaveStatus(item, 'Gagal', 'text-danger');
            });
    };

    const scheduleAutosave = function (item) {
        window.clearTimeout(item._anamnesaAutosaveTimer);
        item._anamnesaAutosaveTimer = window.setTimeout(function () {
            autosaveAnamnesa(item);
        }, 500);
    };

    page.querySelectorAll('[data-anamnesa-item]').forEach(function (item) {
        syncAnamnesaNote(item);

        item.addEventListener('change', function (event) {
            if (event.target.matches('input[type="radio"]')) {
                syncAnamnesaNote(item);
                scheduleAutosave(item);
            }
        });

        const noteInput = item.querySelector('[data-anamnesa-note-input]');
        if (noteInput) {
            noteInput.addEventListener('input', function () {
                scheduleAutosave(item);
            });
        }
    });
});
</script>
@endpush
