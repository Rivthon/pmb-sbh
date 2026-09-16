<?php

namespace App\Services\Pmb;

use App\Events\PmbQueueUpdated;
use App\Models\PmbOfflineQueue;
use App\Models\Admin;
use App\Models\PmbQueueCallLog;
use App\Models\PmbQueueCounter;
use App\Models\PmbQueueStage;
use App\Models\PmbQueueStageLog;
use App\Models\PmbQueueStageStep;
use App\Models\PmbQueueStatusLog;
use App\Models\PmbTestRoom;
use App\Models\PmbTestSession;
use App\Services\Audit\ActivityLogger;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PmbOfflineQueueWorkflowService
{
    private const HEALTH_ROOM_CODE = 'KES-SHARED';
    private const HEALTH_ROOM_NAME = 'Ruang Tes Kesehatan, Lantai 2';
    public const NOTES_FLAG_SKIP_TES_TULIS = '[jalur:bebas_tes_tulis]';

    public function ensureDefaultStages(PmbTestSession $session): Collection
    {
        foreach (PmbQueueStage::defaults() as $key => $stage) {
            PmbQueueStage::firstOrCreate(
                ['session_id' => $session->id, 'key' => $key],
                [
                    'name' => $stage['name'],
                    'sort_order' => $stage['sort_order'],
                    'is_required' => $stage['is_required'],
                    'is_active' => true,
                ]
            );
        }

        $this->ensureHealthRoom($session);
        $this->ensureInterviewRooms($session);

        return $session->stages()->get();
    }

    public function ensureSteps(PmbOfflineQueue $queue): Collection
    {
        $stages = $this->ensureDefaultStages($queue->session);

        foreach ($stages as $stage) {
            PmbQueueStageStep::firstOrCreate(
                ['queue_id' => $queue->id, 'stage_id' => $stage->id],
                ['status' => PmbQueueStageStep::STATUS_PENDING]
            );
        }

        return $queue->stageSteps()->with(['stage', 'room'])->get();
    }

    public function checkIn(PmbOfflineQueue $queue, ?int $adminId = null): PmbOfflineQueue
    {
        return DB::transaction(function () use ($queue, $adminId): PmbOfflineQueue {
            $locked = PmbOfflineQueue::with(['session', 'stageSteps.stage'])
                ->whereKey($queue->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->isOnlineSelection()) {
                throw new DomainException('Peserta mode online tidak menggunakan check-in/antrian fisik kampus.');
            }

            $this->assertQueueCanCheckIn($locked);

            if ($locked->checked_in_at) {
                $this->ensureSteps($locked);

                return $locked->fresh(['user.jurusan', 'room', 'session', 'currentStage', 'stageSteps.stage']);
            }

            $nextNumber = $this->nextArrivalNumber($locked->session_id);

            $stages = $this->ensureDefaultStages($locked->session)->where('is_active', true);
            $pemberkasanStage = $stages->firstWhere('key', PmbQueueStage::KEY_PEMBERKASAN);
            $tesTulisStage = $stages->firstWhere('key', PmbQueueStage::KEY_TES_TULIS);
            $healthStage = $stages->firstWhere('key', PmbQueueStage::KEY_TES_KESEHATAN);
            $firstStage = $this->queueSkipsTesTulis($locked) && $healthStage
                ? $healthStage
                : ($tesTulisStage ?: $stages->first(fn (PmbQueueStage $stage) => $stage->id !== $pemberkasanStage?->id));
            $firstStage ??= $pemberkasanStage ?: $stages->first();
            $fromStatus = $locked->status;

            $firstStageRoomId = null;
            if ($firstStage) {
                $firstStageStep = $locked->stageSteps()->where('stage_id', $firstStage->id)->first();
                $firstStageRoomId = $firstStageStep?->room_id ?: $firstStage->default_room_id;
            }

            $locked->update([
                'queue_number' => $nextNumber,
                'queue_code' => $this->queueCodeFor($locked, $nextNumber),
                'status' => $firstStage ? PmbOfflineQueue::STATUS_WAITING : PmbOfflineQueue::STATUS_WAITING,
                'arrival_status' => PmbOfflineQueue::ARRIVAL_CHECKED_IN,
                'overall_status' => PmbOfflineQueue::OVERALL_IN_PROCESS,
                'current_stage_id' => $firstStage?->id,
                'room_id' => $firstStageRoomId,
                'entered_at' => null,
                'checked_in_at' => now(),
                'checked_in_by_admin_id' => $adminId,
            ]);

            $this->writeLegacyStatusLog(
                $locked,
                $fromStatus,
                PmbOfflineQueue::STATUS_WAITING,
                $adminId,
                $firstStage ? 'Peserta check-in dan masuk antrean tahap ' . $firstStage->name . '.' : 'Peserta check-in kedatangan.'
            );
            $this->ensureSteps($locked);

            if ($pemberkasanStage && $firstStage?->id !== $pemberkasanStage->id) {
                $pemberkasanStep = $locked->stageSteps()->where('stage_id', $pemberkasanStage->id)->lockForUpdate()->first();
                if ($pemberkasanStep && $pemberkasanStep->status !== PmbQueueStageStep::STATUS_COMPLETED) {
                    $this->transitionStep($pemberkasanStep, PmbQueueStageStep::STATUS_COMPLETED, $adminId, 'check_in_pemberkasan', [
                        'queued_at' => now(),
                        'completed_at' => now(),
                        'notes' => 'Pemberkasan awal dicek bersamaan dengan check-in kedatangan.',
                    ]);
                }
            }

            if ($firstStage) {
                $step = $locked->stageSteps()->where('stage_id', $firstStage->id)->lockForUpdate()->first();
                $number = $step->stage_queue_number ?: $this->nextStageNumber($step->stage);
                
                $this->transitionStep($step, PmbQueueStageStep::STATUS_WAITING, $adminId, 'check_in_waiting', [
                    'stage_queue_number' => $number,
                    'stage_queue_code' => $this->stageCode($step->stage, $number),
                    'queued_at' => now(),
                    'room_id' => $firstStageRoomId,
                    'notes' => 'Peserta menunggu panggilan ' . $firstStage->name . ' setelah check-in.',
                ]);
            }

            if ($this->queueSkipsTesTulis($locked) && $tesTulisStage && $firstStage?->id !== $tesTulisStage->id) {
                $tesTulisStep = $locked->stageSteps()->where('stage_id', $tesTulisStage->id)->lockForUpdate()->first();
                if ($tesTulisStep && $tesTulisStep->status !== PmbQueueStageStep::STATUS_SKIPPED) {
                    $this->transitionStep($tesTulisStep, PmbQueueStageStep::STATUS_SKIPPED, $adminId, 'skip_tes_tulis', [
                        'skipped_at' => now(),
                        'notes' => 'Peserta jalur bebas Tes Tulis, langsung masuk Tes Kesehatan.',
                    ]);
                }
            }

            if ($firstStage?->key === PmbQueueStage::KEY_TES_TULIS) {
                $this->autoStartTesTulisInTransaction($locked->session, $adminId);
            }

            event(new PmbQueueUpdated($locked->fresh(['user.jurusan', 'room', 'session', 'currentStage']), 'waiting'));
            $this->writeActivityLog($locked, 'check_in', 'Peserta check-in antrian offline PMB.', [
                'queue_number' => $nextNumber,
                'stage_id' => $firstStage?->id,
            ]);

            return $locked->fresh(['user.jurusan', 'room', 'session', 'currentStage', 'stageSteps.stage']);
        });
    }

    public function autoStartTesTulis(PmbTestSession $session, ?int $adminId = null): int
    {
        return DB::transaction(fn (): int => $this->autoStartTesTulisInTransaction($session, $adminId));
    }

    public function markQueueTesTulisRequirement(PmbOfflineQueue $queue, bool $requiresTesTulis): void
    {
        $notes = (string) $queue->notes;
        $notes = trim(str_replace(self::NOTES_FLAG_SKIP_TES_TULIS, '', $notes));

        if (!$requiresTesTulis) {
            $notes = trim(self::NOTES_FLAG_SKIP_TES_TULIS . ($notes !== '' ? PHP_EOL . $notes : ''));
        }

        $queue->forceFill(['notes' => $notes !== '' ? $notes : null])->save();
    }

    public function autoDispatchForOfficer(PmbTestSession $session, string $stageKey, int $adminId, ?PmbTestRoom $room = null): ?PmbQueueStageStep
    {
        if ($stageKey === PmbQueueStage::KEY_TES_TULIS) {
            $this->autoStartTesTulis($session, $adminId);

            return null;
        }

        return DB::transaction(function () use ($session, $stageKey, $adminId, $room): ?PmbQueueStageStep {
            $stage = PmbQueueStage::where('session_id', $session->id)
                ->where('key', $stageKey)
                ->where('is_active', true)
                ->first();

            if (!$stage || $this->officerHasActiveStep($stage, $adminId)) {
                return null;
            }

            if ($stage->key === PmbQueueStage::KEY_TES_KESEHATAN) {
                $healthRooms = $this->healthServiceRooms($stage, true);
                $usesAssignedLokets = $healthRooms->contains(
                    fn (PmbTestRoom $healthRoom) => $healthRoom->code !== self::HEALTH_ROOM_CODE
                );

                if ($usesAssignedLokets) {
                    $room = $healthRooms->firstWhere('assigned_admin_id', $adminId);

                    if (!$room) {
                        throw new DomainException('Akun petugas belum dipasangkan dengan loket Tes Kesehatan pada sesi ini.');
                    }
                }
            }

            if ($stage->key === PmbQueueStage::KEY_WAWANCARA_KAPRODI) {
                $officer = Admin::with('jurusan')->whereKey($adminId)->where('is_active', true)->first();

                if (!$officer?->jurusan_id) {
                    throw new DomainException('Program studi akun dosen belum ditentukan. Hubungi admin PMB.');
                }

                $assignedRooms = PmbTestRoom::where('session_id', $session->id)
                    ->where('stage_id', $stage->id)
                    ->whereNotNull('assigned_admin_id')
                    ->lockForUpdate()
                    ->get();

                $room = $assignedRooms->firstWhere('assigned_admin_id', $adminId)
                    ?: $this->legacyInterviewRoomForOfficer($stage, $officer);

                if (!$room) {
                    throw new DomainException('Meja Wawancara untuk program studi akun dosen belum tersedia.');
                }
            }

            return $this->callNext($session, $stage, $room, null, $adminId);
        });
    }

    public function callNext(PmbTestSession $session, ?PmbQueueStage $stage = null, ?PmbTestRoom $room = null, ?int $queueId = null, ?int $adminId = null): ?PmbQueueStageStep
    {
        return DB::transaction(function () use ($session, $stage, $room, $queueId, $adminId): ?PmbQueueStageStep {
            $stages = $this->ensureDefaultStages($session)->where('is_active', true);
            $stage ??= $room?->stage ?: $stages->first();

            if (!$stage) {
                return null;
            }

            $isHealthStage = $stage->key === PmbQueueStage::KEY_TES_KESEHATAN;
            $isInterviewStage = $stage->key === PmbQueueStage::KEY_WAWANCARA_KAPRODI;

            if ($room) {
                $this->assertRoomCanReceive($room);
            }

            $programKeyword = $room && $this->stageUsesProgramRooms($stage) ? $this->programKeywordFromRoomCode($room->code) : null;
            $assignedProgramIds = [];
            if ($isInterviewStage && $adminId) {
                $assignedOfficer = Admin::with('jurusan')->find($adminId);
                $assignedProgramIds = $assignedOfficer?->jurusan?->interviewPoolIds() ?? [];
            } elseif ($isInterviewStage && $room?->assigned_admin_id) {
                $room->loadMissing('assignedOfficer.jurusan');
                $assignedProgramIds = $room->assignedOfficer?->jurusan?->interviewPoolIds() ?? [];
            }

            $query = PmbQueueStageStep::with(['queue.user.jurusan', 'stage', 'room'])
                ->where('stage_id', $stage->id)
                ->whereIn('status', [PmbQueueStageStep::STATUS_WAITING, PmbQueueStageStep::STATUS_SKIPPED])
                ->when($room && !$programKeyword && !$assignedProgramIds && !$isHealthStage, fn ($q) => $q->where('room_id', $room->id))
                ->when($programKeyword, fn ($q) => $q->whereHas('queue.user.jurusan', fn ($jurusanQuery) => $jurusanQuery->where('nama_jurusan', 'like', '%' . $programKeyword . '%')))
                ->when($assignedProgramIds, fn ($q) => $q->whereHas('queue.user', fn ($userQuery) => $userQuery->whereIn('jurusan_id', $assignedProgramIds)))
                ->whereHas('queue', fn ($q) => $q
                    ->where('session_id', $session->id)
                    ->whereNotNull('checked_in_at')
                    ->whereNotIn('status', [
                        PmbOfflineQueue::STATUS_REGISTERED,
                        PmbOfflineQueue::STATUS_COMPLETED,
                        PmbOfflineQueue::STATUS_CANCELLED,
                        PmbOfflineQueue::STATUS_NO_SHOW,
                    ]));

            if ($queueId) {
                $query->where('queue_id', $queueId);
            }

            $query->orderByRaw('CASE WHEN status = ? THEN 1 ELSE 0 END', [PmbQueueStageStep::STATUS_SKIPPED]);

            $this->orderStepsForCall($query, $stage);

            $targetRoomId = $room?->id;

            if ($isInterviewStage && !$room) {
                $candidates = $query->lockForUpdate()->get();
                $step = null;

                foreach ($candidates as $candidate) {
                    $candidateRoom = $this->receivableRoomForStep($candidate, $stage);

                    if ($candidateRoom) {
                        $step = $candidate;
                        $targetRoomId = $candidateRoom->id;
                        break;
                    }
                }

                if (!$step && $candidates->isNotEmpty()) {
                    throw new DomainException('Semua loket Wawancara sedang penuh/tidak tersedia.');
                }
            } else {
                $step = $query
                    ->lockForUpdate()
                    ->first();
            }

            if (!$step) {
                return null;
            }

            if ($isHealthStage && !$room) {
                $room = $this->receivableHealthRoom($stage);

                if (!$room) {
                    throw new DomainException('Semua loket Tes Kesehatan sedang terisi atau tidak aktif.');
                }

                $targetRoomId = $room->id;
            }

            $this->assertStepActionAllowed($step, 'call');

            $targetRoomId ??= $step->room_id ?: $stage->default_room_id;

            $this->transitionStep($step, PmbQueueStageStep::STATUS_PROCESSING, $adminId, 'call', [
                'room_id' => $targetRoomId,
                'assigned_officer_id' => $adminId,
                'called_at' => now(),
                'started_at' => now(),
                'call_count' => $step->call_count + 1,
            ]);

            $legacyFrom = $step->queue->status;
            $step->queue->update([
                'status' => PmbOfflineQueue::STATUS_IN_ROOM,
                'room_id' => $step->room_id,
                'current_stage_id' => $stage->id,
                'called_at' => $step->queue->called_at ?: now(),
                'last_called_at' => now(),
                'entered_at' => $step->queue->entered_at ?: now(),
                'call_count' => $step->queue->call_count + 1,
            ]);

            $this->writeLegacyStatusLog($step->queue, $legacyFrom, PmbOfflineQueue::STATUS_IN_ROOM, $adminId, 'Peserta dipanggil dan mulai diproses.');
            $this->writeCallLog($step, $legacyFrom, PmbOfflineQueue::STATUS_IN_ROOM, $adminId, 'call');

            event(new PmbQueueUpdated($step->queue->fresh(['user.jurusan', 'room', 'session', 'currentStage']), 'called'));
            $this->writeActivityLog($step->queue, 'call', 'Peserta dipanggil ke tahap antrian.', [
                'stage_id' => $step->stage_id,
                'room_id' => $step->room_id,
            ]);

            return $step->fresh(['queue.user.jurusan', 'stage', 'room']);
        });
    }

    public function updateStep(PmbQueueStageStep $step, string $action, ?int $roomId = null, ?int $adminId = null, ?string $note = null): PmbQueueStageStep
    {
        return DB::transaction(function () use ($step, $action, $roomId, $adminId, $note): PmbQueueStageStep {
            $locked = PmbQueueStageStep::with(['queue.session', 'stage'])
                ->whereKey($step->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertStepActionAllowed($locked, $action);

            match ($action) {
                'in_room', 'start' => $this->markProcessing($locked, $roomId, $adminId, $note),
                'complete' => $this->completeStep($locked, $adminId, $note),
                'complete_test_tulis' => $this->completeStep($locked, $adminId, $note ?: 'Tes Tulis di-ACC manual oleh panitia.'),
                'hold_health_form' => $this->holdHealthForm($locked, $adminId, $note),
                'release_health_form' => $this->releaseHealthForm($locked, $adminId, $note),
                'complete_health' => $this->completeStep($locked, $adminId, $note ?: 'Tes Kesehatan selesai. Peserta masuk antrean Wawancara sesuai urutan selesai kesehatan.'),
                'hold' => $this->holdStep($locked, $adminId, $note),
                'release' => $this->activateStep($locked, $adminId, 'release', $note),
                'skip' => $this->skipStep($locked, $adminId, $note),
                'recall' => $this->recallStep($locked, $roomId, $adminId, $note),
                'problem' => $this->problemStep($locked, $adminId, $note),
                default => null,
            };

            return $locked->fresh(['queue.user.jurusan', 'stage', 'room']);
        });
    }

    public function cancelQueue(PmbOfflineQueue $queue, ?int $adminId = null, ?string $note = null): PmbOfflineQueue
    {
        return DB::transaction(function () use ($queue, $adminId, $note): PmbOfflineQueue {
            $locked = PmbOfflineQueue::whereKey($queue->id)->lockForUpdate()->firstOrFail();
            $from = $locked->status;
            $this->assertQueueCanBeTerminalChanged($locked, 'cancel');

            $locked->update([
                'status' => PmbOfflineQueue::STATUS_CANCELLED,
                'arrival_status' => PmbOfflineQueue::ARRIVAL_CANCELLED,
                'overall_status' => PmbOfflineQueue::OVERALL_CANCELLED,
                'cancelled_at' => now(),
            ]);

            $locked->stageSteps()
                ->whereNotIn('status', [PmbQueueStageStep::STATUS_COMPLETED, PmbQueueStageStep::STATUS_SKIPPED])
                ->update(['status' => PmbQueueStageStep::STATUS_SKIPPED, 'skipped_at' => now()]);

            $this->writeLegacyStatusLog($locked, $from, PmbOfflineQueue::STATUS_CANCELLED, $adminId, $note ?: 'Peserta dibatalkan.');
            event(new PmbQueueUpdated($locked->fresh(['user.jurusan', 'room', 'session', 'currentStage']), 'cancelled'));
            $this->writeActivityLog($locked, 'cancel', $note ?: 'Peserta dibatalkan dari antrian offline PMB.');

            return $locked;
        });
    }

    public function markNoShow(PmbOfflineQueue $queue, ?int $adminId = null, ?string $note = null): PmbOfflineQueue
    {
        return DB::transaction(function () use ($queue, $adminId, $note): PmbOfflineQueue {
            $locked = PmbOfflineQueue::whereKey($queue->id)->lockForUpdate()->firstOrFail();
            $from = $locked->status;
            $this->assertQueueCanBeTerminalChanged($locked, 'no_show');

            $locked->update([
                'status' => PmbOfflineQueue::STATUS_NO_SHOW,
                'arrival_status' => PmbOfflineQueue::ARRIVAL_NO_SHOW,
                'overall_status' => PmbOfflineQueue::OVERALL_NO_SHOW,
                'no_show_at' => now(),
            ]);

            $this->writeLegacyStatusLog($locked, $from, PmbOfflineQueue::STATUS_NO_SHOW, $adminId, $note ?: 'Peserta tidak hadir.');
            event(new PmbQueueUpdated($locked->fresh(['user.jurusan', 'room', 'session', 'currentStage']), 'no_show'));
            $this->writeActivityLog($locked, 'no_show', $note ?: 'Peserta ditandai tidak hadir.');

            return $locked;
        });
    }

    public function reactivateQueue(PmbOfflineQueue $queue, ?int $adminId = null, ?string $note = null): PmbOfflineQueue
    {
        return DB::transaction(function () use ($queue, $adminId, $note): PmbOfflineQueue {
            $locked = PmbOfflineQueue::with(['session', 'stageSteps.stage'])->whereKey($queue->id)->lockForUpdate()->firstOrFail();
            $from = $locked->status;
            if (!in_array($locked->status, [PmbOfflineQueue::STATUS_CANCELLED, PmbOfflineQueue::STATUS_NO_SHOW], true)) {
                throw new DomainException('Peserta hanya dapat diaktifkan kembali dari status dibatalkan atau tidak hadir.');
            }

            $stages = $this->ensureDefaultStages($locked->session)->where('is_active', true);
            $steps = $locked->stageSteps->sortBy(fn ($step) => $step->stage->sort_order ?? 999);
            
            $activeStep = $steps->first(fn ($step) => $step->status !== PmbQueueStageStep::STATUS_COMPLETED);
            
            if (!$activeStep) {
                $firstStage = $stages->first();
                if ($firstStage) {
                    $activeStep = PmbQueueStageStep::firstOrCreate(
                        ['queue_id' => $locked->id, 'stage_id' => $firstStage->id],
                        ['status' => PmbQueueStageStep::STATUS_PENDING]
                    );
                }
            }

            if ($activeStep) {
                $activeStep->update([
                    'status' => PmbQueueStageStep::STATUS_WAITING,
                    'skipped_at' => null,
                    'notes' => $note ?: 'Peserta diaktifkan kembali.',
                ]);
                
                $locked->update([
                    'status' => PmbOfflineQueue::STATUS_WAITING,
                    'arrival_status' => PmbOfflineQueue::ARRIVAL_CHECKED_IN,
                    'overall_status' => PmbOfflineQueue::OVERALL_IN_PROCESS,
                    'current_stage_id' => $activeStep->stage_id,
                    'room_id' => $activeStep->room_id ?: $activeStep->stage->default_room_id,
                    'cancelled_at' => null,
                    'no_show_at' => null,
                ]);
            } else {
                $locked->update([
                    'status' => PmbOfflineQueue::STATUS_WAITING,
                    'arrival_status' => PmbOfflineQueue::ARRIVAL_CHECKED_IN,
                    'overall_status' => PmbOfflineQueue::OVERALL_IN_PROCESS,
                    'cancelled_at' => null,
                    'no_show_at' => null,
                ]);
            }

            $this->writeLegacyStatusLog($locked, $from, PmbOfflineQueue::STATUS_WAITING, $adminId, $note ?: 'Peserta diaktifkan kembali dari status ' . $from . '.');
            event(new PmbQueueUpdated($locked->fresh(['user.jurusan', 'room', 'session', 'currentStage']), 'reactivated'));
            $this->writeActivityLog($locked, 'reactivate', $note ?: 'Peserta diaktifkan kembali dari status ' . $from . '.');

            return $locked->fresh(['user.jurusan', 'room', 'session', 'currentStage', 'stageSteps.stage']);
        });
    }

    public function boardPayload(PmbTestSession $session, bool $includePrivate = true): array
    {
        $stages = $this->ensureDefaultStages($session)
            ->where('is_active', true)
            ->load(['rooms.assignedOfficer.jurusan'])
            ->keyBy('key');
        $lanes = collect();

        $healthStage = $stages->get(PmbQueueStage::KEY_TES_KESEHATAN);
        $interviewStage = $stages->get(PmbQueueStage::KEY_WAWANCARA_KAPRODI);
        if ($interviewStage) {
            $interviewCodes = collect($this->interviewRoomDefinitions())->pluck('code');
            $interviewRooms = $interviewStage->rooms
                ->whereIn('code', $interviewCodes)
                ->sortBy('sort_order')
                ->values();
            if ($interviewRooms->isEmpty()) {
                $lanes->push($this->stageBoardPayload($interviewStage, includePrivate: $includePrivate));
            } else {
                foreach ($interviewRooms as $room) {
                    $lanes->push($this->stageBoardPayload($interviewStage, $room, $includePrivate));
                }
            }
        }

        return [
            'session' => [
                'uuid' => $session->uuid,
                'code' => $session->code,
                'name' => $session->name,
                'status' => $session->status,
                'message' => $session->status === PmbTestSession::STATUS_DRAFT ? 'Sesi belum dimulai' : null,
            ],
            'health' => $healthStage ? $this->healthBoardPayload($healthStage, $includePrivate) : null,
            'interviews' => $interviewStage ? $this->interviewBoardPayload($interviewStage, $includePrivate) : [],
            'stages' => $lanes->values(),
        ];
    }

    public function stageBoardPayload(PmbQueueStage $stage, ?PmbTestRoom $room = null, bool $includePrivate = true): array
    {
        $programKeyword = $room && $this->stageUsesProgramRooms($stage) ? $this->programKeywordFromRoomCode($room->code) : null;
        $current = PmbQueueStageStep::with(['queue.user.jurusan', 'room'])
            ->where('stage_id', $stage->id)
            ->when($room && !$programKeyword, fn ($query) => $query->where('room_id', $room->id))
            ->when($programKeyword, fn ($query) => $query->whereHas('queue.user.jurusan', fn ($jurusanQuery) => $jurusanQuery->where('nama_jurusan', 'like', '%' . $programKeyword . '%')))
            ->whereHas('queue.user', fn ($query) => $query->isPmbVerified())
            ->whereHas('queue', fn ($query) => $query->where('current_stage_id', $stage->id))
            ->whereIn('status', [PmbQueueStageStep::STATUS_CALLED, PmbQueueStageStep::STATUS_PROCESSING])
            ->latest('called_at')
            ->limit(3)
            ->get();

        $waiting = PmbQueueStageStep::with(['queue.user.jurusan', 'room'])
            ->where('stage_id', $stage->id)
            ->when($room && !$programKeyword, fn ($query) => $query->where('room_id', $room->id))
            ->when($programKeyword, fn ($query) => $query->whereHas('queue.user.jurusan', fn ($jurusanQuery) => $jurusanQuery->where('nama_jurusan', 'like', '%' . $programKeyword . '%')))
            ->whereHas('queue.user', fn ($query) => $query->isPmbVerified())
            ->whereHas('queue', fn ($query) => $query->where('current_stage_id', $stage->id))
            ->where('status', PmbQueueStageStep::STATUS_WAITING)
            ->orderBy('queued_at')
            ->limit(8)
            ->get();

        $counts = PmbQueueStageStep::where('stage_id', $stage->id)
            ->when($room && !$programKeyword, fn ($query) => $query->where('room_id', $room->id))
            ->when($programKeyword, fn ($query) => $query->whereHas('queue.user.jurusan', fn ($jurusanQuery) => $jurusanQuery->where('nama_jurusan', 'like', '%' . $programKeyword . '%')))
            ->whereHas('queue.user', fn ($query) => $query->isPmbVerified())
            ->whereHas('queue', fn ($query) => $query->where('selection_mode', PmbOfflineQueue::MODE_OFFLINE))
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'id' => $stage->id,
            'key' => $stage->key,
            'name' => $this->boardLaneName($stage, $room),
            'is_required' => $stage->is_required,
            'is_active' => $stage->is_active,
            'current_calls' => $current
                ->map(fn (PmbQueueStageStep $step) => $this->stepPayload($step, $includePrivate, !$includePrivate))
                ->values(),
            'waiting' => $waiting->map(fn (PmbQueueStageStep $step) => $this->stepPayload($step, $includePrivate))->values(),
            'rooms' => ($room ? collect([$room]) : $stage->rooms)->map(fn (PmbTestRoom $room) => $this->roomPayload($room))->values(),
            'counts' => [
                'waiting' => $counts->get(PmbQueueStageStep::STATUS_WAITING, 0),
                'called' => $counts->get(PmbQueueStageStep::STATUS_CALLED, 0),
                'processing' => $counts->get(PmbQueueStageStep::STATUS_PROCESSING, 0),
                'completed' => $counts->get(PmbQueueStageStep::STATUS_COMPLETED, 0),
                'held' => $counts->get(PmbQueueStageStep::STATUS_HELD, 0),
                'problem' => $counts->get(PmbQueueStageStep::STATUS_PROBLEM, 0),
            ],
        ];
    }

    public function stepPayload(PmbQueueStageStep $step, bool $includePrivate = true, bool $includePublicCallName = false): array
    {
        $payload = [
            'id' => $step->id,
            'queue_id' => $step->queue_id,
            'queue_uuid' => $step->queue?->uuid,
            'queue_code' => $step->queue?->queue_code,
            'status' => $step->status,
            'status_label' => $step->statusLabel(),
            'participant' => [
                'name' => $step->queue?->user?->name,
                'program_studi' => $step->queue?->user?->jurusan?->nama_jurusan,
            ],
            'room' => $step->room?->displayName(),
            'called_at' => optional($step->called_at)->toIso8601String(),
            'queued_at' => optional($step->queued_at)->toIso8601String(),
        ];

        if (!$includePrivate) {
            unset($payload['participant']);
        }

        if ($includePublicCallName) {
            $payload = array_merge($payload, $this->publicCallName((string) $step->queue?->user?->name));
        }

        return $payload;
    }

    private function publicCallName(string $fullName): array
    {
        $parts = preg_split('/\s+/u', trim($fullName), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $spokenName = implode(' ', array_slice($parts, 0, 2));
        $lastName = count($parts) > 2 ? $parts[array_key_last($parts)] : '';
        $lastInitial = $lastName !== '' ? mb_strtoupper(mb_substr($lastName, 0, 1)) . '.' : '';

        return [
            'display_name' => trim($spokenName . ' ' . $lastInitial),
            'spoken_name' => $spokenName,
        ];
    }

    private function activateStep(PmbQueueStageStep $step, ?int $adminId, string $action = 'activate', ?string $note = null): void
    {
        if ($step->status === PmbQueueStageStep::STATUS_WAITING) {
            return;
        }

        $number = $step->stage_queue_number ?: $this->nextStageNumber($step->stage);

        $roomId = $step->room_id ?: $step->stage->default_room_id;

        if ($step->stage->key === PmbQueueStage::KEY_TES_KESEHATAN) {
            $roomId = $this->healthRoomForStage($step->stage)?->id;
        }

        if ($step->stage->key === PmbQueueStage::KEY_WAWANCARA_KAPRODI) {
            $roomId = $this->interviewRoomForQueue($step->queue) ?: $roomId;
        }

        $this->transitionStep($step, PmbQueueStageStep::STATUS_WAITING, $adminId, $action, [
            'stage_queue_number' => $number,
            'stage_queue_code' => $this->stageCode($step->stage, $number),
            'queued_at' => now(),
            'room_id' => $roomId,
            'notes' => $note ?: $step->notes,
        ]);

        $step->queue->update([
            'status' => PmbOfflineQueue::STATUS_WAITING,
            'overall_status' => PmbOfflineQueue::OVERALL_IN_PROCESS,
            'current_stage_id' => $step->stage_id,
            'room_id' => $step->room_id,
        ]);

        if ($step->stage->key === PmbQueueStage::KEY_TES_TULIS) {
            $this->autoStartTesTulisInTransaction($step->queue->session, $adminId);
        }
    }

    private function autoStartTesTulisInTransaction(PmbTestSession $session, ?int $adminId = null): int
    {
        $stage = PmbQueueStage::where('session_id', $session->id)
            ->where('key', PmbQueueStage::KEY_TES_TULIS)
            ->where('is_active', true)
            ->first();

        if (!$stage) {
            return 0;
        }

        $steps = PmbQueueStageStep::with(['queue', 'stage'])
            ->where('stage_id', $stage->id)
            ->where('status', PmbQueueStageStep::STATUS_WAITING)
            ->whereHas('queue', fn ($query) => $query
                ->where('session_id', $session->id)
                ->whereNotNull('checked_in_at')
                ->whereNotIn('status', [
                    PmbOfflineQueue::STATUS_COMPLETED,
                    PmbOfflineQueue::STATUS_CANCELLED,
                    PmbOfflineQueue::STATUS_NO_SHOW,
                ]))
            ->orderBy(PmbOfflineQueue::select('queue_number')
                ->whereColumn('pmb_offline_queues.id', 'pmb_queue_stage_steps.queue_id')
                ->limit(1))
            ->lockForUpdate()
            ->get();

        foreach ($steps as $step) {
            $this->transitionStep($step, PmbQueueStageStep::STATUS_PROCESSING, $adminId, 'auto_start_tes_tulis', [
                'assigned_officer_id' => $adminId,
                'called_at' => $step->called_at ?: now(),
                'started_at' => $step->started_at ?: now(),
                'notes' => 'Tes Tulis dimulai otomatis untuk sesi offline.',
            ]);

            $from = $step->queue->status;
            $step->queue->update([
                'status' => PmbOfflineQueue::STATUS_IN_ROOM,
                'overall_status' => PmbOfflineQueue::OVERALL_IN_PROCESS,
                'current_stage_id' => $stage->id,
                'last_called_at' => now(),
                'entered_at' => $step->queue->entered_at ?: now(),
                'call_count' => $step->queue->call_count + 1,
            ]);

            $this->writeLegacyStatusLog($step->queue, $from, PmbOfflineQueue::STATUS_IN_ROOM, $adminId, 'Tes Tulis dimulai otomatis.');
            event(new PmbQueueUpdated($step->queue->fresh(['user.jurusan', 'room', 'session', 'currentStage']), 'auto_started'));
        }

        return $steps->count();
    }

    private function officerHasActiveStep(PmbQueueStage $stage, int $adminId): bool
    {
        return PmbQueueStageStep::where('stage_id', $stage->id)
            ->where('assigned_officer_id', $adminId)
            ->whereIn('status', [PmbQueueStageStep::STATUS_CALLED, PmbQueueStageStep::STATUS_PROCESSING])
            ->exists();
    }

    private function markProcessing(PmbQueueStageStep $step, ?int $roomId, ?int $adminId, ?string $note): void
    {
        if (!$roomId && $step->stage->key === PmbQueueStage::KEY_TES_KESEHATAN) {
            $roomId = $this->healthRoomForStage($step->stage)?->id;
        }

        if (!$roomId && $step->stage->key === PmbQueueStage::KEY_WAWANCARA_KAPRODI) {
            $room = $this->receivableRoomForStep($step, $step->stage);

            if (!$room) {
                throw new DomainException('Loket Wawancara peserta sedang penuh/tidak tersedia.');
            }

            $roomId = $room->id;
        }

        if ($roomId && $step->stage->key !== PmbQueueStage::KEY_TES_KESEHATAN) {
            $room = PmbTestRoom::whereKey($roomId)->lockForUpdate()->first();
            if ($room) {
                $this->assertRoomCanReceive($room);
            }
        }

        $this->transitionStep($step, PmbQueueStageStep::STATUS_PROCESSING, $adminId, 'start', [
            'room_id' => $roomId ?: $step->room_id ?: $step->stage->default_room_id,
            'assigned_officer_id' => $adminId,
            'started_at' => now(),
            'notes' => $note ?: $step->notes,
        ]);

        $from = $step->queue->status;
        $step->queue->update([
            'status' => PmbOfflineQueue::STATUS_IN_ROOM,
            'room_id' => $step->room_id,
            'entered_at' => now(),
            'current_stage_id' => $step->stage_id,
        ]);
        $this->writeLegacyStatusLog($step->queue, $from, PmbOfflineQueue::STATUS_IN_ROOM, $adminId, $note ?: 'Peserta masuk ruangan/tahap.');
        event(new PmbQueueUpdated($step->queue->fresh(['user.jurusan', 'room', 'session', 'currentStage']), 'processing'));
        $this->writeActivityLog($step->queue, 'start', $note ?: 'Peserta mulai diproses pada tahap antrian.', [
            'stage_id' => $step->stage_id,
            'room_id' => $step->room_id,
        ]);
    }

    private function completeStep(PmbQueueStageStep $step, ?int $adminId, ?string $note): void
    {
        $completedStageKey = $step->stage->key;
        $completedSession = $step->queue->session;

        $this->transitionStep($step, PmbQueueStageStep::STATUS_COMPLETED, $adminId, 'complete', [
            'completed_at' => now(),
            'assigned_officer_id' => $step->assigned_officer_id ?: $adminId,
            'notes' => $note ?: $step->notes,
        ]);

        $next = $this->nextStage($step);
        if ($next) {
            $nextStep = PmbQueueStageStep::firstOrCreate(
                ['queue_id' => $step->queue_id, 'stage_id' => $next->id],
                ['status' => PmbQueueStageStep::STATUS_PENDING]
            );
            $this->activateStep($nextStep->load(['queue', 'stage']), $adminId, 'advance');
            event(new PmbQueueUpdated($step->queue->fresh(['user.jurusan', 'room', 'session', 'currentStage']), 'advance'));

            if ($completedStageKey === PmbQueueStage::KEY_TES_KESEHATAN && $adminId) {
                $this->autoDispatchForOfficer($completedSession, PmbQueueStage::KEY_TES_KESEHATAN, $adminId);
            }

            return;
        }

        $from = $step->queue->status;
        $step->queue->update([
            'status' => PmbOfflineQueue::STATUS_COMPLETED,
            'overall_status' => PmbOfflineQueue::OVERALL_COMPLETED,
            'completed_at' => now(),
            'current_stage_id' => null,
        ]);

        $this->writeLegacyStatusLog($step->queue, $from, PmbOfflineQueue::STATUS_COMPLETED, $adminId, $note ?: 'Semua tahap wajib selesai.');
        event(new PmbQueueUpdated($step->queue->fresh(['user.jurusan', 'room', 'session']), 'completed'));
        $this->writeActivityLog($step->queue, 'complete', $note ?: 'Tahap akhir antrian offline PMB selesai.');

        if ($completedStageKey === PmbQueueStage::KEY_WAWANCARA_KAPRODI && $adminId) {
            $this->autoDispatchForOfficer($completedSession, PmbQueueStage::KEY_WAWANCARA_KAPRODI, $adminId);
        }
    }

    private function holdStep(PmbQueueStageStep $step, ?int $adminId, ?string $note): void
    {
        $this->transitionStep($step, PmbQueueStageStep::STATUS_HELD, $adminId, 'hold', [
            'held_at' => now(),
            'notes' => $note ?: $step->notes,
        ]);

        $step->queue->update([
            'overall_status' => PmbOfflineQueue::OVERALL_IN_PROCESS,
            'current_stage_id' => $step->stage_id,
        ]);
        event(new PmbQueueUpdated($step->queue->fresh(['user.jurusan', 'room', 'session', 'currentStage']), 'held'));
    }

    private function holdHealthForm(PmbQueueStageStep $step, ?int $adminId, ?string $note): void
    {
        if ($step->stage->key === PmbQueueStage::KEY_TES_TULIS) {
            $this->transitionStep($step, PmbQueueStageStep::STATUS_COMPLETED, $adminId, 'complete_test_tulis', [
                'completed_at' => now(),
                'notes' => $note ?: 'Tes Tulis selesai, peserta menunggu berkas kesehatan manual.',
            ]);

            $healthStep = $this->stepForStageKey($step->queue, PmbQueueStage::KEY_TES_KESEHATAN);

            if ($healthStep) {
                $this->activateStep($healthStep->load(['queue.user.jurusan', 'stage']), $adminId, 'advance_health_form');
                $this->holdStep($healthStep, $adminId, $note ?: 'Menunggu berkas kesehatan manual.');
            }

            return;
        }

        $this->holdStep($step, $adminId, $note ?: 'Menunggu berkas kesehatan manual.');
    }

    private function releaseHealthForm(PmbQueueStageStep $step, ?int $adminId, ?string $note): void
    {
        $healthStep = $step->stage->key === PmbQueueStage::KEY_TES_KESEHATAN
            ? $step
            : $this->stepForStageKey($step->queue, PmbQueueStage::KEY_TES_KESEHATAN);

        if ($healthStep) {
            $this->activateStep($healthStep->load(['queue.user.jurusan', 'stage']), $adminId, 'release_health_form', $note ?: 'Berkas kesehatan manual diterima.');
        }
    }

    private function skipStep(PmbQueueStageStep $step, ?int $adminId, ?string $note): void
    {
        $this->transitionStep($step, PmbQueueStageStep::STATUS_SKIPPED, $adminId, 'skip', [
            'skipped_at' => now(),
            'queued_at' => $step->queued_at ?: now(),
            'notes' => $note ?: $step->notes,
        ]);

        $from = $step->queue->status;
        $step->queue->update([
            'status' => PmbOfflineQueue::STATUS_SKIPPED,
            'current_stage_id' => $step->stage_id,
        ]);
        $this->writeLegacyStatusLog($step->queue, $from, PmbOfflineQueue::STATUS_SKIPPED, $adminId, $note ?: 'Peserta dilewati sementara.');
        event(new PmbQueueUpdated($step->queue->fresh(['user.jurusan', 'room', 'session', 'currentStage']), 'skipped'));
        $this->writeActivityLog($step->queue, 'skip', $note ?: 'Peserta dilewati sementara.', [
            'stage_id' => $step->stage_id,
        ]);
    }

    private function recallStep(PmbQueueStageStep $step, ?int $roomId, ?int $adminId, ?string $note): void
    {
        $this->transitionStep($step, PmbQueueStageStep::STATUS_PROCESSING, $adminId, 'recall', [
            'room_id' => $roomId ?: $step->room_id ?: $step->stage->default_room_id,
            'called_at' => now(),
            'call_count' => $step->call_count + 1,
            'notes' => $note ?: $step->notes,
        ]);

        $from = $step->queue->status;
        $step->queue->update([
            'status' => PmbOfflineQueue::STATUS_IN_ROOM,
            'room_id' => $step->room_id,
            'last_called_at' => now(),
            'call_count' => $step->queue->call_count + 1,
            'current_stage_id' => $step->stage_id,
        ]);
        $this->writeCallLog($step, $from, PmbOfflineQueue::STATUS_IN_ROOM, $adminId, 'recall');
        event(new PmbQueueUpdated($step->queue->fresh(['user.jurusan', 'room', 'session', 'currentStage']), 'recall'));
        $this->writeActivityLog($step->queue, 'recall', $note ?: 'Peserta dipanggil ulang.', [
            'stage_id' => $step->stage_id,
            'room_id' => $step->room_id,
        ]);
    }

    private function problemStep(PmbQueueStageStep $step, ?int $adminId, ?string $note): void
    {
        $this->transitionStep($step, PmbQueueStageStep::STATUS_PROBLEM, $adminId, 'problem', [
            'notes' => $note ?: $step->notes,
        ]);
        event(new PmbQueueUpdated($step->queue->fresh(['user.jurusan', 'room', 'session', 'currentStage']), 'problem'));
    }

    private function transitionStep(PmbQueueStageStep $step, string $toStatus, ?int $adminId, string $action, array $extra = []): void
    {
        $from = $step->status;
        $step->update(array_merge(['status' => $toStatus], $extra));

        PmbQueueStageLog::create([
            'step_id' => $step->id,
            'queue_id' => $step->queue_id,
            'stage_id' => $step->stage_id,
            'admin_id' => $adminId,
            'action' => $action,
            'from_status' => $from,
            'to_status' => $toStatus,
            'metadata' => ['notes' => $extra['notes'] ?? null],
        ]);
    }

    private function nextStageNumber(PmbQueueStage $stage): int
    {
        PmbQueueStage::whereKey($stage->id)->lockForUpdate()->first();

        PmbQueueCounter::firstOrCreate(
            ['session_id' => $stage->session_id, 'stage_id' => $stage->id],
            ['last_number' => 0]
        );

        $counter = PmbQueueCounter::where('session_id', $stage->session_id)
            ->where('stage_id', $stage->id)
            ->lockForUpdate()
            ->firstOrFail();

        $counter->increment('last_number');
        $counter->refresh();

        return $counter->last_number;
    }

    private function stageCode(PmbQueueStage $stage, int $number): string
    {
        $prefix = match ($stage->key) {
            PmbQueueStage::KEY_PEMBERKASAN => 'B',
            PmbQueueStage::KEY_TES_TULIS => 'T',
            PmbQueueStage::KEY_TES_KESEHATAN => 'K',
            PmbQueueStage::KEY_WAWANCARA_KAPRODI => 'W',
            default => 'Q',
        };

        return $prefix . str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }

    private function queueCodeFor(PmbOfflineQueue $queue, int $number): string
    {
        return $this->programPrefixForQueue($queue) . str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }

    private function programPrefixForQueue(PmbOfflineQueue $queue): string
    {
        $queue->loadMissing('user.jurusan');
        $program = strtoupper((string) ($queue->user?->jurusan?->nama_jurusan ?? ''));

        return match (true) {
            str_contains($program, 'GIZI') => 'GZ',
            str_contains($program, 'KEBIDANAN') => 'KB',
            str_contains($program, 'FARMASI') => 'FAR',
            default => 'Q',
        };
    }

    private function boardLaneName(PmbQueueStage $stage, ?PmbTestRoom $room): string
    {
        if ($stage->key === PmbQueueStage::KEY_TES_KESEHATAN && $room) {
            return $room->displayName();
        }

        return $room?->name ?: $stage->name;
    }

    private function nextStage(PmbQueueStageStep $step): ?PmbQueueStage
    {
        return PmbQueueStage::where('session_id', $step->stage->session_id)
            ->where('is_active', true)
            ->where('sort_order', '>', $step->stage->sort_order)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();
    }

    private function stepForStageKey(PmbOfflineQueue $queue, string $stageKey): ?PmbQueueStageStep
    {
        $stage = PmbQueueStage::where('session_id', $queue->session_id)
            ->where('key', $stageKey)
            ->where('is_active', true)
            ->first();

        if (!$stage) {
            return null;
        }

        return PmbQueueStageStep::firstOrCreate(
            ['queue_id' => $queue->id, 'stage_id' => $stage->id],
            ['status' => PmbQueueStageStep::STATUS_PENDING]
        );
    }

    private function queueSkipsTesTulis(PmbOfflineQueue $queue): bool
    {
        return str_contains((string) $queue->notes, self::NOTES_FLAG_SKIP_TES_TULIS);
    }

    private function ensureInterviewRooms(PmbTestSession $session): void
    {
        $stage = PmbQueueStage::where('session_id', $session->id)
            ->where('key', PmbQueueStage::KEY_WAWANCARA_KAPRODI)
            ->first();

        if (!$stage) {
            return;
        }

        if (PmbTestRoom::where('session_id', $session->id)
            ->where('stage_id', $stage->id)
            ->whereNotNull('assigned_admin_id')
            ->exists()) {
            return;
        }

        foreach ($this->interviewRoomDefinitions() as $definition) {
            $room = PmbTestRoom::firstOrCreate(
                ['session_id' => $session->id, 'code' => $definition['code']],
                [
                    'stage_id' => $stage->id,
                    'name' => $definition['name'],
                    'capacity' => 1,
                    'sort_order' => $definition['sort_order'],
                    'status' => PmbTestRoom::STATUS_AVAILABLE,
                ]
            );

            $room->update([
                'stage_id' => $stage->id,
                'name' => $definition['name'],
                'sort_order' => $definition['sort_order'],
            ]);
        }
    }

    private function ensureHealthRoom(PmbTestSession $session): ?PmbTestRoom
    {
        $stage = PmbQueueStage::where('session_id', $session->id)
            ->where('key', PmbQueueStage::KEY_TES_KESEHATAN)
            ->first();

        if (!$stage) {
            return null;
        }

        $assignedRoom = PmbTestRoom::where('session_id', $session->id)
            ->where('stage_id', $stage->id)
            ->whereNotNull('assigned_admin_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        // Loket bersama hanya diperlukan sebelum sesi memiliki loket per akun petugas.
        if ($assignedRoom) {
            if (!$stage->default_room_id) {
                $stage->update(['default_room_id' => $assignedRoom->id]);
            }

            return $assignedRoom;
        }

        $room = PmbTestRoom::firstOrCreate(
            ['session_id' => $session->id, 'code' => self::HEALTH_ROOM_CODE],
            [
                'stage_id' => $stage->id,
                'name' => self::HEALTH_ROOM_NAME,
                'capacity' => 1,
                'sort_order' => 30,
                'status' => PmbTestRoom::STATUS_AVAILABLE,
            ]
        );

        $room->update([
            'stage_id' => $stage->id,
            'name' => self::HEALTH_ROOM_NAME,
            'sort_order' => 30,
        ]);

        if (!$stage->default_room_id) {
            $stage->update(['default_room_id' => $room->id]);
        }

        return $room;
    }

    private function healthRoomForStage(PmbQueueStage $stage): ?PmbTestRoom
    {
        return PmbTestRoom::where('session_id', $stage->session_id)
            ->where('stage_id', $stage->id)
            ->where('code', self::HEALTH_ROOM_CODE)
            ->first()
            ?: $this->ensureHealthRoom($stage->session);
    }

    private function healthRoomForQueue(PmbOfflineQueue $queue): ?int
    {
        $stage = PmbQueueStage::where('session_id', $queue->session_id)
            ->where('key', PmbQueueStage::KEY_TES_KESEHATAN)
            ->first();

        return $stage ? $this->healthRoomForStage($stage)?->id : null;
    }

    private function healthBoardPayload(PmbQueueStage $stage, bool $includePrivate = true): array
    {
        $current = PmbQueueStageStep::with(['queue.user.jurusan', 'room'])
            ->where('stage_id', $stage->id)
            ->whereIn('status', [PmbQueueStageStep::STATUS_CALLED, PmbQueueStageStep::STATUS_PROCESSING])
            ->whereHas('queue', fn ($query) => $query->where('current_stage_id', $stage->id))
            ->whereHas('queue.user', fn ($query) => $query->isPmbVerified())
            ->orderBy('called_at')
            ->get();

        $waiting = PmbQueueStageStep::with(['queue.user.jurusan', 'room'])
            ->where('stage_id', $stage->id)
            ->where('status', PmbQueueStageStep::STATUS_WAITING)
            ->whereHas('queue', fn ($query) => $query->where('current_stage_id', $stage->id))
            ->whereHas('queue.user', fn ($query) => $query->isPmbVerified());

        $waitingCount = (clone $waiting)->count();
        $this->orderStepsByCheckInQueue($waiting);
        $waiting = $waiting->limit(8)->get();

        $rooms = $this->healthServiceRooms($stage);
        $currentByRoom = $current->groupBy('room_id');

        // Tetap tampilkan loket fallback bila masih memegang panggilan dari alur lama.
        $fallbackWithActiveCall = $stage->rooms
            ->first(fn (PmbTestRoom $room) => $room->code === self::HEALTH_ROOM_CODE && $currentByRoom->has($room->id));
        if ($fallbackWithActiveCall && !$rooms->contains('id', $fallbackWithActiveCall->id)) {
            $rooms->push($fallbackWithActiveCall);
        }

        $roomPayloads = $rooms->map(function (PmbTestRoom $room) use ($currentByRoom, $includePrivate): array {
            $payload = $this->roomPayload($room);
            $activeCalls = $currentByRoom->get($room->id, collect());
            $isInactive = in_array($room->status, [PmbTestRoom::STATUS_PAUSED, PmbTestRoom::STATUS_CLOSED], true);

            return array_merge($payload, [
                'occupancy_status' => $isInactive ? 'Tidak Aktif' : ($activeCalls->isNotEmpty() ? 'Terisi' : 'Kosong'),
                'occupancy_tone' => $isInactive ? 'muted' : ($activeCalls->isNotEmpty() ? 'busy' : 'ready'),
                'current_calls' => $activeCalls
                    ->map(fn (PmbQueueStageStep $step) => $this->stepPayload($step, $includePrivate, !$includePrivate))
                    ->values(),
            ]);
        })->values();

        return [
            'stage_id' => $stage->id,
            'name' => 'Tes Kesehatan',
            'location' => self::HEALTH_ROOM_NAME,
            'rooms' => $roomPayloads,
            'waiting_count' => $waitingCount,
            'current_calls' => $current
                ->map(fn (PmbQueueStageStep $step) => $this->stepPayload($step, $includePrivate, !$includePrivate))
                ->values(),
            'waiting' => $waiting->map(fn (PmbQueueStageStep $step) => $this->stepPayload($step, $includePrivate))->values(),
        ];
    }

    private function interviewBoardPayload(PmbQueueStage $stage, bool $includePrivate = true): array
    {
        $rooms = $stage->rooms
            ->whereNotNull('assigned_admin_id')
            ->filter(fn (PmbTestRoom $room) => $room->assignedOfficer?->jurusan_id)
            ->sortBy(['sort_order', 'id'])
            ->values();

        if ($rooms->isEmpty()) {
            return [];
        }

        return $rooms
            ->groupBy(fn (PmbTestRoom $room) => $room->assignedOfficer->jurusan->interviewPoolKey())
            ->map(function ($programRooms) use ($stage, $includePrivate): array {
                $roomIds = $programRooms->pluck('id');
                $jurusan = $programRooms->first()->assignedOfficer->jurusan;
                $jurusanIds = $jurusan->interviewPoolIds();

                $current = PmbQueueStageStep::with(['queue.user.jurusan', 'room'])
                    ->where('stage_id', $stage->id)
                    ->whereIn('room_id', $roomIds)
                    ->whereIn('status', [PmbQueueStageStep::STATUS_CALLED, PmbQueueStageStep::STATUS_PROCESSING])
                    ->whereHas('queue', fn ($query) => $query->where('current_stage_id', $stage->id))
                    ->orderBy('called_at')
                    ->get();

                $waitingQuery = PmbQueueStageStep::with(['queue.user.jurusan', 'room'])
                    ->where('stage_id', $stage->id)
                    ->where('status', PmbQueueStageStep::STATUS_WAITING)
                    ->whereHas('queue', fn ($query) => $query->where('current_stage_id', $stage->id))
                    ->whereHas('queue.user', fn ($query) => $query
                        ->whereIn('jurusan_id', $jurusanIds)
                        ->isPmbVerified());

                $waitingCount = (clone $waitingQuery)->count();
                $this->orderStepsByCheckInQueue($waitingQuery);
                $waiting = $waitingQuery->limit(8)->get();
                $currentByRoom = $current->groupBy('room_id');

                $roomPayloads = $programRooms->map(function (PmbTestRoom $room) use ($currentByRoom, $includePrivate): array {
                    $activeCalls = $currentByRoom->get($room->id, collect());
                    $isInactive = in_array($room->status, [PmbTestRoom::STATUS_PAUSED, PmbTestRoom::STATUS_CLOSED], true);

                    return array_merge($this->roomPayload($room), [
                        'occupancy_status' => $isInactive ? 'Tidak Aktif' : ($activeCalls->isNotEmpty() ? 'Terisi' : 'Kosong'),
                        'occupancy_tone' => $isInactive ? 'muted' : ($activeCalls->isNotEmpty() ? 'busy' : 'ready'),
                        'current_calls' => $activeCalls
                            ->map(fn (PmbQueueStageStep $step) => $this->stepPayload($step, $includePrivate, !$includePrivate))
                            ->values(),
                    ]);
                })->values();

                return [
                    'jurusan_ids' => $jurusanIds,
                    'name' => $jurusan->interviewPoolLabel(),
                    'waiting_count' => $waitingCount,
                    'rooms' => $roomPayloads,
                    'current_calls' => $current
                        ->map(fn (PmbQueueStageStep $step) => $this->stepPayload($step, $includePrivate, !$includePrivate))
                        ->values(),
                    'waiting' => $waiting
                        ->map(fn (PmbQueueStageStep $step) => $this->stepPayload($step, $includePrivate))
                        ->values(),
                ];
            })
            ->values()
            ->all();
    }

    private function interviewRoomForQueue(PmbOfflineQueue $queue): ?int
    {
        $this->ensureInterviewRooms($queue->session);

        return $this->programRoomForQueue($queue, PmbQueueStage::KEY_WAWANCARA_KAPRODI, 'W');
    }

    private function legacyInterviewRoomForOfficer(PmbQueueStage $stage, Admin $officer): ?PmbTestRoom
    {
        $program = strtoupper((string) $officer->jurusan?->nama_jurusan);
        $code = match (true) {
            str_contains($program, 'KEBIDANAN') => 'W-D3-KEB',
            str_contains($program, 'FARMASI') => 'W-S1-FAR',
            str_contains($program, 'GIZI') => 'W-S1-GIZ',
            default => null,
        };

        $query = PmbTestRoom::where('session_id', $stage->session_id)
            ->where('stage_id', $stage->id);

        if ($code) {
            $room = (clone $query)
                ->where('code', $code)
                ->lockForUpdate()
                ->first();

            if ($room) {
                return $room;
            }
        }

        return (clone $query)
            ->whereNull('assigned_admin_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->lockForUpdate()
            ->first()
            ?: (clone $query)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();
    }

    private function firstRoomForStage(PmbOfflineQueue $queue, string $stageKey): ?int
    {
        $stageId = PmbQueueStage::where('session_id', $queue->session_id)
            ->where('key', $stageKey)
            ->value('id');

        return PmbTestRoom::where('session_id', $queue->session_id)
            ->where('stage_id', $stageId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->value('id');
    }

    private function programRoomForQueue(PmbOfflineQueue $queue, string $stageKey, string $prefix): ?int
    {
        $program = strtoupper((string) ($queue->user?->jurusan?->nama_jurusan ?? ''));
        $code = match (true) {
            str_contains($program, 'KEBIDANAN') => $prefix . '-D3-KEB',
            str_contains($program, 'FARMASI') => $prefix . '-S1-FAR',
            str_contains($program, 'GIZI') => $prefix . '-S1-GIZ',
            default => null,
        };

        $stageId = PmbQueueStage::where('session_id', $queue->session_id)
            ->where('key', $stageKey)
            ->value('id');

        $query = PmbTestRoom::where('session_id', $queue->session_id)
            ->where('stage_id', $stageId);

        if ($code) {
            return (clone $query)->where('code', $code)->value('id') ?: PmbTestRoom::where('session_id', $queue->session_id)->where('code', $code)->value('id');
        }

        return $query->orderBy('sort_order')->value('id');
    }

    private function interviewRoomDefinitions(): array
    {
        return [
            ['code' => 'W-D3-KEB', 'name' => 'Wawancara D3 Kebidanan', 'sort_order' => 101],
            ['code' => 'W-S1-FAR', 'name' => 'Wawancara S1 Farmasi', 'sort_order' => 102],
            ['code' => 'W-S1-GIZ', 'name' => 'Wawancara S1 Gizi', 'sort_order' => 103],
        ];
    }

    private function stageUsesProgramRooms(?PmbQueueStage $stage): bool
    {
        return $stage?->key === PmbQueueStage::KEY_WAWANCARA_KAPRODI;
    }

    private function receivableRoomForStep(PmbQueueStageStep $step, PmbQueueStage $stage): ?PmbTestRoom
    {
        $roomId = $step->room_id ?: $stage->default_room_id;

        if (!$roomId) {
            return null;
        }

        $room = PmbTestRoom::whereKey($roomId)->lockForUpdate()->first();

        if (!$room || $room->session_id !== $stage->session_id || $room->stage_id !== $stage->id) {
            return null;
        }

        return $this->roomCanReceive($room) ? $room : null;
    }

    private function availableRoomForStage(PmbQueueStage $stage): ?PmbTestRoom
    {
        $rooms = PmbTestRoom::where('session_id', $stage->session_id)
            ->where('stage_id', $stage->id)
            ->whereNotIn('status', [PmbTestRoom::STATUS_PAUSED, PmbTestRoom::STATUS_CLOSED])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $candidate = $rooms
            ->map(function (PmbTestRoom $room): array {
                $activeCount = $this->roomActiveCount($room);

                return [
                    'room' => $room,
                    'available_slots' => $room->capacity - $activeCount,
                ];
            })
            ->filter(fn (array $candidate): bool => $candidate['available_slots'] > 0)
            ->sort(function (array $a, array $b): int {
                return ($b['available_slots'] <=> $a['available_slots'])
                    ?: ($a['room']->sort_order <=> $b['room']->sort_order)
                    ?: ($a['room']->id <=> $b['room']->id);
            })
            ->first();

        return $candidate['room'] ?? null;
    }

    private function orderStepsForCall(Builder $query, PmbQueueStage $stage): void
    {
        if ($stage->key === PmbQueueStage::KEY_TES_KESEHATAN) {
            $this->orderStepsByCheckInQueue($query);

            return;
        }

        $query->orderBy('queued_at')->orderBy('id');
    }

    private function orderStepsByCheckInQueue(Builder $query): void
    {
        $query
            ->orderBy(PmbOfflineQueue::select('queue_number')
                ->whereColumn('pmb_offline_queues.id', 'pmb_queue_stage_steps.queue_id')
                ->limit(1))
            ->orderBy(PmbOfflineQueue::select('checked_in_at')
                ->whereColumn('pmb_offline_queues.id', 'pmb_queue_stage_steps.queue_id')
                ->limit(1))
            ->orderBy(PmbOfflineQueue::select('id')
                ->whereColumn('pmb_offline_queues.id', 'pmb_queue_stage_steps.queue_id')
                ->limit(1))
            ->orderBy('pmb_queue_stage_steps.id');
    }

    private function roomActiveCount(PmbTestRoom $room): int
    {
        return PmbQueueStageStep::where('room_id', $room->id)
            ->whereIn('status', [PmbQueueStageStep::STATUS_CALLED, PmbQueueStageStep::STATUS_PROCESSING])
            ->count();
    }

    private function roomCanReceive(PmbTestRoom $room): bool
    {
        if (in_array($room->status, [PmbTestRoom::STATUS_PAUSED, PmbTestRoom::STATUS_CLOSED], true)) {
            return false;
        }

        $room->loadMissing('stage');
        if ($room->stage?->key === PmbQueueStage::KEY_WAWANCARA_KAPRODI
            || ($room->stage?->key === PmbQueueStage::KEY_TES_KESEHATAN
                && $room->code === self::HEALTH_ROOM_CODE
                && !$room->assigned_admin_id)) {
            return true;
        }

        return $this->roomActiveCount($room) < $room->capacity;
    }

    private function healthServiceRooms(PmbQueueStage $stage, bool $lockForUpdate = false): Collection
    {
        $query = PmbTestRoom::where('session_id', $stage->session_id)
            ->where('stage_id', $stage->id)
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $rooms = $query->get();
        $customRooms = $rooms
            ->reject(fn (PmbTestRoom $room) => $room->code === self::HEALTH_ROOM_CODE && !$room->assigned_admin_id)
            ->values();

        return $customRooms->isNotEmpty()
            ? $customRooms
            : $rooms
                ->filter(fn (PmbTestRoom $room) => $room->code === self::HEALTH_ROOM_CODE && !$room->assigned_admin_id)
                ->values();
    }

    private function receivableHealthRoom(PmbQueueStage $stage): ?PmbTestRoom
    {
        return $this->healthServiceRooms($stage, true)
            ->first(fn (PmbTestRoom $room) => $this->roomCanReceive($room));
    }

    private function roomPayload(PmbTestRoom $room): array
    {
        $activeCount = $this->roomActiveCount($room);

        return [
            'id' => $room->id,
            'name' => $room->displayName(),
            'code' => $room->code,
            'status' => $room->status,
            'capacity' => $room->capacity,
            'active_count' => $activeCount,
            'available_slots' => max(0, $room->capacity - $activeCount),
            'display_status' => $this->roomDisplayStatus($room, $activeCount),
            'display_tone' => $this->roomDisplayTone($room, $activeCount),
        ];
    }

    private function roomDisplayStatus(PmbTestRoom $room, int $activeCount): string
    {
        if (in_array($room->status, [PmbTestRoom::STATUS_PAUSED, PmbTestRoom::STATUS_CLOSED], true)) {
            return $room->status === PmbTestRoom::STATUS_PAUSED ? 'Istirahat' : 'Tutup';
        }

        $room->loadMissing('stage');
        if (in_array($room->stage?->key, [PmbQueueStage::KEY_TES_KESEHATAN, PmbQueueStage::KEY_WAWANCARA_KAPRODI], true)) {
            return $activeCount > 0 ? 'Proses' : 'Ready';
        }

        if ($activeCount >= $room->capacity) {
            return 'Penuh';
        }

        return $activeCount > 0 ? 'Proses' : 'Ready';
    }

    private function roomDisplayTone(PmbTestRoom $room, int $activeCount): string
    {
        if (in_array($room->status, [PmbTestRoom::STATUS_PAUSED, PmbTestRoom::STATUS_CLOSED], true)) {
            return 'muted';
        }

        $room->loadMissing('stage');
        if (in_array($room->stage?->key, [PmbQueueStage::KEY_TES_KESEHATAN, PmbQueueStage::KEY_WAWANCARA_KAPRODI], true)) {
            return $activeCount > 0 ? 'busy' : 'ready';
        }

        if ($activeCount >= $room->capacity) {
            return 'full';
        }

        return $activeCount > 0 ? 'busy' : 'ready';
    }

    private function programKeywordFromRoomCode(string $code): ?string
    {
        return match (true) {
            str_contains($code, 'KEB') => 'KEBIDANAN',
            str_contains($code, 'FAR') => 'FARMASI',
            str_contains($code, 'GIZ') => 'GIZI',
            default => null,
        };
    }

    private function writeLegacyStatusLog(PmbOfflineQueue $queue, ?string $fromStatus, string $toStatus, ?int $adminId, ?string $note): void
    {
        PmbQueueStatusLog::create([
            'session_id' => $queue->session_id,
            'queue_id' => $queue->id,
            'admin_id' => $adminId,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'note' => $note,
        ]);
    }

    private function writeCallLog(PmbQueueStageStep $step, ?string $fromStatus, string $toStatus, ?int $adminId, string $action): void
    {
        PmbQueueCallLog::create([
            'session_id' => $step->stage->session_id,
            'queue_id' => $step->queue_id,
            'room_id' => $step->room_id,
            'admin_id' => $adminId,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'call_number' => max(1, $step->call_count),
            'called_at' => now(),
            'metadata' => ['stage_id' => $step->stage_id, 'stage' => $step->stage->key],
        ]);
    }

    private function nextArrivalNumber(int $sessionId): int
    {
        DB::table('pmb_test_sessions')->where('id', $sessionId)->lockForUpdate()->first();
        $lastNumber = (int) PmbOfflineQueue::where('session_id', $sessionId)
            ->whereNotNull('queue_number')
            ->max('queue_number');

        DB::table('pmb_queue_arrival_counters')->updateOrInsert(
            ['session_id' => $sessionId],
            [
                'last_number' => $lastNumber,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $counter = DB::table('pmb_queue_arrival_counters')
            ->where('session_id', $sessionId)
            ->lockForUpdate()
            ->first();

        $next = ((int) $counter->last_number) + 1;

        DB::table('pmb_queue_arrival_counters')
            ->where('session_id', $sessionId)
            ->update(['last_number' => $next, 'updated_at' => now()]);

        return $next;
    }

    public function assertSessionAllowsCheckIn(PmbTestSession $session): void
    {
        if ($session->status !== PmbTestSession::STATUS_OPEN) {
            throw new DomainException('Check-in belum dibuka untuk sesi ini.');
        }

        $now = now();
        if ($session->checkin_open_at && $now->lt($session->checkin_open_at)) {
            throw new DomainException('Check-in belum masuk jadwal yang ditentukan.');
        }

        if ($session->checkin_close_at && $now->gt($session->checkin_close_at)) {
            throw new DomainException('Check-in sudah ditutup untuk sesi ini.');
        }
    }

    private function assertQueueCanCheckIn(PmbOfflineQueue $queue): void
    {
        if (in_array($queue->status, [
            PmbOfflineQueue::STATUS_COMPLETED,
            PmbOfflineQueue::STATUS_CANCELLED,
            PmbOfflineQueue::STATUS_NO_SHOW,
        ], true)) {
            throw new DomainException('Peserta tidak bisa check-in dari status saat ini.');
        }

        if (!$queue->checked_in_at && $queue->status !== PmbOfflineQueue::STATUS_REGISTERED) {
            throw new DomainException('Peserta hanya bisa check-in dari status terdaftar.');
        }
    }

    private function assertQueueCanBeTerminalChanged(PmbOfflineQueue $queue, string $action): void
    {
        if ($queue->status === PmbOfflineQueue::STATUS_COMPLETED) {
            throw new DomainException('Peserta yang sudah selesai tidak dapat diubah ke status ' . $action . '.');
        }

        if (in_array($queue->status, [PmbOfflineQueue::STATUS_CANCELLED, PmbOfflineQueue::STATUS_NO_SHOW], true)) {
            throw new DomainException('Peserta sudah berada pada status akhir. Gunakan aktifkan kembali bila diperlukan.');
        }
    }

    private function assertStepActionAllowed(PmbQueueStageStep $step, string $action): void
    {
        $queue = $step->queue;

        if (!$queue?->checked_in_at) {
            throw new DomainException('Peserta belum check-in.');
        }

        if (in_array($queue->status, [
            PmbOfflineQueue::STATUS_COMPLETED,
            PmbOfflineQueue::STATUS_CANCELLED,
            PmbOfflineQueue::STATUS_NO_SHOW,
        ], true)) {
            throw new DomainException('Peserta tidak dapat diproses dari status saat ini.');
        }

        $status = $step->status;
        $allowed = match ($action) {
            'call', 'start', 'in_room' => [PmbQueueStageStep::STATUS_WAITING, PmbQueueStageStep::STATUS_SKIPPED],
            'recall' => [PmbQueueStageStep::STATUS_CALLED, PmbQueueStageStep::STATUS_PROCESSING],
            'skip' => [PmbQueueStageStep::STATUS_WAITING, PmbQueueStageStep::STATUS_PROCESSING],
            'complete', 'complete_test_tulis', 'complete_health', 'hold_health_form' => [PmbQueueStageStep::STATUS_PROCESSING, PmbQueueStageStep::STATUS_WAITING, PmbQueueStageStep::STATUS_HELD],
            'release', 'release_health_form' => [PmbQueueStageStep::STATUS_HELD, PmbQueueStageStep::STATUS_PROBLEM],
            'hold' => [PmbQueueStageStep::STATUS_WAITING, PmbQueueStageStep::STATUS_PROCESSING, PmbQueueStageStep::STATUS_PROBLEM],
            'problem' => [PmbQueueStageStep::STATUS_WAITING, PmbQueueStageStep::STATUS_PROCESSING, PmbQueueStageStep::STATUS_HELD],
            default => [],
        };

        if (!in_array($status, $allowed, true)) {
            throw new DomainException('Aksi tidak valid untuk status tahap peserta saat ini.');
        }
    }

    private function assertRoomCanReceive(PmbTestRoom $room): void
    {
        $lockedRoom = PmbTestRoom::whereKey($room->id)->lockForUpdate()->first();
        $room = $lockedRoom ?: $room;

        if (in_array($room->status, [PmbTestRoom::STATUS_PAUSED, PmbTestRoom::STATUS_CLOSED], true)) {
            throw new DomainException('Ruangan sedang tidak tersedia.');
        }

        if (!$this->roomCanReceive($room)) {
            throw new DomainException('Ruangan sudah penuh.');
        }
    }

    private function writeActivityLog(PmbOfflineQueue $queue, string $action, string $description, array $metadata = []): void
    {
        app(ActivityLogger::class)->log(
            'pmb_queue',
            $action,
            $description,
            $queue,
            [],
            ['status' => $queue->status, 'overall_status' => $queue->overall_status],
            $metadata
        );
    }
}
