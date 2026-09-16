<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TesTulisHasilKategori extends Model
{
    /** @use HasFactory<\Database\Factories\TesTulisHasilKategoriFactory> */
    use HasFactory;
    protected $table = 'tes_tulis_hasil_kategori';
    protected $fillable = [
        'hasil_tes_id',
        'kategori_id',
        'jumlah_soal',
        'jawaban_benar',
        'jawaban_salah',
        'skor',
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriSoal::class);
    }

    public function hasilTes()
    {
        return $this->belongsTo(HasilTesTulis::class, 'hasil_tes_id');
    }
}
