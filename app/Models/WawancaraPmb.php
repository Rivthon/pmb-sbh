<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WawancaraPmb extends Model
{
    protected $table = 'wawancara_pmb';

    protected $fillable = [
        'calon_mahasiswa_id',
        'jawaban_1',
        'jawaban_2',
        'kelebihan',
        'kekurangan',
        'jawaban_3',
        'jawaban_4',
        'sumber_informasi',
        'jawaban_5',
        'jawaban_6',
        'kesimpulan_kaprodi',
        'rekomendasi',
        'kaprodi_id',
        'tanggal_review',
        'status',
    ];

    protected $casts = [
        'tanggal_review' => 'datetime',
    ];

    /* =========================
       RELATION
    ========================= */
    public function user()
    {
        return $this->belongsTo(User::class, 'calon_mahasiswa_id');
    }

    public function pewawancara()
    {
        return $this->belongsTo(Admin::class, 'kaprodi_id');
    }
    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    /* =========================
       HELPER STATUS
    ========================= */
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isSubmitted()
    {
        return $this->status === 'submitted';
    }

    public function isReviewed()
    {
        return in_array($this->status, ['reviewed', 'locked']);
    }
}
