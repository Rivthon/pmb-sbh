<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmbQueueStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'queue_id',
        'admin_id',
        'from_status',
        'to_status',
        'note',
        'metadata',
    ];

    protected $casts = [
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

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
