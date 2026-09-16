<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmbQueueCounter extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'stage_id',
        'last_number',
    ];

    protected $casts = [
        'last_number' => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(PmbTestSession::class, 'session_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PmbQueueStage::class, 'stage_id');
    }
}
