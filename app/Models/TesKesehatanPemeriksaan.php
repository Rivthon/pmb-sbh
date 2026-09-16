<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TesKesehatanPemeriksaan extends Model
{
    use HasFactory;

    protected $table = 'tes_kesehatan_pemeriksaan';

    protected $fillable = [
        'tes_kesehatan_anamnesa_id',
        'nama_pemeriksa',
        'tanggal_pemeriksaan',
        'tinggi_badan',
        'berat_badan',
        'tekanan_darah',
        'konjungtiva_kondisi',
        'konjungtiva_keterangan',
        'ikhterik_kondisi',
        'ikhterik_keterangan',
        'buta_warna_kondisi',
        'buta_warna_keterangan',
        'respon_pendengaran_kondisi',
        'respon_pendengaran_keterangan',
        'kelengkapan_jari_atas_kondisi',
        'kelengkapan_jari_atas_keterangan',
        'tremor_kondisi',
        'tremor_keterangan',
        'bekas_luka_sayatan_kondisi',
        'bekas_luka_sayatan_keterangan',
        'kidal_kondisi',
        'kidal_keterangan',
        'labioschizis_kondisi',
        'labioschizis_keterangan',
        'vokal_kondisi',
        'vokal_keterangan',
        'cat_rambut_kondisi',
        'cat_rambut_keterangan',
        'tyroid_kondisi',
        'tyroid_keterangan',
        'bunyi_irama_jantung_kondisi',
        'bunyi_irama_jantung_keterangan',
        'bunyi_paru_kondisi',
        'bunyi_paru_keterangan',
        'respirasi_kondisi',
        'respirasi_keterangan',
        'simetris_bawah_kondisi',
        'simetris_bawah_keterangan',
        'kelengkapan_jari_bawah_kondisi',
        'kelengkapan_jari_bawah_keterangan',
        'motorik_cara_jalan_kondisi',
        'motorik_cara_jalan_keterangan',
        'bentuk_kaki_kondisi',
        'bentuk_kaki_keterangan',
        'tes_urine_kondisi',
        'tes_urine_keterangan',
        'konjungtiva',
        'ikhterik',
        'buta_warna',
        'respon_pendengaran',
        'kelengkapan_jari_atas',
        'tremor',
        'kidal',
        'bekas_luka_sayatan',
        'bunyi_jantung',
        'bunyi_paru',
        'catatan',
        'rekomendasi',
    ];

    /* ==============================
     * 🔗 RELATIONSHIPS
     * ============================== */
    protected $casts = [
        'tanggal_pengisian' => 'datetime',
    ];

    public static function officialExamSections(bool $includeUrine = true): array
    {
        $sections = [
            'Mata' => [
                'konjungtiva' => 'Konjungtiva',
                'ikhterik' => 'Ikhterik',
                'buta_warna' => 'Buta Warna',
            ],
            'Telinga' => [
                'respon_pendengaran' => 'Respon Pendengaran',
            ],
            'Ekstremitas Atas' => [
                'kelengkapan_jari_atas' => 'Kelengkapan Jari',
                'tremor' => 'Tremor',
                'bekas_luka_sayatan' => 'Bekas Luka Sayatan',
                'kidal' => 'Kidal',
            ],
            'Hidung - Mulut' => [
                'labioschizis' => 'Labioschizis',
                'vokal' => 'Vokal',
            ],
            'Rambut' => [
                'cat_rambut' => 'Cat Rambut',
            ],
            'Tyroid' => [
                'tyroid' => 'Tyroid',
            ],
            'Jantung' => [
                'bunyi_irama_jantung' => 'Bunyi dan Irama Jantung',
            ],
            'Paru' => [
                'bunyi_paru' => 'Bunyi Paru',
                'respirasi' => 'Respirasi',
            ],
            'Ekstremitas Bawah' => [
                'simetris_bawah' => 'Simetris',
                'kelengkapan_jari_bawah' => 'Kelengkapan Jari',
                'motorik_cara_jalan' => 'Motoric / Pergerakan / Cara Jalan',
                'bentuk_kaki' => 'Bentuk Kaki (X/O)',
            ],
        ];

        if ($includeUrine) {
            $sections['Tes Urine (Kebidanan)'] = [
                'tes_urine' => 'Tes Urine',
            ];
        }

        return $sections;
    }

    public function conditionLabel(?string $condition): string
    {
        return match ($condition) {
            'normal' => 'Normal',
            'kelainan' => 'Kelainan',
            default => '-',
        };
    }

    public static function recommendationLabels(): array
    {
        return [
            'layak' => 'Direkomendasikan',
            'tidak layak' => 'Tidak Direkomendasikan',
            'perlu pemeriksaan lanjutan' => 'Pemeriksaan Lebih Lanjut',
        ];
    }

    public function recommendationLabel(): string
    {
        return self::recommendationLabels()[$this->rekomendasi] ?? '-';
    }

    public function healthSummary(): string
    {
        $this->loadMissing('anamnesa.user.jurusan');
        $includeUrine = (bool) $this->anamnesa?->user?->jurusan?->isD3Kebidanan();
        $findings = [];

        foreach (self::officialExamSections($includeUrine) as $items) {
            foreach ($items as $field => $label) {
                if ($this->getAttribute("{$field}_kondisi") !== 'kelainan') {
                    continue;
                }

                $note = trim((string) $this->getAttribute("{$field}_keterangan"));
                $findings[] = $label . ($note !== '' ? ': ' . $note : ': Kelainan');
            }
        }

        if ($findings) {
            return implode('; ', $findings);
        }

        return 'Hasil tes Kesehatan normal semua, diperiksa oleh ' . ($this->nama_pemeriksa ?: '-') . '.';
    }

    public function anamnesa()
    {
        return $this->belongsTo(TesKesehatanAnamnesa::class, 'tes_kesehatan_anamnesa_id');
    }

    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            TesKesehatanAnamnesa::class,
            'id',
            'id',
            'tes_kesehatan_anamnesa_id',
            'user_id'
        );
    }
}
