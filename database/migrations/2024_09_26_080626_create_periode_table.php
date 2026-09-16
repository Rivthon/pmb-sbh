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
        Schema::create('periodes', function (Blueprint $table) {
            $table->id();
            $table->date('tgl_mulai'); // Tanggal mulai periode
            $table->string('deskripsi')->nullable(); // Deskripsi periode
            $table->enum('status_periode', ['aktif', 'nonaktif'])->default('nonaktif'); // Status periode
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periode');
    }
};
