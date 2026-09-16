<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pmb_queue_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('pmb_test_sessions')->cascadeOnDelete();
            $table->string('key', 60);
            $table->string('name', 120);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->foreignId('default_room_id')->nullable()->constrained('pmb_test_rooms')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['session_id', 'key'], 'pmb_queue_stages_session_key_unique');
            $table->index(['session_id', 'is_active', 'sort_order'], 'pmb_queue_stages_session_active_idx');
        });

        Schema::table('pmb_test_rooms', function (Blueprint $table) {
            $table->foreignId('stage_id')
                ->nullable()
                ->after('session_id')
                ->constrained('pmb_queue_stages')
                ->nullOnDelete();

            $table->index(['session_id', 'stage_id', 'status'], 'pmb_rooms_session_stage_status_idx');
        });

        Schema::table('pmb_offline_queues', function (Blueprint $table) {
            $table->string('arrival_status', 30)->default('not_checked_in')->after('status');
            $table->string('overall_status', 30)->default('not_started')->after('arrival_status');
            $table->foreignId('current_stage_id')
                ->nullable()
                ->after('overall_status')
                ->constrained('pmb_queue_stages')
                ->nullOnDelete();

            $table->index(['session_id', 'arrival_status'], 'pmb_queues_session_arrival_idx');
            $table->index(['session_id', 'overall_status'], 'pmb_queues_session_overall_idx');
            $table->index(['current_stage_id', 'overall_status'], 'pmb_queues_current_stage_idx');
        });

        Schema::create('pmb_queue_stage_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_id')->constrained('pmb_offline_queues')->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained('pmb_queue_stages')->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('pmb_test_rooms')->nullOnDelete();
            $table->foreignId('assigned_officer_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->unsignedInteger('stage_queue_number')->nullable();
            $table->string('stage_queue_code', 40)->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('held_at')->nullable();
            $table->timestamp('skipped_at')->nullable();
            $table->unsignedSmallInteger('call_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['queue_id', 'stage_id'], 'pmb_stage_steps_queue_stage_unique');
            $table->unique(['stage_id', 'stage_queue_code'], 'pmb_stage_steps_stage_code_unique');
            $table->index(['stage_id', 'status', 'queued_at'], 'pmb_stage_steps_stage_status_idx');
            $table->index(['queue_id', 'status'], 'pmb_stage_steps_queue_status_idx');
        });

        Schema::create('pmb_queue_stage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('step_id')->nullable()->constrained('pmb_queue_stage_steps')->cascadeOnDelete();
            $table->foreignId('queue_id')->constrained('pmb_offline_queues')->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained('pmb_queue_stages')->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('action', 40);
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['stage_id', 'action', 'created_at'], 'pmb_stage_logs_stage_action_idx');
            $table->index(['queue_id', 'created_at'], 'pmb_stage_logs_queue_idx');
        });

        Schema::create('pmb_queue_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('pmb_test_sessions')->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained('pmb_queue_stages')->cascadeOnDelete();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['session_id', 'stage_id'], 'pmb_queue_counters_session_stage_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pmb_queue_counters');
        Schema::dropIfExists('pmb_queue_stage_logs');
        Schema::dropIfExists('pmb_queue_stage_steps');

        Schema::table('pmb_offline_queues', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_stage_id');
            $table->dropIndex('pmb_queues_session_arrival_idx');
            $table->dropIndex('pmb_queues_session_overall_idx');
            $table->dropIndex('pmb_queues_current_stage_idx');
            $table->dropColumn(['arrival_status', 'overall_status']);
        });

        Schema::table('pmb_test_rooms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stage_id');
            $table->dropIndex('pmb_rooms_session_stage_status_idx');
        });

        Schema::dropIfExists('pmb_queue_stages');
    }
};
