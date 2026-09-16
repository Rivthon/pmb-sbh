<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PilihanJawaban extends Model
{
    /** @use HasFactory<\Database\Factories\PilihanJawabanFactory> */
    use HasFactory;

    protected $table = 'pilihan_jawaban';

    protected $fillable = [
        'soal_tes_id',
        'teks_pilihan',
        'kode_pilihan',
        'benar',
        'urutan',
    ];

    public function soalTes()
    {
        return $this->belongsTo(SoalTes::class, 'soal_tes_id');
    }
}
