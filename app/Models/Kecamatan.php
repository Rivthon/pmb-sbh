<?php

namespace App\Models;

use App\Models\Kabupaten;
use App\Models\Kelurahan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kecamatan extends Model
{
    use HasFactory;

    protected $table = 'kecamatan';

    protected $fillable = [
        'nama_kec',
        'id_kab',
    ];

    // Relasi ke Kabupaten
    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class);
    }

    // Relasi ke Kelurahan
    public function kelurahan()
    {
        return $this->hasMany(Kelurahan::class);
    }
}
