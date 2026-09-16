<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kategori_soal', function (Blueprint $table) {
            $table->longText('cerita_bacaan')->nullable()->after('nama_kategori');
        });
    }

    public function down(): void
    {
        Schema::table('kategori_soal', function (Blueprint $table) {
            $table->dropColumn('cerita_bacaan');
        });
    }
};
