<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wawancara_pmb') && !Schema::hasColumn('wawancara_pmb', 'jawaban_6')) {
            Schema::table('wawancara_pmb', function (Blueprint $table) {
                $table->text('jawaban_6')->nullable()->after('jawaban_5');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('wawancara_pmb') && Schema::hasColumn('wawancara_pmb', 'jawaban_6')) {
            Schema::table('wawancara_pmb', function (Blueprint $table) {
                $table->dropColumn('jawaban_6');
            });
        }
    }
};
