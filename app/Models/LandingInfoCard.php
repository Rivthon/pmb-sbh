<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingInfoCard extends Model
{
    use HasFactory;

    public const SECTION_ADVANTAGES = 'advantages';
    public const SECTION_COST_INFO = 'cost_info';

    protected $fillable = [
        'section', 'title', 'description', 'items', 'icon', 'color', 'variant',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'items' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public static function availableIcons(): array
    {
        return [
            'bx bxs-network-chart' => 'Jaringan',
            'bx bxs-badge-check' => 'Lencana Terverifikasi',
            'bx bxs-school' => 'Gedung Kampus',
            'bx bxs-user-detail' => 'Dosen / Pengajar',
            'bx bxs-briefcase' => 'Tas Kerja',
            'bx bxs-book-content' => 'Buku / Kurikulum',
            'bx bx-calendar' => 'Kalender',
            'bx bx-check-shield' => 'Perisai Terverifikasi',
            'bx bx-info-circle' => 'Informasi',
            'bx bxs-graduation' => 'Topi Wisuda',
            'bx bx-laptop' => 'Laptop',
            'bx bxs-wallet-alt' => 'Dompet',
            'bx bxs-credit-card' => 'Kartu Pembayaran',
            'bx bxs-building-house' => 'Gedung',
            'bx bxs-flask' => 'Laboratorium',
            'bx bxs-heart' => 'Kesehatan',
        ];
    }

    public function getColorHexAttribute(): string
    {
        return match ($this->color) {
            'warning' => '#f59e0b',
            'success' => '#22c55e',
            'info' => '#06b6d4',
            'danger' => '#ef4444',
            'primary' => '#696cff',
            'dark' => '#334155',
            default => '#64748b',
        };
    }

    public function getSoftColorAttribute(): string
    {
        return match ($this->color) {
            'warning' => 'rgba(245, 158, 11, 0.12)',
            'success' => 'rgba(34, 197, 94, 0.12)',
            'info' => 'rgba(6, 182, 212, 0.12)',
            'danger' => 'rgba(239, 68, 68, 0.12)',
            'primary' => 'rgba(105, 108, 255, 0.12)',
            'dark' => 'rgba(51, 65, 85, 0.12)',
            default => 'rgba(100, 116, 139, 0.12)',
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function getIconClassAttribute(): string
    {
        return self::normalizeIcon($this->icon);
    }

    public static function normalizeIcon(?string $icon): string
    {
        $icon = strtolower(trim(preg_replace('/\s+/', ' ', (string) $icon)));

        if (preg_match('/^bx (bx[srl]?-[a-z0-9-]+)$/', $icon, $matches)) {
            return 'bx ' . $matches[1];
        }

        if (preg_match('/^bx[srl]?-[a-z0-9-]+$/', $icon)) {
            return 'bx ' . $icon;
        }

        if (preg_match('/^(bx[srl]?) bx-([a-z0-9-]+)$/', $icon, $matches)) {
            return 'bx ' . $matches[1] . '-' . $matches[2];
        }

        return 'bx bx-image-alt';
    }
}
