<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pmb_test_rooms', function (Blueprint $table): void {
            $table->foreignId('assigned_admin_id')
                ->nullable()
                ->after('stage_id')
                ->constrained('admins')
                ->nullOnDelete();
            $table->unique(
                ['session_id', 'stage_id', 'assigned_admin_id'],
                'pmb_rooms_session_stage_officer_unique'
            );
        });

        $normalize = static fn (?string $value): string => preg_replace('/[^a-z0-9]+/', '', strtolower((string) $value));
        $admins = DB::table('admins')
            ->where('is_active', true)
            ->get(['id', 'name', 'email']);

        $healthRooms = DB::table('pmb_test_rooms as rooms')
            ->join('pmb_queue_stages as stages', 'stages.id', '=', 'rooms.stage_id')
            ->where('stages.key', 'tes_kesehatan')
            ->where('rooms.code', '<>', 'KES-SHARED')
            ->select('rooms.id', 'rooms.session_id', 'rooms.stage_id', 'rooms.name')
            ->get();

        foreach ($healthRooms as $room) {
            $roomName = $normalize($room->name);
            $officer = $admins->first(function ($admin) use ($normalize, $roomName): bool {
                $emailName = strstr((string) $admin->email, '@', true) ?: '';
                $aliases = array_filter([$normalize($admin->name), $normalize($emailName)]);

                return collect($aliases)->contains(fn (string $alias): bool => strlen($alias) >= 3 && str_contains($roomName, $alias));
            });

            if (!$officer) {
                continue;
            }

            DB::table('pmb_test_rooms')->where('id', $room->id)->update([
                'assigned_admin_id' => $officer->id,
                'updated_at' => now(),
            ]);

            $activeSteps = DB::table('pmb_queue_stage_steps')
                ->where('stage_id', $room->stage_id)
                ->where('assigned_officer_id', $officer->id)
                ->whereIn('status', ['called', 'processing'])
                ->get(['id', 'queue_id']);

            if ($activeSteps->isEmpty()) {
                continue;
            }

            DB::table('pmb_queue_stage_steps')
                ->whereIn('id', $activeSteps->pluck('id'))
                ->update(['room_id' => $room->id, 'updated_at' => now()]);

            DB::table('pmb_offline_queues')
                ->whereIn('id', $activeSteps->pluck('queue_id'))
                ->update(['room_id' => $room->id, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::table('pmb_test_rooms', function (Blueprint $table): void {
            $table->dropUnique('pmb_rooms_session_stage_officer_unique');
            $table->dropConstrainedForeignId('assigned_admin_id');
        });
    }
};
