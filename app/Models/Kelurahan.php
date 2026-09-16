<?php

namespace App\Models;

use App\Models\Kecamatan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kelurahan extends Model
{
     use HasFactory;

    protected $table = 'kelurahan';

    protected $fillable = [
        'nama_kel',
        'id_kec',
    ];

    // Relasi ke Kecamatan
    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class);
    }
}
