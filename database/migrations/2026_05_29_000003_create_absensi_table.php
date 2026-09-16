<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pertemuan_id');
            $table->unsignedBigInteger('jadwal_id');
            $table->unsignedBigInteger('mahasiswa_id');
            $table->enum('status', ['hadir', 'tidak hadir', 'izin'])->default('hadir');
            $table->string('keterangan')->nullable();
            $table->timestamp('tanggal')->nullable();
            $table->timestamps();

            $table->index(['pertemuan_id', 'jadwal_id', 'mahasiswa_id'], 'absensi_pertemuan_jadwal_mhs_idx');
            $table->index(['mahasiswa_id', 'tanggal'], 'absensi_mhs_tanggal_idx');
        });

        Schema::table('absensi', function (Blueprint $table) {
            if (Schema::hasTable('pertemuan')) {
                $table->foreign('pertemuan_id')
                    ->references('pertemuan_id')
                    ->on('pertemuan')
                    ->cascadeOnDelete();
            }

            if (Schema::hasTable('jadwal')) {
                $table->foreign('jadwal_id')
                    ->references('jadwal_id')
                    ->on('jadwal')
                    ->cascadeOnDelete();
            }

            if (Schema::hasTable('mahasiswa')) {
                $table->foreign('mahasiswa_id')
                    ->references('mahasiswa_id')
                    ->on('mahasiswa')
                    ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
