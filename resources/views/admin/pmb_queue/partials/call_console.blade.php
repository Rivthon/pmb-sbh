@php
    $healthStage = $stages->where('is_active', true)->firstWhere('key', \App\Models\PmbQueueStage::KEY_TES_KESEHATAN);
    $interviewStage = $stages->where('is_active', true)->firstWhere('key', \App\Models\PmbQueueStage::KEY_WAWANCARA_KAPRODI);
    $health = $boardPayload['health'] ?? null;
    $healthWaiting = collect($health['waiting'] ?? [])->take(5);
    $healthRooms = collect($health['rooms'] ?? []);
    $interviewLanes = collect($boardPayload['stages'] ?? [])
        ->where('key', \App\Models\PmbQueueStage::KEY_WAWANCARA_KAPRODI)
        ->values();
@endphp

<div class="card pmb-call-console-card">
    <div class="card-header d-flex flex-column flex-xl-row justify-content-between gap-3">
        <div>
            <h5 class="mb-1">Alur Otomatis Petugas</h5>
            <small class="text-muted">Dashboard ini memantau antrean. Pemanggilan diproses otomatis oleh sistem ketika halaman petugas kesehatan/dosen siap.</small>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="#daftar-peserta" class="btn btn-sm btn-outline-secondary">
                <i class="bx bx-list-ul me-1"></i> Daftar Peserta
            </a>
            <a href="#loket" class="btn btn-sm btn-outline-secondary">
                <i class="bx bx-door-open me-1"></i> Loket
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-xl-6">
                <div class="pmb-call-panel h-100">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                        <div>
                            <h6 class="fw-semibold mb-1">Tes Kesehatan</h6>
                            <div class="text-muted small">{{ $healthWaiting->count() }} peserta menunggu petugas kesehatan</div>
                        </div>
                        @if($canOperateHealth ?? false)
                            <span class="badge bg-label-success">Auto-dispatch aktif</span>
                        @endif
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @forelse($healthRooms as $room)
                            <span class="badge bg-label-{{ ($room['display_tone'] ?? 'ready') === 'ready' ? 'success' : (($room['display_tone'] ?? '') === 'busy' ? 'info' : (($room['display_tone'] ?? '') === 'full' ? 'warning' : 'secondary')) }}">
                                {{ $room['name'] ?? '-' }}: {{ $room['display_status'] ?? '-' }} {{ $room['active_count'] ?? 0 }}/{{ $room['capacity'] ?? 1 }}
                            </span>
                        @empty
                            <span class="badge bg-label-secondary">Ruang Tes Kesehatan, Lantai 2</span>
                        @endforelse
                    </div>

                    <div class="pmb-next-list">
                        @forelse($healthWaiting as $candidate)
                            <div class="pmb-next-item">
                                <div class="d-flex align-items-start gap-2 pmb-next-main">
                                    <span class="badge bg-label-primary">{{ $candidate['queue_code'] ?? '-' }}</span>
                                    <div>
                                        <div class="fw-semibold">{{ data_get($candidate, 'participant.name', '-') }}</div>
                                        <div class="text-muted small">{{ data_get($candidate, 'participant.program_studi', '-') }} - {{ $candidate['status_label'] ?? 'Menunggu' }}</div>
                                    </div>
                                </div>
                                @if($canOperateHealth ?? false)
                                    <span class="badge bg-label-secondary">Menunggu sistem</span>
                                @endif
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="bx bx-time-five fs-3 d-block mb-1"></i>
                                Belum ada peserta menunggu Tes Kesehatan.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="pmb-call-panel h-100">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                        <div>
                            <h6 class="fw-semibold mb-1">Wawancara</h6>
                                <div class="text-muted small">{{ $interviewLanes->sum(fn ($lane) => data_get($lane, 'counts.waiting', 0)) }} peserta menunggu dosen wawancara</div>
                        </div>
                        @if($canOperateInterview ?? false)
                            <span class="badge bg-label-success">Auto-dispatch aktif</span>
                        @endif
                    </div>

                    <div class="row g-3">
                        @forelse($interviewLanes as $lane)
                            @php
                                $laneRoom = collect($lane['rooms'] ?? [])->first();
                                $waiting = collect($lane['waiting'] ?? [])->take(3);
                            @endphp
                            <div class="col-12">
                                <div class="pmb-interview-lane">
                                    <div class="d-flex justify-content-between gap-2 mb-2">
                                        <div class="fw-semibold">{{ $lane['name'] ?? 'Wawancara' }}</div>
                                        @if($laneRoom)
                                            <span class="badge bg-label-{{ ($laneRoom['display_tone'] ?? 'ready') === 'ready' ? 'success' : (($laneRoom['display_tone'] ?? '') === 'busy' ? 'info' : (($laneRoom['display_tone'] ?? '') === 'full' ? 'warning' : 'secondary')) }}">
                                                {{ $laneRoom['display_status'] ?? '-' }} {{ $laneRoom['active_count'] ?? 0 }}/{{ $laneRoom['capacity'] ?? 1 }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="pmb-next-list">
                                        @forelse($waiting as $candidate)
                                            <div class="pmb-next-item">
                                                <div class="d-flex align-items-start gap-2 pmb-next-main">
                                                    <span class="badge bg-label-primary">{{ $candidate['queue_code'] ?? '-' }}</span>
                                                    <div>
                                                        <div class="fw-semibold">{{ data_get($candidate, 'participant.name', '-') }}</div>
                                                        <div class="text-muted small">{{ data_get($candidate, 'participant.program_studi', '-') }}</div>
                                                    </div>
                                                </div>
                                                @if($canOperateInterview ?? false)
                                                    <span class="badge bg-label-secondary">Menunggu sistem</span>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="text-muted small py-2">Belum ada peserta menunggu di loket ini.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="text-center text-muted py-4">
                                    <i class="bx bx-time-five fs-3 d-block mb-1"></i>
                                    Belum ada lane Wawancara aktif.
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
