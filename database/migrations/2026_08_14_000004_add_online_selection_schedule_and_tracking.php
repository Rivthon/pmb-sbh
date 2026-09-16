<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pmb_test_sessions', function (Blueprint $table): void {
            $table->timestamp('interview_starts_at')->nullable()->after('ends_at');
            $table->text('zoom_url')->nullable()->after('interview_starts_at');
        });

        Schema::table('pmb_offline_queues', function (Blueprint $table): void {
            $table->timestamp('health_notified_at')->nullable()->after('selection_mode');
        });
    }

    public function down(): void
    {
        Schema::table('pmb_offline_queues', function (Blueprint $table): void {
            $table->dropColumn('health_notified_at');
        });

        Schema::table('pmb_test_sessions', function (Blueprint $table): void {
            $table->dropColumn(['interview_starts_at', 'zoom_url']);
        });
    }
};
