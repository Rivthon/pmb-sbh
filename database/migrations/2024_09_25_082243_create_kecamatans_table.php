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
        Schema::create('kecamatan', function (Blueprint $table) {
            $table->bigIncrements('id_kec'); // Primary key dengan auto-increment
            $table->unsignedBigInteger('id_kab'); // Foreign key ke tabel kabupaten
            $table->string('nama_kec'); // Ubah penamaan dari 'nama_' menjadi 'nama_kec' agar lebih deskriptif
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('id_kab')->references('id_kab')->on('kabupaten')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kecamatan');
    }
};
