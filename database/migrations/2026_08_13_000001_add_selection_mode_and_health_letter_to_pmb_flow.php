<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pmb_offline_queues', function (Blueprint $table): void {
            $table->string('selection_mode', 20)->default('offline')->after('priority');
            $table->index(['session_id', 'selection_mode'], 'pmb_queues_session_mode_idx');
        });

        Schema::table('tes_kesehatan_anamnesa', function (Blueprint $table): void {
            $table->string('surat_kesehatan_path')->nullable()->after('keterangan');
            $table->timestamp('surat_kesehatan_uploaded_at')->nullable()->after('surat_kesehatan_path');
        });
    }

    public function down(): void
    {
        Schema::table('tes_kesehatan_anamnesa', function (Blueprint $table): void {
            $table->dropColumn(['surat_kesehatan_path', 'surat_kesehatan_uploaded_at']);
        });

        Schema::table('pmb_offline_queues', function (Blueprint $table): void {
            $table->dropIndex('pmb_queues_session_mode_idx');
            $table->dropColumn('selection_mode');
        });
    }
};
