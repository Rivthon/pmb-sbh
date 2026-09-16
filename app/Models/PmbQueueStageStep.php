<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PmbQueueStageStep extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_WAITING = 'waiting';
    public const STATUS_CALLED = 'called';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_HELD = 'held';
    public const STATUS_SKIPPED = 'skipped';
    public const STATUS_PROBLEM = 'problem';
    public const STATUS_REENTRY = 'reentry';

    protected $fillable = [
        'queue_id',
        'stage_id',
        'room_id',
        'assigned_officer_id',
        'stage_queue_number',
        'stage_queue_code',
        'status',
        'queued_at',
        'called_at',
        'started_at',
        'completed_at',
        'held_at',
        'skipped_at',
        'call_count',
        'notes',
    ];

    protected $casts = [
        'stage_queue_number' => 'integer',
        'call_count' => 'integer',
        'queued_at' => 'datetime',
        'called_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'held_at' => 'datetime',
        'skipped_at' => 'datetime',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Belum Dimulai',
            self::STATUS_WAITING => 'Menunggu',
            self::STATUS_CALLED => 'Dipanggil',
            self::STATUS_PROCESSING => 'Sedang Diproses',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_HELD => 'Ditahan',
            self::STATUS_SKIPPED => 'Dilewati',
            self::STATUS_PROBLEM => 'Bermasalah',
            self::STATUS_REENTRY => 'Masuk Kembali',
        ];
    }

    public static function statusTones(): array
    {
        return [
            self::STATUS_PENDING => 'secondary',
            self::STATUS_WAITING => 'primary',
            self::STATUS_CALLED => 'warning',
            self::STATUS_PROCESSING => 'dark',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_HELD => 'info',
            self::STATUS_SKIPPED => 'secondary',
            self::STATUS_PROBLEM => 'danger',
            self::STATUS_REENTRY => 'warning',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? Str::headline($this->status);
    }

    public function statusTone(): string
    {
        return self::statusTones()[$this->status] ?? 'secondary';
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(PmbOfflineQueue::class, 'queue_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PmbQueueStage::class, 'stage_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(PmbTestRoom::class, 'room_id');
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_officer_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PmbQueueStageLog::class, 'step_id');
    }
}
