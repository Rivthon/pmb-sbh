<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TesWawancara extends Model
{
    use HasFactory;

    protected $table = 'tes_wawancara';

    protected $fillable = [
        'user_id',
        'pewawancara_id',
        'tanggal_wawancara',
        'pertanyaan_1',
        'pertanyaan_2_kelebihan',
        'pertanyaan_2_kekurangan',
        'pertanyaan_3',
        'pertanyaan_4',
        'sumber_informasi',
        'pertanyaan_5',
        'kesimpulan_prodi',
        'hasil',
    ];

    /* ==============================
     * 🔗 RELATIONSHIPS
     * ============================== */

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pewawancara()
    {
        return $this->belongsTo(Admin::class, 'pewawancara_id');
    }
}
