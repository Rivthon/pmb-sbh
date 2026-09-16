<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PmbOfflineQueue;
use App\Models\PmbTestSession;
use App\Services\Pmb\PmbOfflineQueueWorkflowService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PmbQueueController extends Controller
{
    public function __construct(private readonly PmbOfflineQueueWorkflowService $workflow)
    {
    }

    public function me(Request $request): JsonResponse
    {
        $queues = PmbOfflineQueue::with(['session.periode', 'session.gelombang', 'room', 'user.jurusan', 'currentStage', 'stageSteps.stage', 'stageSteps.room'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn (PmbOfflineQueue $queue) => $this->queuePayload($queue));

        return response()->json(['data' => $queues]);
    }

    public function board(PmbTestSession $session): JsonResponse
    {
        return response()->json($this->workflow->boardPayload($session, false));
    }

    public function checkIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'queue_uuid' => ['required', 'string'],
        ]);

        $queue = PmbOfflineQueue::where('uuid', $validated['queue_uuid'])
            ->where('user_id', $request->user()->id)
            ->with('session')
            ->firstOrFail();

        try {
            $this->workflow->assertSessionAllowsCheckIn($queue->session);
            $this->workflow->checkIn($queue);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $this->queuePayload($queue->fresh(['session', 'room', 'user.jurusan', 'currentStage', 'stageSteps.stage', 'stageSteps.room']))]);
    }

    private function queuePayload(PmbOfflineQueue $queue): array
    {
        return [
            'uuid' => $queue->uuid,
            'queue_code' => $queue->queue_code,
            'status' => $queue->status,
            'status_label' => $queue->statusLabel(),
            'arrival_status' => $queue->arrival_status,
            'overall_status' => $queue->overall_status,
            'priority' => $queue->priority,
            'current_stage' => $queue->currentStage ? [
                'id' => $queue->currentStage->id,
                'key' => $queue->currentStage->key,
                'name' => $queue->currentStage->name,
            ] : null,
            'stages' => $queue->stageSteps?->map(fn ($step) => [
                'id' => $step->id,
                'stage' => $step->stage?->name,
                'stage_key' => $step->stage?->key,
                'queue_code' => $queue->queue_code,
                'status' => $step->status,
                'status_label' => $step->statusLabel(),
                'room' => $step->room?->name,
            ])->values(),
            'participant' => [
                'name' => $queue->user->name,
                'email' => $queue->user->email,
                'program_studi' => $queue->user->jurusan->nama_jurusan ?? null,
            ],
            'session' => [
                'uuid' => $queue->session->uuid ?? null,
                'code' => $queue->session->code ?? null,
                'name' => $queue->session->name ?? null,
                'starts_at' => optional($queue->session?->starts_at)->toIso8601String(),
            ],
            'room' => $queue->room?->name,
            'checked_in_at' => optional($queue->checked_in_at)->toIso8601String(),
            'last_called_at' => optional($queue->last_called_at)->toIso8601String(),
            'completed_at' => optional($queue->completed_at)->toIso8601String(),
        ];
    }
}
