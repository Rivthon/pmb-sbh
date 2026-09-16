<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use App\Services\Pmb\PmbOfflineQueueWorkflowService;
use DomainException;

class PmbOfflineQueue extends Model
{
    use HasFactory;

    public const STATUS_REGISTERED = 'registered';
    public const STATUS_CHECKED_IN = 'checked_in';
    public const STATUS_WAITING = 'waiting';
    public const STATUS_CALLED = 'called';
    public const STATUS_IN_ROOM = 'in_room';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_SKIPPED = 'skipped';
    public const STATUS_NO_SHOW = 'no_show';
    public const STATUS_CANCELLED = 'cancelled';

    public const ARRIVAL_NOT_CHECKED_IN = 'not_checked_in';
    public const ARRIVAL_CHECKED_IN = 'checked_in';
    public const ARRIVAL_NO_SHOW = 'no_show';
    public const ARRIVAL_CANCELLED = 'cancelled';

    public const OVERALL_NOT_STARTED = 'not_started';
    public const OVERALL_IN_PROCESS = 'in_process';
    public const OVERALL_COMPLETED = 'completed';
    public const OVERALL_NO_SHOW = 'no_show';
    public const OVERALL_CANCELLED = 'cancelled';

    public const MODE_OFFLINE = 'offline';
    public const MODE_ONLINE = 'online';

    protected $fillable = [
        'uuid',
        'session_id',
        'room_id',
        'user_id',
        'queue_number',
        'queue_code',
        'qr_token_hash',
        'status',
        'arrival_status',
        'overall_status',
        'current_stage_id',
        'priority',
        'selection_mode',
        'health_notified_at',
        'checked_in_at',
        'called_at',
        'last_called_at',
        'entered_at',
        'completed_at',
        'no_show_at',
        'cancelled_at',
        'call_count',
        'notes',
        'created_by_admin_id',
        'checked_in_by_admin_id',
    ];

    protected $casts = [
        'queue_number' => 'integer',
        'priority' => 'integer',
        'health_notified_at' => 'datetime',
        'call_count' => 'integer',
        'checked_in_at' => 'datetime',
        'called_at' => 'datetime',
        'last_called_at' => 'datetime',
        'entered_at' => 'datetime',
        'completed_at' => 'datetime',
        'no_show_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $queue): void {
            $queue->uuid ??= (string) Str::uuid();
            if ($queue->selection_mode !== self::MODE_ONLINE) {
                $queue->qr_token_hash ??= hash('sha256', $queue->uuid . '|' . config('app.key'));
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_REGISTERED => 'Terdaftar',
            self::STATUS_CHECKED_IN => 'Check-in',
            self::STATUS_WAITING => 'Menunggu',
            self::STATUS_CALLED => 'Dipanggil',
            self::STATUS_IN_ROOM => 'Proses',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_SKIPPED => 'Dilewati',
            self::STATUS_NO_SHOW => 'Tidak Hadir',
            self::STATUS_CANCELLED => 'Dibatalkan',
        ];
    }

    public static function statusTones(): array
    {
        return [
            self::STATUS_REGISTERED => 'secondary',
            self::STATUS_CHECKED_IN => 'info',
            self::STATUS_WAITING => 'primary',
            self::STATUS_CALLED => 'warning',
            self::STATUS_IN_ROOM => 'dark',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_SKIPPED => 'secondary',
            self::STATUS_NO_SHOW => 'danger',
            self::STATUS_CANCELLED => 'danger',
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

    public function signedCheckInUrl(): string
    {
        if ($this->isOnlineSelection()) {
            throw new DomainException('Peserta online tidak menggunakan QR atau check-in.');
        }

        return URL::signedRoute('admin.pmb-queues.check-in-link', $this);
    }

    public function healthLetterDueAt()
    {
        return $this->session?->health_ends_at ?: $this->health_notified_at?->copy()->addDays(7);
    }

    public function isHealthLetterLate(?\DateTimeInterface $uploadedAt = null): bool
    {
        $dueAt = $this->healthLetterDueAt();

        return $dueAt !== null && ($uploadedAt ? $uploadedAt > $dueAt : now()->isAfter($dueAt));
    }

    public function requiresTesTulis(): bool
    {
        return !str_contains((string) $this->notes, PmbOfflineQueueWorkflowService::NOTES_FLAG_SKIP_TES_TULIS);
    }

    public function testFlowLabel(): string
    {
        return $this->requiresTesTulis() ? 'Ikut Tes Tulis' : 'Bebas Tes Tulis';
    }

    public function isOnlineSelection(): bool
    {
        return $this->selection_mode === self::MODE_ONLINE;
    }

    public function isOfflineSelection(): bool
    {
        return !$this->isOnlineSelection();
    }

    public function selectionModeLabel(): string
    {
        return $this->isOnlineSelection() ? 'Online / Jarak Jauh' : 'Offline / Datang Kampus';
    }

    public function plannedTestFlowItems(): array
    {
        if ($this->isOnlineSelection()) {
            return [
                [
                    'label' => 'Tes Tulis Online',
                    'status' => $this->requiresTesTulis() ? 'Wajib diikuti' : 'Bebas / tidak mengikuti',
                    'tone' => $this->requiresTesTulis() ? 'primary' : 'info',
                ],
                ['label' => 'Anamnesa Kesehatan', 'status' => 'Diisi online', 'tone' => 'primary'],
                ['label' => 'Surat Kesehatan', 'status' => 'Upload dari klinik/RS', 'tone' => 'primary'],
                ['label' => 'Wawancara', 'status' => 'Dijadwalkan panitia', 'tone' => 'primary'],
            ];
        }

        return [
            ['label' => 'Pemberkasan', 'status' => 'Wajib diikuti', 'tone' => 'primary'],
            [
                'label' => 'Tes Tulis',
                'status' => $this->requiresTesTulis() ? 'Wajib diikuti' : 'Bebas / tidak mengikuti',
                'tone' => $this->requiresTesTulis() ? 'primary' : 'info',
            ],
            ['label' => 'Tes Kesehatan', 'status' => 'Wajib diikuti', 'tone' => 'primary'],
            ['label' => 'Wawancara Kaprodi', 'status' => 'Wajib diikuti', 'tone' => 'primary'],
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(PmbTestSession::class, 'session_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(PmbTestRoom::class, 'room_id');
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(PmbQueueStage::class, 'current_stage_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public function checkInAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'checked_in_by_admin_id');
    }

    public function callLogs(): HasMany
    {
        return $this->hasMany(PmbQueueCallLog::class, 'queue_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(PmbQueueStatusLog::class, 'queue_id');
    }

    public function stageSteps(): HasMany
    {
        return $this->hasMany(PmbQueueStageStep::class, 'queue_id');
    }

}
