<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kelurahan', function (Blueprint $table) {
            $table->bigIncrements('id_kel'); // Primary key dengan auto-increment
            $table->unsignedBigInteger('id_kec'); // Foreign key ke tabel kecamatan
            $table->string('nama_kel'); // Ubah penamaan dari 'nama' menjadi 'nama_kel' agar lebih deskriptif
            $table->unsignedInteger('id_jenis'); // Kolom untuk tipe kelurahan (opsional)
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('id_kec')->references('id_kec')->on('kecamatan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelurahan');
    }
};
