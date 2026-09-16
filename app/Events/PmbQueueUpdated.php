<?php

namespace App\Events;

use App\Models\PmbOfflineQueue;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PmbQueueUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public PmbOfflineQueue $queue, public string $action)
    {
        $this->queue->loadMissing(['user.jurusan', 'room', 'session', 'currentStage']);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('pmb.queue.session.' . $this->queue->session_id),
            new PrivateChannel('pmb.queue.user.' . $this->queue->user_id),
            new Channel('pmb.queue.signage.' . $this->queue->session_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'queue.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'session_id' => $this->queue->session_id,
            'queue' => [
                'uuid' => $this->queue->uuid,
                'queue_code' => $this->queue->queue_code,
                'status' => $this->queue->status,
                'status_label' => $this->queue->statusLabel(),
                'arrival_status' => $this->queue->arrival_status,
                'overall_status' => $this->queue->overall_status,
                'program_studi' => $this->queue->user?->jurusan?->nama_jurusan,
                'current_stage' => $this->queue->currentStage?->name,
                'room' => $this->queue->room?->name,
                'checked_in_at' => optional($this->queue->checked_in_at)->toIso8601String(),
                'last_called_at' => optional($this->queue->last_called_at)->toIso8601String(),
            ],
        ];
    }
}
