<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soal_tes', function (Blueprint $table) {
            if (!Schema::hasColumn('soal_tes', 'cerita_bacaan')) {
                $table->longText('cerita_bacaan')->nullable()->after('pertanyaan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('soal_tes', function (Blueprint $table) {
            if (Schema::hasColumn('soal_tes', 'cerita_bacaan')) {
                $table->dropColumn('cerita_bacaan');
            }
        });
    }
};
