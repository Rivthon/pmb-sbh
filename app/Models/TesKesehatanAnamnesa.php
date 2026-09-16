<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TesKesehatanAnamnesa extends Model
{
    use HasFactory;

    protected $table = 'tes_kesehatan_anamnesa';

    protected $fillable = [
        'user_id',
        'admin_id',
        'tanggal_pengisian',

        // 1 - 5
        'riwayat_penyakit_keluarga',
        'riwayat_penyakit_pribadi',
        'riwayat_operasi',
        'konsumsi_obat_rutin',
        'penyakit_menular',

        // 6 - 10
        'masalah_kulit',
        'tumor_benjolan',          // ⬅️ tambahan
        'epilepsi',
        'cedera_kepala',
        'batuk_kronis',

        // 11 - 15
        'gangguan_pencernaan',
        'gangguan_keseimbangan',
        'claustrophobia',
        'takut_darah',
        'kacamata',

        // 16 - 21
        'gagap',                   // ⬅️ tambahan
        'alat_bantu_tulang',
        'lemah_otot',
        'pikiran_bunuh_diri',
        'kelainan_darah',          // ⬅️ tambahan
        'riwayat_psikolog',

        'riwayat_penyakit_keluarga_keterangan',
        'riwayat_penyakit_pribadi_keterangan',
        'riwayat_operasi_keterangan',
        'konsumsi_obat_rutin_keterangan',
        'penyakit_menular_keterangan',
        'masalah_kulit_keterangan',
        'tumor_benjolan_keterangan',
        'epilepsi_keterangan',
        'cedera_kepala_keterangan',
        'batuk_kronis_keterangan',
        'gangguan_pencernaan_keterangan',
        'gangguan_keseimbangan_keterangan',
        'claustrophobia_keterangan',
        'takut_darah_keterangan',
        'kacamata_keterangan',
        'gagap_keterangan',
        'alat_bantu_tulang_keterangan',
        'lemah_otot_keterangan',
        'pikiran_bunuh_diri_keterangan',
        'kelainan_darah_keterangan',
        'riwayat_psikolog_keterangan',

        // tambahan umum
        'keterangan',
        'surat_kesehatan_path',
        'surat_kesehatan_uploaded_at',
        'status',
    ];

    protected $casts = [
        'tanggal_pengisian' => 'datetime',
        'surat_kesehatan_uploaded_at' => 'datetime',
    ];

    public static function officerAnamnesaQuestions(): array
    {
        return [
            ['field' => 'riwayat_penyakit_keluarga', 'label' => 'Riwayat Penyakit Keluarga (Jantung, TBC, DM, Kanker, Hipertensi, Asma, HIV/AIDS)'],
            ['field' => 'riwayat_penyakit_pribadi', 'label' => 'Riwayat Penyakit yang pernah di derita'],
            ['field' => 'riwayat_operasi', 'label' => 'Riwayat Operasi'],
            ['field' => 'konsumsi_obat_rutin', 'label' => 'Adakah obat yang dikonsumsi secara rutin'],
            ['field' => 'penyakit_menular', 'label' => 'Penyakit Menular Seksual (HIV/AIDS, Sifilis, Hep.B, Gonorhea)'],
            ['field' => 'masalah_kulit', 'label' => 'Masalah Kulit (Eksim, Jamur, herpes, Kurap/Kudis)'],
            ['field' => 'tumor_benjolan', 'label' => 'Tumor, Benjolan abnormal, Kista, Kanker'],
            ['field' => 'epilepsi', 'label' => 'Epilepsi (Ayan), Kejang-kejang'],
            ['field' => 'cedera_kepala', 'label' => 'Luka pada kepala / gegar otak'],
            ['field' => 'batuk_kronis', 'label' => 'Batuk Kronis (lebih dari 2 minggu)'],
            ['field' => 'gangguan_pencernaan', 'label' => 'Masalah pencernaan: Maag / GERD'],
            ['field' => 'gangguan_keseimbangan', 'label' => 'Gangguan keseimbangan/ koordinasi'],
            ['field' => 'claustrophobia', 'label' => 'Takut pada ruang sempit/ tertutup'],
            ['field' => 'takut_darah', 'label' => 'Takut melihat darah'],
            ['field' => 'kacamata', 'label' => 'Menggunakan kacamata'],
            ['field' => 'gagap', 'label' => 'Gagap'],
            ['field' => 'alat_bantu_tulang', 'label' => 'Menggunakan penyangga tulang / sendi / pin / splint'],
            ['field' => 'lemah_otot', 'label' => 'Lemah kekuatan otot / gangguan saraf'],
            ['field' => 'pikiran_bunuh_diri', 'label' => 'Percobaan atau pemikiran untuk bunuh diri'],
            ['field' => 'kelainan_darah', 'label' => 'Kelainan darah (perdarahan tanpa sebab, hemophilia, thalassemia'],
            ['field' => 'riwayat_psikolog', 'label' => 'Mengunjungi psikolog atau psikiater'],
        ];
    }


    /* ==============================
     * 🔗 RELATIONSHIPS
     * ============================== */

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function pemeriksaan()
    {
        return $this->hasOne(TesKesehatanPemeriksaan::class);
    }
}
