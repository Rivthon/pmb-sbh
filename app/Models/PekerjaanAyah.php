<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PekerjaanAyah extends Model
{

    use HasFactory;
    protected $table = 'pekerjaan_ayah';
    protected $fillable = [
        'nama_pek_ayah',
    ];
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'pek_ayah_id');
    }
}
