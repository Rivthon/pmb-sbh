<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $fields = [
        'riwayat_penyakit_keluarga',
        'riwayat_penyakit_pribadi',
        'riwayat_operasi',
        'konsumsi_obat_rutin',
        'penyakit_menular',
        'masalah_kulit',
        'tumor_benjolan',
        'epilepsi',
        'cedera_kepala',
        'batuk_kronis',
        'gangguan_pencernaan',
        'gangguan_keseimbangan',
        'claustrophobia',
        'takut_darah',
        'kacamata',
        'gagap',
        'alat_bantu_tulang',
        'lemah_otot',
        'pikiran_bunuh_diri',
        'kelainan_darah',
        'riwayat_psikolog',
    ];

    public function up(): void
    {
        Schema::table('tes_kesehatan_anamnesa', function (Blueprint $table) {
            foreach ($this->fields as $field) {
                if (!Schema::hasColumn('tes_kesehatan_anamnesa', "{$field}_keterangan")) {
                    $table->string("{$field}_keterangan")->nullable()->after($field);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('tes_kesehatan_anamnesa', function (Blueprint $table) {
            $table->dropColumn(array_map(fn (string $field): string => "{$field}_keterangan", $this->fields));
        });
    }
};
