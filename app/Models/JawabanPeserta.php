<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JawabanPeserta extends Model
{
    /** @use HasFactory<\Database\Factories\JawabanPesertaFactory> */
    use HasFactory;
    protected $table = 'jawaban_peserta';

    protected $fillable = [
        'hasil_tes_id',
        'soal_tes_id',
        'pilihan_jawaban_id',
        'benar',
    ];

    public function hasilTes()
    {
        return $this->belongsTo(HasilTes::class, 'hasil_tes_id');
    }

    public function soalTes()
    {
        return $this->belongsTo(SoalTes::class, 'soal_tes_id');
    }

    public function pilihanJawaban()
    {
        return $this->belongsTo(PilihanJawaban::class, 'pilihan_jawaban_id');
    }
}
