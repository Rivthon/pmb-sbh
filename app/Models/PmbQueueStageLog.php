<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmbQueueStageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'step_id',
        'queue_id',
        'stage_id',
        'admin_id',
        'action',
        'from_status',
        'to_status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function step(): BelongsTo
    {
        return $this->belongsTo(PmbQueueStageStep::class, 'step_id');
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(PmbOfflineQueue::class, 'queue_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PmbQueueStage::class, 'stage_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
