<?php

namespace App\Models;

use App\Models\Provinsi;
use App\Models\Kecamatan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kabupaten extends Model
{
    use HasFactory;

    protected $table = 'kabupaten';

    protected $fillable = [
        'nama_kab',
        'id_prov',
    ];

    // Relasi ke Provinsi
    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class);
    }

    // Relasi ke Kecamatan
    public function kecamatan()
    {
        return $this->hasMany(Kecamatan::class);
    }
}