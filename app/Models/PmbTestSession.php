<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PmbTestSession extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const TYPE_TULIS = 'tulis';
    public const TYPE_WAWANCARA = 'wawancara';
    public const TYPE_KESEHATAN = 'kesehatan';
    public const TYPE_CAMPURAN = 'campuran';

    protected $fillable = [
        'uuid',
        'code',
        'name',
        'type',
        'periode_id',
        'gelombang_id',
        'tes_tulis_id',
        'starts_at',
        'ends_at',
        'online_test_starts_at',
        'online_test_ends_at',
        'health_starts_at',
        'health_ends_at',
        'interview_starts_at',
        'interview_ends_at',
        'zoom_url',
        'checkin_open_at',
        'checkin_close_at',
        'status',
        'notes',
        'created_by_admin_id',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'online_test_starts_at' => 'datetime',
        'online_test_ends_at' => 'datetime',
        'health_starts_at' => 'datetime',
        'health_ends_at' => 'datetime',
        'interview_starts_at' => 'datetime',
        'interview_ends_at' => 'datetime',
        'checkin_open_at' => 'datetime',
        'checkin_close_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $session): void {
            $session->uuid ??= (string) Str::uuid();
            $session->code ??= static::nextCode();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public static function nextCode(): string
    {
        $prefix = 'PMB-' . now()->format('ymd') . '-';
        $latest = static::withTrashed()
            ->where('code', 'like', $prefix . '%')
            ->orderByDesc('code')
            ->value('code');

        $next = $latest ? ((int) substr($latest, -4)) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SCHEDULED => 'Terjadwal',
            self::STATUS_OPEN => 'Check-in Dibuka',
            self::STATUS_CLOSED => 'Ditutup',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
        ];
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_CAMPURAN => 'Campuran',
            self::TYPE_TULIS => 'Tes Tulis',
            self::TYPE_WAWANCARA => 'Wawancara',
            self::TYPE_KESEHATAN => 'Tes Kesehatan',
        ];
    }

    public function isOnlineSelectionOpen(): bool
    {
        return $this->status === self::STATUS_OPEN
            && $this->isWindowOpen($this->online_test_starts_at, $this->online_test_ends_at);
    }

    public function isOnlineHealthOpen(): bool
    {
        return $this->isWindowOpen($this->health_starts_at, $this->health_ends_at);
    }

    public function isOnlineInterviewFormOpen(): bool
    {
        return $this->isWindowOpen($this->interview_starts_at, $this->interview_ends_at);
    }

    public function isZoomVisible(): bool
    {
        return filled($this->zoom_url)
            && $this->isOnlineInterviewFormOpen();
    }

    private function isWindowOpen($startsAt, $endsAt): bool
    {
        return (!$startsAt || $startsAt->isPast())
            && (!$endsAt || $endsAt->isFuture());
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }

    public function gelombang(): BelongsTo
    {
        return $this->belongsTo(Gelombang::class);
    }

    public function tesTulis(): BelongsTo
    {
        return $this->belongsTo(TesTulis::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(PmbTestRoom::class, 'session_id')->orderBy('sort_order')->orderBy('name');
    }

    public function queues(): HasMany
    {
        return $this->hasMany(PmbOfflineQueue::class, 'session_id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(PmbQueueStage::class, 'session_id')->orderBy('sort_order')->orderBy('id');
    }
}
