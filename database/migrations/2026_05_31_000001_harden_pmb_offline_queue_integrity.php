<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pmb_offline_queues', function (Blueprint $table) {
            $table->unique(['session_id', 'queue_number'], 'pmb_queues_session_number_unique');
        });

        Schema::create('pmb_queue_arrival_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('pmb_test_sessions')->cascadeOnDelete();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique('session_id', 'pmb_arrival_counters_session_unique');
        });

        DB::table('pmb_offline_queues')
            ->select('session_id', DB::raw('MAX(queue_number) as last_number'))
            ->whereNotNull('queue_number')
            ->groupBy('session_id')
            ->orderBy('session_id')
            ->chunk(100, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('pmb_queue_arrival_counters')->updateOrInsert(
                        ['session_id' => $row->session_id],
                        [
                            'last_number' => (int) $row->last_number,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('pmb_queue_arrival_counters');

        Schema::table('pmb_offline_queues', function (Blueprint $table) {
            $table->dropUnique('pmb_queues_session_number_unique');
        });
    }
};
