<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PERIODE_DESC = 'PMB TEST ANTRIAN';
    private const GELOMBANG_NAME = 'Gelombang Test Antrian';
    private const JURUSAN_CODE = 9999;
    private const SESSION_CODE = 'PMB-TEST-QUEUE';

    public function up(): void
    {
        DB::transaction(function (): void {
            $sessionId = DB::table('pmb_test_sessions')->where('code', self::SESSION_CODE)->value('id');

            if ($sessionId) {
                $queueIds = DB::table('pmb_offline_queues')
                    ->where('session_id', $sessionId)
                    ->pluck('id');

                DB::table('pmb_queue_stages')->where('session_id', $sessionId)->update(['default_room_id' => null]);
                DB::table('pmb_test_rooms')->where('session_id', $sessionId)->update(['stage_id' => null]);
                DB::table('pmb_offline_queues')->where('session_id', $sessionId)->update([
                    'current_stage_id' => null,
                    'room_id' => null,
                ]);

                if ($queueIds->isNotEmpty()) {
                    DB::table('pmb_queue_stage_logs')->whereIn('queue_id', $queueIds)->delete();
                    DB::table('pmb_queue_stage_steps')->whereIn('queue_id', $queueIds)->delete();
                    DB::table('pmb_queue_call_logs')->whereIn('queue_id', $queueIds)->delete();
                    DB::table('pmb_queue_status_logs')->whereIn('queue_id', $queueIds)->delete();
                }

                DB::table('pmb_queue_arrival_counters')->where('session_id', $sessionId)->delete();
                DB::table('pmb_queue_counters')->where('session_id', $sessionId)->delete();
                DB::table('pmb_offline_queues')->where('session_id', $sessionId)->delete();
                DB::table('pmb_test_rooms')->where('session_id', $sessionId)->delete();
                DB::table('pmb_queue_stages')->where('session_id', $sessionId)->delete();
                DB::table('pmb_test_sessions')->where('id', $sessionId)->delete();
            }

            DB::table('users')
                ->where('email', 'like', 'antrian.test%@sbh.test')
                ->delete();

            $periodeId = DB::table('periodes')->where('deskripsi', self::PERIODE_DESC)->value('id');

            if ($periodeId) {
                DB::table('gelombangs')
                    ->where('periode_id', $periodeId)
                    ->where('nama_gelombang', self::GELOMBANG_NAME)
                    ->delete();

                DB::table('periodes')->where('id', $periodeId)->delete();
            }

            DB::table('jurusan')->where('kd_jurusan', self::JURUSAN_CODE)->delete();
        });
    }

    public function down(): void
    {
        // Data dummy test tidak dibuat ulang saat rollback.
    }
};
