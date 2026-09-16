<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('periodes', 'linked')) {
            DB::statement('ALTER TABLE periodes MODIFY linked VARCHAR(255) NULL');
        }

        if (Schema::hasColumn('periodes', 'file_lulus')) {
            DB::statement('ALTER TABLE periodes MODIFY file_lulus VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('periodes', 'linked')) {
            DB::table('periodes')->whereNull('linked')->update(['linked' => '']);
            DB::statement("ALTER TABLE periodes MODIFY linked VARCHAR(255) NOT NULL DEFAULT ''");
        }

        if (Schema::hasColumn('periodes', 'file_lulus')) {
            DB::table('periodes')->whereNull('file_lulus')->update(['file_lulus' => '']);
            DB::statement("ALTER TABLE periodes MODIFY file_lulus VARCHAR(255) NOT NULL DEFAULT ''");
        }
    }
};
