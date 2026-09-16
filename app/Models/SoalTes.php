<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoalTes extends Model
{
    /** @use HasFactory<\Database\Factories\SoalTesFactory> */
    use HasFactory;
    protected $table = 'soal_tes';

    protected $fillable = [
        'tes_tulis_id',
        'pertanyaan',
        'cerita_bacaan',
        'kategori_id',
        'skor',
    ];

    public function tesTulis()
    {
        return $this->belongsTo(TesTulis::class, 'tes_tulis_id');
    }

    public function pilihanJawaban()
    {
        return $this->hasMany(PilihanJawaban::class, 'soal_tes_id');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriSoal::class, 'kategori_id');
    }
}
