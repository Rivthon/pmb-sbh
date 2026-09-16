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
        Schema::table('users', function (Blueprint $table) {
        // Menambahkan foreign key untuk alamat (provinsi, kabupaten, kecamatan, kelurahan)
        $table->unsignedBigInteger('provinsi_id')->nullable();
        $table->unsignedBigInteger('kabupaten_id')->nullable();
        $table->unsignedBigInteger('kecamatan_id')->nullable();
        $table->unsignedBigInteger('kelurahan_id')->nullable();

        // Relasi foreign key
        $table->foreign('provinsi_id')->references('id_prov')->on('provinsi')->onDelete('set null');
        $table->foreign('kecamatan_id')->references('id_kec')->on('kecamatan')->onDelete('set null');
        $table->foreign('kelurahan_id')->references('id_kel')->on('kelurahan')->onDelete('set null');
          $table->foreign('kabupaten_id')->references('id_kab')->on('kabupaten')->onDelete('set null');

    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['provinsi_id']);
        $table->dropForeign(['kabupaten_id']);
        $table->dropForeign(['kecamatan_id']);
        $table->dropForeign(['kelurahan_id']);
        $table->dropColumn(['provinsi_id', 'kabupaten_id', 'kecamatan_id', 'kelurahan_id']);
    });
    }
};