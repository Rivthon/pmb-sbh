@php
    $stageKey = $stageKey ?? \App\Models\PmbQueueStage::KEY_WAWANCARA_KAPRODI;
    $stageRooms = $rooms->filter(fn ($room) => $room->stage?->key === $stageKey);
@endphp

<div class="pmb-room-list">
    @forelse($stageRooms as $room)
        @php
            $displayName = $room->displayName();
            $current = $room->stageSteps
                ->whereIn('status', [\App\Models\PmbQueueStageStep::STATUS_CALLED, \App\Models\PmbQueueStageStep::STATUS_PROCESSING])
                ->sortByDesc('called_at')
                ->first();
            $activeCount = $room->stageSteps
                ->whereIn('status', [\App\Models\PmbQueueStageStep::STATUS_CALLED, \App\Models\PmbQueueStageStep::STATUS_PROCESSING])
                ->count();
            $isUnavailable = in_array($room->status, [\App\Models\PmbTestRoom::STATUS_PAUSED, \App\Models\PmbTestRoom::STATUS_CLOSED], true);
            $isFull = !$isUnavailable && $activeCount >= $room->capacity;
            $roomLabel = $isUnavailable
                ? ($roomStatusOptions[$room->status] ?? $room->status)
                : ($isFull ? 'Penuh' : ($activeCount > 0 ? 'Proses' : 'Ready'));
            $roomTone = $isUnavailable ? 'secondary' : ($isFull ? 'warning' : ($activeCount > 0 ? 'info' : 'success'));
        @endphp
        <div class="pmb-room-item d-flex justify-content-between align-items-center border-bottom py-2">
            <div>
                <div class="fw-semibold">{{ $displayName }}</div>
                @if($stageKey === \App\Models\PmbQueueStage::KEY_TES_KESEHATAN && $room->assignedOfficer)
                    <div class="text-muted small">Akun: {{ $room->assignedOfficer->email }}</div>
                @endif
                <div class="text-muted small">
                    <span class="badge bg-label-{{ $roomTone }}">{{ $roomLabel }}</span>
                    <span class="ms-1">{{ $activeCount }}/{{ $room->capacity }} proses</span>
                </div>
                @if($current)
                    <div class="small mt-1">
                        <span class="badge bg-label-warning">{{ $current->queue->queue_code ?? '-' }}</span>
                        {{ $current->queue->user->name ?? '-' }}
                    </div>
                @endif
            </div>
            <div class="d-flex align-items-center gap-1">
                @if($canManageQueue ?? false)
                <form method="POST" action="{{ route('admin.pmb-queues.rooms.status', $room) }}" class="pmb-ajax-action">
                    @csrf
                    @method('PUT')
                    <select name="status" class="form-select form-select-sm" onchange="this.form.requestSubmit()" style="width: auto; min-width: 100px;">
                        @foreach($roomStatusOptions as $value => $label)
                            <option value="{{ $value }}" @selected($room->status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
                <button type="button" class="btn btn-sm btn-icon btn-outline-secondary pmb-edit-room-trigger" data-room='@json($room)' title="Edit Loket">
                    <i class="bx bx-edit-alt"></i>
                </button>
                <form method="POST" action="{{ route('admin.pmb-queues.rooms.destroy', $room) }}" class="pmb-ajax-action" onsubmit="return confirm('Hapus loket {{ $displayName }}?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus Loket">
                        <i class="bx bx-trash"></i>
                    </button>
                </form>
                @else
                <span class="badge bg-label-secondary">{{ $roomStatusOptions[$room->status] ?? $room->status }}</span>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center text-muted py-4">
            <i class="bx bx-door-open fs-2"></i>
            <div>Belum ada loket/ruangan untuk tahap ini.</div>
        </div>
    @endforelse
</div>
