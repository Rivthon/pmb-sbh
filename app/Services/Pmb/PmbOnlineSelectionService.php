<?php

namespace App\Services\Pmb;

use App\Models\PmbOfflineQueue;
use App\Models\PmbQueueStage;
use App\Models\PmbQueueStageLog;
use App\Models\PmbQueueStageStep;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PmbOnlineSelectionService
{
    public function __construct(private readonly PmbOfflineQueueWorkflowService $workflow)
    {
    }

    public function ensureSteps(PmbOfflineQueue $queue): Collection
    {
        $steps = $this->workflow->ensureSteps($queue->loadMissing('session'));

        if ($queue->isOnlineSelection()) {
            $pemberkasan = $steps->first(fn ($step) => $step->stage?->key === PmbQueueStage::KEY_PEMBERKASAN);
            if ($pemberkasan && $pemberkasan->status === PmbQueueStageStep::STATUS_PENDING) {
                $this->transition($pemberkasan, PmbQueueStageStep::STATUS_SKIPPED, 'online_no_checkin', [
                    'skipped_at' => now(),
                    'notes' => 'Peserta online tidak menggunakan pemberkasan/check-in fisik.',
                ]);
            }

            if (!$queue->requiresTesTulis()) {
                $written = $steps->first(fn ($step) => $step->stage?->key === PmbQueueStage::KEY_TES_TULIS);
                if ($written && $written->status === PmbQueueStageStep::STATUS_PENDING) {
                    $this->transition($written, PmbQueueStageStep::STATUS_SKIPPED, 'online_skip_written', [
                        'skipped_at' => now(),
                        'notes' => 'Peserta online bebas Tes Tulis.',
                    ]);
                }
            }
        }

        return $queue->stageSteps()->with(['stage', 'room', 'officer'])->get();
    }

    public function markWrittenCompleted(PmbOfflineQueue $queue): void
    {
        $this->markStage($queue, PmbQueueStage::KEY_TES_TULIS, PmbQueueStageStep::STATUS_COMPLETED, 'online_written_completed');
    }

    public function syncWrittenRequirement(PmbOfflineQueue $queue): void
    {
        $steps = $this->ensureSteps($queue->fresh(['session']));
        $written = $steps->first(fn ($step) => $step->stage?->key === PmbQueueStage::KEY_TES_TULIS);
        if (!$written) return;

        if ($queue->fresh()->requiresTesTulis() && $written->status === PmbQueueStageStep::STATUS_SKIPPED) {
            $this->transition($written, PmbQueueStageStep::STATUS_PENDING, 'online_require_written', [
                'skipped_at' => null,
                'notes' => 'Jalur peserta dikoreksi menjadi ikut Tes Tulis sebelum seleksi dimulai.',
            ]);
        }
    }

    public function markAnamnesisSubmitted(PmbOfflineQueue $queue): void
    {
        $this->markStage($queue, PmbQueueStage::KEY_TES_KESEHATAN, PmbQueueStageStep::STATUS_PROCESSING, 'online_anamnesis_submitted');
    }

    public function markHealthLetterUploaded(PmbOfflineQueue $queue): void
    {
        $this->markStage($queue, PmbQueueStage::KEY_TES_KESEHATAN, PmbQueueStageStep::STATUS_COMPLETED, 'online_health_letter_uploaded');
    }

    public function markInterviewFormSubmitted(PmbOfflineQueue $queue): void
    {
        $this->markStage($queue, PmbQueueStage::KEY_WAWANCARA_KAPRODI, PmbQueueStageStep::STATUS_WAITING, 'online_interview_form_submitted');
    }

    public function markInterviewCompleted(PmbOfflineQueue $queue, ?int $adminId = null): void
    {
        $this->markStage($queue, PmbQueueStage::KEY_WAWANCARA_KAPRODI, PmbQueueStageStep::STATUS_COMPLETED, 'online_interview_reviewed', $adminId);
    }

    public function markInterviewReentry(PmbOfflineQueue $queue, ?int $adminId = null): void
    {
        $this->markStage($queue, PmbQueueStage::KEY_WAWANCARA_KAPRODI, PmbQueueStageStep::STATUS_REENTRY, 'online_interview_reentry', $adminId);
    }

    public function recompute(PmbOfflineQueue $queue): void
    {
        $queue->loadMissing('stageSteps.stage');
        $steps = $queue->stageSteps->keyBy(fn ($step) => $step->stage?->key);
        $writtenDone = in_array($steps->get(PmbQueueStage::KEY_TES_TULIS)?->status, [PmbQueueStageStep::STATUS_COMPLETED, PmbQueueStageStep::STATUS_SKIPPED], true);
        $healthDone = $steps->get(PmbQueueStage::KEY_TES_KESEHATAN)?->status === PmbQueueStageStep::STATUS_COMPLETED;
        $interviewDone = $steps->get(PmbQueueStage::KEY_WAWANCARA_KAPRODI)?->status === PmbQueueStageStep::STATUS_COMPLETED;
        $complete = $writtenDone && $healthDone && $interviewDone;

        $queue->update([
            'overall_status' => $complete ? PmbOfflineQueue::OVERALL_COMPLETED : PmbOfflineQueue::OVERALL_IN_PROCESS,
            'status' => $complete ? PmbOfflineQueue::STATUS_COMPLETED : PmbOfflineQueue::STATUS_REGISTERED,
            'completed_at' => $complete ? ($queue->completed_at ?: now()) : null,
        ]);
    }

    private function markStage(PmbOfflineQueue $queue, string $key, string $status, string $action, ?int $adminId = null): void
    {
        DB::transaction(function () use ($queue, $key, $status, $action, $adminId): void {
            $steps = $this->ensureSteps($queue);
            $step = $steps->first(fn ($item) => $item->stage?->key === $key);
            if (!$step) {
                return;
            }

            $attributes = match ($status) {
                PmbQueueStageStep::STATUS_COMPLETED => ['completed_at' => now()],
                PmbQueueStageStep::STATUS_PROCESSING => ['started_at' => $step->started_at ?: now()],
                PmbQueueStageStep::STATUS_WAITING => ['queued_at' => $step->queued_at ?: now()],
                default => [],
            };
            $this->transition($step, $status, $action, $attributes, $adminId);
            $this->recompute($queue->fresh());
        });
    }

    private function transition(PmbQueueStageStep $step, string $status, string $action, array $attributes = [], ?int $adminId = null): void
    {
        $from = $step->status;
        $step->update(array_merge($attributes, ['status' => $status]));

        PmbQueueStageLog::create([
            'step_id' => $step->id,
            'queue_id' => $step->queue_id,
            'stage_id' => $step->stage_id,
            'admin_id' => $adminId,
            'action' => $action,
            'from_status' => $from,
            'to_status' => $status,
        ]);
    }
}
