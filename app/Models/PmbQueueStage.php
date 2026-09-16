<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PmbQueueStage extends Model
{
    use HasFactory;

    public const KEY_PEMBERKASAN = 'pemberkasan';
    public const KEY_TES_TULIS = 'tes_tulis';
    public const KEY_TES_KESEHATAN = 'tes_kesehatan';
    public const KEY_WAWANCARA_KAPRODI = 'wawancara_kaprodi';

    protected $fillable = [
        'session_id',
        'key',
        'name',
        'sort_order',
        'is_required',
        'is_active',
        'default_room_id',
        'metadata',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public static function defaults(): array
    {
        return [
            self::KEY_PEMBERKASAN => ['name' => 'Pemberkasan', 'sort_order' => 10, 'is_required' => true],
            self::KEY_TES_TULIS => ['name' => 'Tes Tulis', 'sort_order' => 20, 'is_required' => true],
            self::KEY_TES_KESEHATAN => ['name' => 'Tes Kesehatan', 'sort_order' => 30, 'is_required' => true],
            self::KEY_WAWANCARA_KAPRODI => ['name' => 'Wawancara Kaprodi', 'sort_order' => 40, 'is_required' => true],
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(PmbTestSession::class, 'session_id');
    }

    public function defaultRoom(): BelongsTo
    {
        return $this->belongsTo(PmbTestRoom::class, 'default_room_id');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(PmbTestRoom::class, 'stage_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(PmbQueueStageStep::class, 'stage_id');
    }
}
