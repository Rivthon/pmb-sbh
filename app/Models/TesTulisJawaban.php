<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TesTulisJawaban extends Model
{
    /** @use HasFactory<\Database\Factories\TesTulisJawabanFactory> */
    use HasFactory;
    protected $table = 'tes_tulis_jawaban';

    protected $fillable = [
        'hasil_tes_id',
        'soal_id',
        'pilihan_jawaban_id',
        'benar',
    ];

    public function hasil()
    {
        return $this->belongsTo(HasilTesTulis::class, 'hasil_tes_id');
    }
    public function hasilTes()
    {
        return $this->belongsTo(HasilTesTulis::class, 'hasil_tes_id');
    }
    public function soal()
    {
        return $this->belongsTo(SoalTes::class, 'soal_id');
    }

    public function pilihan()
    {
        return $this->belongsTo(PilihanJawaban::class, 'pilihan_jawaban_id');
    }
    public function pilihanJawaban()
    {
        return $this->belongsTo(PilihanJawaban::class, 'pilihan_jawaban_id');
    }
}
