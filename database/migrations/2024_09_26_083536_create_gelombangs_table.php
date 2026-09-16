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
        Schema::create('gelombangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_gelombang'); // Nama gelombang
            $table->date('tgl_mulai'); // Tanggal mulai
            $table->date('tgl_selesai'); // Tanggal selesai
            $table->enum('status_gelombang', ['aktif', 'nonaktif'])->default('nonaktif'); // Status gelombang
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gelombangs');
    }
};
