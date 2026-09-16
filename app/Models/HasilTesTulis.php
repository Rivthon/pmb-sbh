<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HasilTesTulis extends Model
{
    use HasFactory;

    protected $table = 'hasil_tes_tulis';

    protected $fillable = [
        'user_id',
        'tes_tulis_id',
        'skor',
        'jawaban_benar',
        'jawaban_salah',
        'selesai',
        'status',
        'waktu_mulai',
        'waktu_selesai',
    ];

    protected $casts = [
        'selesai' => 'boolean',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    // ==============================
    // 🔗 RELASI
    // ==============================
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tesTulis()
    {
        return $this->belongsTo(TesTulis::class, 'tes_tulis_id');
    }

    // ==============================
    // 🧠 LOGIKA TAMBAHAN
    // ==============================

    /**
     * Hitung waktu selesai otomatis berdasarkan durasi tes.
     * Hanya dipakai jika waktu_selesai belum diset.
     */
    public function setWaktuSelesaiOtomatis()
    {
        if ($this->tesTulis && $this->tesTulis->durasi && !$this->waktu_selesai) {
            $this->waktu_selesai = $this->waktu_mulai->copy()->addMinutes($this->tesTulis->durasi);
            $this->save();
        }
    }

    /**
     * Cek apakah waktu tes sudah habis.
     */
    public function isWaktuHabis()
    {
        if (!$this->waktu_selesai) return false;
        return now()->greaterThan($this->waktu_selesai);
    }

    /**
     * Dapatkan sisa waktu dalam detik.
     */
    public function getSisaWaktuDetik()
    {
        if (!$this->waktu_selesai) return null;
        $sisa = now()->diffInSeconds($this->waktu_selesai, false);
        return max($sisa, 0);
    }
    public function kategoriHasil()
    {
        return $this->hasMany(TesTulisHasilKategori::class, 'hasil_tes_id');
    }
    public function tes()
    {
        return $this->belongsTo(TesTulis::class, 'tes_tulis_id');
    }
    public function jawaban()
    {
        return $this->hasMany(TesTulisJawaban::class, 'hasil_tes_id');
    }
}
