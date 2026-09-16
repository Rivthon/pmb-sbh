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
        // Menambahkan foreign key untuk periode, gelombang, jurusan, dan kuesioner
        $table->unsignedBigInteger('periode_id')->nullable();
        $table->unsignedBigInteger('gelombang_id')->nullable();
        $table->unsignedBigInteger('jurusan_id')->nullable();
        $table->unsignedBigInteger('kuesioner_id')->nullable();

        // Relasi foreign key
        $table->foreign('periode_id')->references('id')->on('periodes')->onDelete('set null');
        $table->foreign('gelombang_id')->references('id')->on('gelombangs')->onDelete('set null');
        $table->foreign('jurusan_id')->references('id')->on('jurusan')->onDelete('set null');
        $table->foreign('kuesioner_id')->references('id')->on('kuesioners')->onDelete('set null');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['periode_id']);
        $table->dropForeign(['gelombang_id']);
        $table->dropForeign(['jurusan_id']);
        $table->dropForeign(['kuesioner_id']);

        $table->dropColumn(['periode_id', 'gelombang_id', 'jurusan_id', 'kuesioner_id']);
    });
    }
};
