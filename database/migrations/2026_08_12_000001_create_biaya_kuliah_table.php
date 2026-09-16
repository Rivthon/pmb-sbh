<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biaya_kuliah', function (Blueprint $table) {
            $table->id();
            $table->string('prodi_key', 50);
            $table->string('prodi_nama');
            $table->unsignedTinyInteger('jumlah_semester');
            $table->unsignedTinyInteger('gelombang');
            $table->unsignedTinyInteger('semester');
            $table->unsignedBigInteger('biaya');
            $table->timestamps();

            $table->unique(['prodi_key', 'gelombang', 'semester'], 'biaya_kuliah_unique_period');
            $table->index('prodi_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biaya_kuliah');
    }
};
