<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmbQueueCallLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'queue_id',
        'room_id',
        'admin_id',
        'action',
        'from_status',
        'to_status',
        'call_number',
        'called_at',
        'metadata',
    ];

    protected $casts = [
        'called_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(PmbTestSession::class, 'session_id');
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(PmbOfflineQueue::class, 'queue_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(PmbTestRoom::class, 'room_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
