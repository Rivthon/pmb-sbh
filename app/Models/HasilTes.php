<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilTes extends Model
{
    /** @use HasFactory<\Database\Factories\HasilTesFactory> */
    use HasFactory;

    protected $table = 'hasil_tes';

    protected $fillable = [
        'user_id',
        'tes_tulis_id',
        'skor_total',
        'status',
        'waktu_mulai',
        'waktu_selesai',
        'lulus',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tesTulis()
    {
        return $this->belongsTo(TesTulis::class, 'tes_tulis_id');
    }

    public function jawaban()
    {
        return $this->hasMany(JawabanPeserta::class, 'hasil_tes_id');
    }
}
