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
        Schema::create('kabupaten', function (Blueprint $table) {
            $table->bigIncrements('id_kab'); // Primary key dengan auto-increment
            $table->unsignedBigInteger('id_prov'); // Foreign key ke tabel provinsi
            $table->string('nama_kab');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('id_prov')->references('id_prov')->on('provinsi')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kabupaten');
    }
};
