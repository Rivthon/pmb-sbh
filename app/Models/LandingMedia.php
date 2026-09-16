<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LandingMedia extends Model
{
    use HasFactory;

    public const HERO_IMAGE = 'hero_image';
    public const AUTH_IMAGE = 'auth_image';
    public const DEFAULT_IMAGE = 'dashboard_assets/assets/img/front-pages/backgrounds/foto_utama.png';

    protected $fillable = ['key', 'label', 'path'];

    public function getUrlAttribute(): string
    {
        $url = str_starts_with($this->path, 'landing-media/')
            ? $this->uploadedFileUrl()
            : asset($this->path);

        return $url . '?v=' . ($this->updated_at?->timestamp ?? 1);
    }

    public function getFileNameAttribute(): string
    {
        return basename($this->path);
    }

    public function getFileSizeLabelAttribute(): string
    {
        $file = $this->absolutePath();

        if (!$file || !is_file($file)) {
            return 'File tidak ditemukan';
        }

        $bytes = filesize($file);

        return $bytes >= 1048576
            ? number_format($bytes / 1048576, 2, ',', '.') . ' MB'
            : number_format($bytes / 1024, 2, ',', '.') . ' KB';
    }

    public function getDimensionsAttribute(): string
    {
        $file = $this->absolutePath();
        $size = $file && is_file($file) ? @getimagesize($file) : false;

        return $size ? $size[0] . ' × ' . $size[1] . ' px' : '-';
    }

    public function getFileFormatAttribute(): string
    {
        return strtoupper(pathinfo($this->path, PATHINFO_EXTENSION) ?: '-');
    }

    public function getIsCustomAttribute(): bool
    {
        return str_starts_with($this->path, 'landing-media/');
    }

    private function absolutePath(): ?string
    {
        if (!$this->is_custom) {
            return public_path($this->path);
        }

        return Storage::disk('local')->exists($this->path)
            ? Storage::disk('local')->path($this->path)
            : Storage::disk('public')->path($this->path);
    }

    private function uploadedFileUrl(): string
    {
        if (Storage::disk('local')->exists($this->path)) {
            return route('landing-media.file', ['filename' => basename($this->path)], false);
        }

        // Kompatibilitas untuk gambar yang diunggah sebelum lokasi penyimpanan dipindahkan.
        return asset('storage/' . ltrim($this->path, '/'));
    }
}
