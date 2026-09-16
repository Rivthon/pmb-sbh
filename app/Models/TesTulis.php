<?php

namespace App\Models;

use App\Models\SoalTes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TesTulis extends Model
{
    /** @use HasFactory<\Database\Factories\TesTulisFactory> */
    use HasFactory;

    protected $table = 'tes_tulis';

    protected $fillable = [
        'nama_tes',
        'deskripsi',
        'durasi_menit',
        'skor_lulus',
        'acak_soal',
        'acak_pilihan',
        'status_aktif',
    ];

    public function soal()
    {
        return $this->hasMany(SoalTes::class);
    }

    public function hasil()
    {
        return $this->hasMany(HasilTes::class, 'tes_tulis_id');
    }
    public function hasilTesTulis()
    {
        return $this->hasMany(HasilTesTulis::class, 'user_id');
    }
    public function hasilTesTulisUser($userId)
    {
        return $this->hasMany(HasilTesTulis::class, 'tes_tulis_id')
            ->where('user_id', $userId)
            ->first();
    }
    public function hasilTesUser()
    {
        return $this->hasOne(HasilTesTulis::class)->where('user_id', auth()->id());
    }
}
