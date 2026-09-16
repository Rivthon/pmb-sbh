<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $items = [
        'konjungtiva',
        'ikhterik',
        'buta_warna',
        'respon_pendengaran',
        'kelengkapan_jari_atas',
        'tremor',
        'bekas_luka_sayatan',
        'kidal',
        'labioschizis',
        'vokal',
        'cat_rambut',
        'tyroid',
        'bunyi_irama_jantung',
        'bunyi_paru',
        'respirasi',
        'simetris_bawah',
        'kelengkapan_jari_bawah',
        'motorik_cara_jalan',
        'bentuk_kaki',
        'tes_urine',
    ];

    public function up(): void
    {
        Schema::table('tes_kesehatan_pemeriksaan', function (Blueprint $table): void {
            foreach ($this->items as $item) {
                $table->string("{$item}_kondisi", 20)->nullable()->after('tekanan_darah');
                $table->string("{$item}_keterangan")->nullable()->after("{$item}_kondisi");
            }
        });
    }

    public function down(): void
    {
        Schema::table('tes_kesehatan_pemeriksaan', function (Blueprint $table): void {
            $columns = [];

            foreach ($this->items as $item) {
                $columns[] = "{$item}_kondisi";
                $columns[] = "{$item}_keterangan";
            }

            $table->dropColumn($columns);
        });
    }
};
