<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pmb_test_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code', 40)->unique();
            $table->string('name');
            $table->string('type', 30)->default('campuran');
            $table->foreignId('periode_id')->nullable()->constrained('periodes')->nullOnDelete();
            $table->foreignId('gelombang_id')->nullable()->constrained('gelombangs')->nullOnDelete();
            $table->foreignId('tes_tulis_id')->nullable()->constrained('tes_tulis')->nullOnDelete();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('checkin_open_at')->nullable();
            $table->timestamp('checkin_close_at')->nullable();
            $table->string('status', 30)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'starts_at'], 'pmb_sessions_status_starts_idx');
            $table->index(['periode_id', 'gelombang_id'], 'pmb_sessions_period_wave_idx');
        });

        Schema::create('pmb_test_rooms', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('session_id')->constrained('pmb_test_sessions')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 30);
            $table->unsignedSmallInteger('capacity')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('status', 30)->default('available');
            $table->timestamps();

            $table->unique(['session_id', 'code'], 'pmb_rooms_session_code_unique');
            $table->index(['session_id', 'status'], 'pmb_rooms_session_status_idx');
        });

        Schema::create('pmb_offline_queues', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('session_id')->constrained('pmb_test_sessions')->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('pmb_test_rooms')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('queue_number')->nullable();
            $table->string('queue_code', 40)->nullable();
            $table->string('qr_token_hash', 128)->nullable();
            $table->string('status', 30)->default('registered');
            $table->unsignedTinyInteger('priority')->default(0);
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('last_called_at')->nullable();
            $table->timestamp('entered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('no_show_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedSmallInteger('call_count')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('checked_in_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            $table->unique(['session_id', 'user_id'], 'pmb_queues_session_user_unique');
            $table->unique(['session_id', 'queue_code'], 'pmb_queues_session_code_unique');
            $table->index(['session_id', 'status'], 'pmb_queues_session_status_idx');
            $table->index(['room_id', 'status'], 'pmb_queues_room_status_idx');
            $table->index(['session_id', 'checked_in_at', 'priority'], 'pmb_queues_call_order_idx');
            $table->index('qr_token_hash', 'pmb_queues_qr_hash_idx');
        });

        Schema::create('pmb_queue_call_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('pmb_test_sessions')->cascadeOnDelete();
            $table->foreignId('queue_id')->constrained('pmb_offline_queues')->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('pmb_test_rooms')->nullOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('action', 40);
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->nullable();
            $table->unsignedSmallInteger('call_number')->default(1);
            $table->timestamp('called_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['session_id', 'action', 'created_at'], 'pmb_call_logs_session_action_idx');
        });

        Schema::create('pmb_queue_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('pmb_test_sessions')->cascadeOnDelete();
            $table->foreignId('queue_id')->constrained('pmb_offline_queues')->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->text('note')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['session_id', 'to_status', 'created_at'], 'pmb_status_logs_session_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pmb_queue_status_logs');
        Schema::dropIfExists('pmb_queue_call_logs');
        Schema::dropIfExists('pmb_offline_queues');
        Schema::dropIfExists('pmb_test_rooms');
        Schema::dropIfExists('pmb_test_sessions');
    }
};
