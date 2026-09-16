<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pmb_test_sessions', function (Blueprint $table): void {
            $table->timestamp('health_starts_at')->nullable()->after('ends_at');
            $table->timestamp('health_ends_at')->nullable()->after('health_starts_at');
            $table->timestamp('interview_ends_at')->nullable()->after('interview_starts_at');
        });
    }

    public function down(): void
    {
        Schema::table('pmb_test_sessions', function (Blueprint $table): void {
            $table->dropColumn(['health_starts_at', 'health_ends_at', 'interview_ends_at']);
        });
    }
};
