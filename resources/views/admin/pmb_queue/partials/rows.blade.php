<div class="table-responsive">
    <table class="table table-hover align-middle mb-0 pmb-queue-table">
        <thead>
            <tr>
                <th style="width: 42px;">
                    <input class="form-check-input" type="checkbox" id="pmbQueueSelectAll" aria-label="Pilih semua peserta">
                </th>
                <th>Peserta</th>
                <th>Tahap</th>
                <th>Lokasi</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($queues as $queue)
                @php
                    $activeStep = $queue->stageSteps->firstWhere('stage_id', $queue->current_stage_id)
                        ?? $queue->stageSteps->whereIn('status', [
                            \App\Models\PmbQueueStageStep::STATUS_WAITING,
                            \App\Models\PmbQueueStageStep::STATUS_CALLED,
                            \App\Models\PmbQueueStageStep::STATUS_PROCESSING,
                            \App\Models\PmbQueueStageStep::STATUS_HELD,
                            \App\Models\PmbQueueStageStep::STATUS_PROBLEM,
                        ])->sortBy(fn ($step) => $step->stage->sort_order ?? 999)->first();
                    $lastCompletedStep = $queue->stageSteps
                        ->where('status', \App\Models\PmbQueueStageStep::STATUS_COMPLETED)
                        ->sortByDesc(fn ($step) => $step->stage->sort_order ?? 0)
                        ->first();
                    $isQueueCompleted = $queue->status === \App\Models\PmbOfflineQueue::STATUS_COMPLETED;

                    $stageKey = $activeStep?->stage?->key;
                    $isInterview = $stageKey === \App\Models\PmbQueueStage::KEY_WAWANCARA_KAPRODI;
                    $isTesTulis = $stageKey === \App\Models\PmbQueueStage::KEY_TES_TULIS;
                    $isHealth = $stageKey === \App\Models\PmbQueueStage::KEY_TES_KESEHATAN;
                    $isHeldHealth = $isHealth && $activeStep?->status === \App\Models\PmbQueueStageStep::STATUS_HELD;
                    $canOperateActive = match ($stageKey) {
                        \App\Models\PmbQueueStage::KEY_TES_TULIS => $canOperateTesTulis ?? false,
                        \App\Models\PmbQueueStage::KEY_TES_KESEHATAN => $canOperateHealth ?? false,
                        \App\Models\PmbQueueStage::KEY_WAWANCARA_KAPRODI => $canOperateInterview ?? false,
                        default => $canManageQueue ?? false,
                    };
                    $completeAction = match ($stageKey) {
                        \App\Models\PmbQueueStage::KEY_TES_TULIS => 'complete_test_tulis',
                        \App\Models\PmbQueueStage::KEY_TES_KESEHATAN => 'complete_health',
                        default => 'complete',
                    };
                    $primaryLabel = match ($stageKey) {
                        \App\Models\PmbQueueStage::KEY_TES_TULIS => 'ACC Tes Tulis',
                        \App\Models\PmbQueueStage::KEY_TES_KESEHATAN => 'ACC Kesehatan',
                        \App\Models\PmbQueueStage::KEY_WAWANCARA_KAPRODI => 'Selesai Wawancara',
                        default => 'ACC Tahap',
                    };
                @endphp
                <tr>
                    <td>
                        <input class="form-check-input pmb-queue-checkbox" type="checkbox" value="{{ $queue->id }}" data-stage="{{ $stageKey }}" aria-label="Pilih {{ $queue->user->name ?? 'peserta' }}">
                    </td>
                    <td>
                        <div class="d-flex align-items-start gap-2">
                            <span class="badge bg-label-primary mt-1">{{ $queue->queue_code ?? 'Baru' }}</span>
                            <div>
                                <div class="fw-semibold text-dark">{{ $queue->user->name ?? '-' }}</div>
                                <div class="text-muted small">{{ $queue->user->jurusan->nama_jurusan ?? '-' }} - {{ $queue->user->email ?? '-' }}</div>
                                <span class="badge bg-label-{{ $queue->isOnlineSelection() ? 'warning' : 'secondary' }} mt-1">
                                    {{ $queue->selectionModeLabel() }}
                                </span>
                                @if(!$queue->requiresTesTulis())
                                    <span class="badge bg-label-info mt-1">Bebas Tes Tulis</span>
                                @else
                                    <span class="badge bg-label-primary mt-1">Tes Tulis</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($queue->isOnlineSelection())
                            <span class="badge bg-label-warning">Jalur online</span>
                            <div class="text-muted small mt-1">Tidak memakai check-in/loket fisik.</div>
                        @elseif($isQueueCompleted)
                            <div class="fw-semibold">Selesai PMB</div>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                <span class="badge bg-label-success">Selesai</span>
                                @if($lastCompletedStep)
                                    <span class="badge bg-label-secondary">{{ $lastCompletedStep->stage->name ?? 'Tahap akhir' }}</span>
                                @endif
                            </div>
                        @elseif($activeStep)
                            <div class="fw-semibold">{{ $activeStep->stage->name ?? '-' }}</div>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                <span class="badge bg-label-{{ $activeStep->statusTone() }}">{{ $activeStep->statusLabel() }}</span>
                                <span class="badge bg-label-secondary">{{ $queue->queue_code ?? '-' }}</span>
                            </div>
                        @else
                            <span class="badge bg-label-secondary">Belum check-in</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $onlineInterviewStep = $queue->isOnlineSelection()
                                ? $queue->stageSteps->first(fn ($step) => $step->stage?->key === \App\Models\PmbQueueStage::KEY_WAWANCARA_KAPRODI)
                                : null;
                            $eligibleInterviewRooms = $queue->isOnlineSelection()
                                ? $rooms->filter(function ($room) use ($queue) {
                                    if ($room->stage?->key !== \App\Models\PmbQueueStage::KEY_WAWANCARA_KAPRODI || !$room->assignedOfficer?->jurusan) return false;
                                    return in_array((int) $queue->user?->jurusan_id, $room->assignedOfficer->jurusan->interviewPoolIds(), true);
                                })
                                : collect();
                        @endphp
                        <div class="fw-semibold">{{ $queue->isOnlineSelection() ? ($onlineInterviewStep?->room?->name ?? 'Belum ada breakout') : ($queue->room?->displayName() ?? '-') }}</div>
                        @if($queue->isOnlineSelection())
                            <div class="text-muted small">{{ $onlineInterviewStep?->officer?->name ?? 'Dosen belum dipilih' }} · Urut {{ $onlineInterviewStep?->stage_queue_number ?? '-' }}</div>
                            <div class="text-muted small">Surat: {{ $queue->health_notified_at ? 'H+7 sampai ' . $queue->healthLetterDueAt()->format('d/m/Y H:i') : 'belum diberitahukan' }}</div>
                        @endif
                        @if($queue->checked_in_at || $queue->last_called_at)
                            <div class="text-muted small">
                                @if($queue->checked_in_at) Check-in {{ $queue->checked_in_at->format('H:i') }} @endif
                                @if($queue->last_called_at) - Panggil {{ $queue->last_called_at->format('H:i') }} @endif
                            </div>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-label-{{ $queue->statusTone() }}">{{ $queue->statusLabel() }}</span>
                    </td>
                    <td class="text-end">
                        @if(($canOperateActive ?? false) || ($canManualOverride ?? false))
                        <div class="d-flex flex-wrap justify-content-end gap-1">
                            @if($activeStep && ($canOperateActive ?? false) && !($isMonitorMode ?? false))
                                @php
                                    $isWaiting = $activeStep->status === \App\Models\PmbQueueStageStep::STATUS_WAITING;
                                @endphp

                                @if($isWaiting && ($isInterview || $isHealth || $isTesTulis))
                                    <span class="badge bg-label-secondary">Menunggu auto-dispatch</span>
                                @else
                                    @if($isHeldHealth)
                                        <form method="POST" action="{{ route('admin.pmb-queues.queue-status', $queue) }}" class="pmb-ajax-action" data-ajax-action="{{ route('admin.pmb-queues.queue-status-json', $queue) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="action" value="release_health_form">
                                            <input type="hidden" name="step_id" value="{{ $activeStep->id }}">
                                            <button class="btn btn-sm btn-primary" type="submit">Lanjut Kesehatan</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.pmb-queues.queue-status', $queue) }}" class="pmb-ajax-action" data-ajax-action="{{ route('admin.pmb-queues.queue-status-json', $queue) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="action" value="{{ $completeAction }}">
                                            <input type="hidden" name="step_id" value="{{ $activeStep->id }}">
                                            <button class="btn btn-sm btn-success" type="submit">{{ $primaryLabel }}</button>
                                        </form>
                                    @endif

                                    @if($isTesTulis)
                                        <form method="POST" action="{{ route('admin.pmb-queues.queue-status', $queue) }}" class="pmb-ajax-action" data-ajax-action="{{ route('admin.pmb-queues.queue-status-json', $queue) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="action" value="hold_health_form">
                                            <input type="hidden" name="step_id" value="{{ $activeStep->id }}">
                                            <button class="btn btn-sm btn-outline-warning" type="submit">Tahan</button>
                                        </form>
                                    @endif
                                @endif
                            @endif

                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-label="Aksi lain">
                                    <i class="bx bx-dots-horizontal-rounded"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @if($queue->isOfflineSelection())
                                        <li><a class="dropdown-item" href="{{ route('admin.pmb-queues.pass', $queue) }}" target="_blank">Kartu / QR Pass</a></li>
                                    @endif
                                    @if(($canManageQueue ?? false) && $queue->isOfflineSelection() && $queue->status === \App\Models\PmbOfflineQueue::STATUS_REGISTERED && !$queue->checked_in_at)
                                        <li>
                                            <form method="POST" action="{{ route('admin.pmb-queues.bulk-check-in', $session) }}" onsubmit="return confirm('Hadirkan peserta ini tanpa scan QR?')">
                                                @csrf
                                                <input type="hidden" name="queue_ids[]" value="{{ $queue->id }}">
                                                <button class="dropdown-item" type="submit">Hadirkan Tanpa Scan</button>
                                            </form>
                                        </li>
                                    @endif
                                    @if(($canManageOnlineSelection ?? false) && $queue->isOnlineSelection())
                                        <li>
                                            <form method="POST" action="{{ route('admin.pmb-queues.online.health-notified', $session) }}">
                                                @csrf
                                                <input type="hidden" name="queue_ids[]" value="{{ $queue->id }}">
                                                <button class="dropdown-item" type="submit">Diberitahukan Surat Kesehatan</button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li class="px-3 py-2" style="min-width: 310px;">
                                            <form method="POST" action="{{ route('admin.pmb-queues.online.interview-assignment', $queue) }}">
                                                @csrf
                                                <label class="form-label small">Dosen / Breakout Room</label>
                                                <select name="room_id" class="form-select form-select-sm mb-2" required>
                                                    <option value="">Pilih dosen</option>
                                                    @foreach($eligibleInterviewRooms as $room)
                                                        <option value="{{ $room->id }}" @selected($onlineInterviewStep?->room_id === $room->id)>{{ $room->assignedOfficer?->name ?? '-' }} · {{ $room->name }}</option>
                                                    @endforeach
                                                </select>
                                                <label class="form-label small">Nomor urut manual</label>
                                                <input type="number" name="stage_queue_number" min="1" class="form-control form-control-sm mb-2" value="{{ $onlineInterviewStep?->stage_queue_number }}" required>
                                                <button class="btn btn-sm btn-primary w-100" type="submit">Simpan Penugasan</button>
                                            </form>
                                        </li>
                                    @endif
                                    @if($canManualOverride ?? false)
                                     @php
                                        $actions = [];
                                        if (in_array($queue->status, [\App\Models\PmbOfflineQueue::STATUS_CANCELLED, \App\Models\PmbOfflineQueue::STATUS_NO_SHOW], true)) {
                                            $actions['reactivate'] = 'Aktifkan Kembali';
                                        } else {
                                            if ($activeStep && !($isMonitorMode ?? false)) {
                                                if ($isInterview || $isHealth) {
                                                    $actions['in_room'] = 'Mulai Proses';
                                                }
                                                if ($isInterview) {
                                                    $actions['recall'] = 'Recall';
                                                    $actions['skip'] = 'Skip';
                                                }
                                                $actions['hold'] = 'Tahan Peserta';
                                                $actions['release'] = 'Lepas Tahanan';
                                                $actions['problem'] = 'Bermasalah';
                                            }
                                            $actions['no_show'] = 'Tidak Hadir';
                                            $actions['cancel'] = 'Batalkan';
                                        }
                                    @endphp
                                    @foreach($actions as $action => $label)
                                        <li>
                                            <form method="POST" action="{{ route('admin.pmb-queues.queue-status', $queue) }}" class="pmb-ajax-action" data-ajax-action="{{ route('admin.pmb-queues.queue-status-json', $queue) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="action" value="{{ $action }}">
                                                @if($activeStep)
                                                    <input type="hidden" name="step_id" value="{{ $activeStep->id }}">
                                                @endif
                                                <button class="dropdown-item" type="submit">{{ $label }}</button>
                                            </form>
                                        </li>
                                    @endforeach
                                    @endif
                                    @if(($canManageQueue ?? false) && $queue->status === \App\Models\PmbOfflineQueue::STATUS_REGISTERED && (!$queue->isOnlineSelection() || $queue->overall_status === \App\Models\PmbOfflineQueue::OVERALL_NOT_STARTED))
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.pmb-queues.queue-status', $queue) }}" class="pmb-ajax-action" data-ajax-action="{{ route('admin.pmb-queues.queue-status-json', $queue) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="action" value="{{ $queue->requiresTesTulis() ? 'set_bebas_tes_tulis' : 'set_tes_tulis' }}">
                                                <button class="dropdown-item" type="submit">
                                                    {{ $queue->requiresTesTulis() ? 'Ubah ke Bebas Tes Tulis' : 'Ubah ke Tes Tulis' }}
                                                </button>
                                            </form>
                                        </li>
                                    @endif
                                    @if(($canManageQueue ?? false) && $queue->status === \App\Models\PmbOfflineQueue::STATUS_REGISTERED && (!$queue->isOnlineSelection() || $queue->overall_status === \App\Models\PmbOfflineQueue::OVERALL_NOT_STARTED))
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.pmb-queues.participants.destroy', $queue) }}" onsubmit="return confirm('Hapus peserta dari sesi?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="dropdown-item text-danger" type="submit">Hapus dari Sesi</button>
                                            </form>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="bx bx-user-x fs-2 d-block mb-2"></i>
                        Belum ada peserta pada filter ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="p-3 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 border-top">
    <div class="text-muted small">
        Menampilkan {{ $queues->firstItem() ?? 0 }}-{{ $queues->lastItem() ?? 0 }} dari {{ $queues->total() }} peserta
    </div>
    <div>
        {{ $queues->links() }}
    </div>
</div>
