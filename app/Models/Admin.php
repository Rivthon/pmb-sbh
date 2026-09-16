<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $guard_name = 'admin'; // penting!

    const FOLDER_NAME = 'admin';

    protected $fillable = [
        'jurusan_id',
        'name',
        'email',
        'password',
        'image',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function getProfileImageURL(): string
    {
        return asset(
            'storage/' . self::FOLDER_NAME . '/' . ($this->image ?? 'default.png')
        );
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }
}
