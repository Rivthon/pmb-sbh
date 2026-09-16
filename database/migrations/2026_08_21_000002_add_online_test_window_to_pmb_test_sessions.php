<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pmb_test_sessions', function (Blueprint $table): void {
            if (!Schema::hasColumn('pmb_test_sessions', 'online_test_starts_at')) {
                $table->timestamp('online_test_starts_at')->nullable()->after('ends_at');
            }

            if (!Schema::hasColumn('pmb_test_sessions', 'online_test_ends_at')) {
                $table->timestamp('online_test_ends_at')->nullable()->after('online_test_starts_at');
            }
        });

        DB::table('pmb_test_sessions')
            ->whereNotNull('tes_tulis_id')
            ->whereNull('online_test_starts_at')
            ->update([
                'online_test_starts_at' => DB::raw('starts_at'),
                'online_test_ends_at' => DB::raw('ends_at'),
            ]);
    }

    public function down(): void
    {
        Schema::table('pmb_test_sessions', function (Blueprint $table): void {
            $columns = array_values(array_filter([
                Schema::hasColumn('pmb_test_sessions', 'online_test_starts_at') ? 'online_test_starts_at' : null,
                Schema::hasColumn('pmb_test_sessions', 'online_test_ends_at') ? 'online_test_ends_at' : null,
            ]));

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
