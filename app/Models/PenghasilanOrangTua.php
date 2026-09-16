<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenghasilanOrangTua extends Model
{
    use HasFactory;
    protected $table = 'penghasilan_orang_tua';
    protected $fillable = [
        'nama_peng',
    ];
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'penghasilan_id');
    }
}
