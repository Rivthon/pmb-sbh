<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $healthStages = DB::table('pmb_queue_stages')
                ->where('key', 'tes_kesehatan')
                ->get(['id', 'session_id']);

            foreach ($healthStages as $stage) {
                $splitRooms = DB::table('pmb_test_rooms')
                    ->where('session_id', $stage->session_id)
                    ->where('stage_id', $stage->id)
                    ->whereIn('code', ['K-D3-KEB', 'K-S1-FAR', 'K-S1-GIZ'])
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get();

                if ($splitRooms->isEmpty()) {
                    continue;
                }

                $customRoom = DB::table('pmb_test_rooms')
                    ->where('session_id', $stage->session_id)
                    ->where('stage_id', $stage->id)
                    ->where('code', 'KES-1')
                    ->first();

                $keepRoom = $customRoom ?: $splitRooms->first();
                $deleteRoomIds = $splitRooms
                    ->pluck('id')
                    ->reject(fn ($id) => (int) $id === (int) $keepRoom->id)
                    ->values();

                if (!$customRoom) {
                    DB::table('pmb_test_rooms')->where('id', $keepRoom->id)->update([
                        'name' => 'Tes Kesehatan',
                        'code' => 'KES-1',
                        'sort_order' => 91,
                        'updated_at' => now(),
                    ]);
                }

                DB::table('pmb_queue_stages')->where('id', $stage->id)->update([
                    'default_room_id' => $keepRoom->id,
                    'updated_at' => now(),
                ]);

                if ($deleteRoomIds->isEmpty()) {
                    continue;
                }

                DB::table('pmb_offline_queues')->whereIn('room_id', $deleteRoomIds)->update([
                    'room_id' => $keepRoom->id,
                    'updated_at' => now(),
                ]);

                DB::table('pmb_queue_stage_steps')->whereIn('room_id', $deleteRoomIds)->update([
                    'room_id' => $keepRoom->id,
                    'updated_at' => now(),
                ]);

                DB::table('pmb_queue_call_logs')->whereIn('room_id', $deleteRoomIds)->update([
                    'room_id' => $keepRoom->id,
                    'updated_at' => now(),
                ]);

                DB::table('pmb_test_rooms')->whereIn('id', $deleteRoomIds)->delete();
            }
        });
    }

    public function down(): void
    {
        // Loket kesehatan yang sudah disatukan tidak dipecah ulang saat rollback.
    }
};
