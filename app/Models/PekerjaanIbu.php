<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PekerjaanIbu extends Model
{
    use HasFactory;
    protected $table = 'pekerjaan_ibu';
    protected $fillable = [
        'nama_pek_ibu',
    ];
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'pek_ibu_id');
    }
}
