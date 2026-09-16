<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PmbTestRoom extends Model
{
    use HasFactory;

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_BUSY = 'busy';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'uuid',
        'session_id',
        'stage_id',
        'assigned_admin_id',
        'name',
        'code',
        'capacity',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $room): void {
            $room->uuid ??= (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_AVAILABLE => 'Siap',
            self::STATUS_BUSY => 'Berjalan',
            self::STATUS_PAUSED => 'Istirahat',
            self::STATUS_CLOSED => 'Ditutup',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(PmbTestSession::class, 'session_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PmbQueueStage::class, 'stage_id');
    }

    public function assignedOfficer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_admin_id');
    }

    public function queues(): HasMany
    {
        return $this->hasMany(PmbOfflineQueue::class, 'room_id');
    }

    public function stageSteps(): HasMany
    {
        return $this->hasMany(PmbQueueStageStep::class, 'room_id');
    }

    public function displayName(): string
    {
        $this->loadMissing('stage');

        if ($this->stage?->key !== PmbQueueStage::KEY_TES_KESEHATAN) {
            return $this->name;
        }

        if (preg_match('/KES-(\d+)/', (string) $this->code, $matches)) {
            return 'Meja ' . $matches[1];
        }

        $number = PmbTestRoom::query()
            ->where('session_id', $this->session_id)
            ->where('stage_id', $this->stage_id)
            ->where(function ($query): void {
                $query->where('sort_order', '<', $this->sort_order)
                    ->orWhere(function ($nested): void {
                        $nested->where('sort_order', $this->sort_order)
                            ->where('id', '<=', $this->id);
                    });
            })
            ->count();

        return 'Meja ' . max(1, $number);
    }
}
