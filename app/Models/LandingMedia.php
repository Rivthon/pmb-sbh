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
        return str_starts_with($this->path, 'landing-media/')
            ? Storage::disk('public')->url($this->path)
            : asset($this->path);
    }
}
