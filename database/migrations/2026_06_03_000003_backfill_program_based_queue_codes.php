<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('pmb_offline_queues')
            ->join('users', 'pmb_offline_queues.user_id', '=', 'users.id')
            ->leftJoin('jurusan', 'users.jurusan_id', '=', 'jurusan.id')
            ->whereNotNull('pmb_offline_queues.queue_number')
            ->select([
                'pmb_offline_queues.id',
                'pmb_offline_queues.session_id',
                'pmb_offline_queues.queue_number',
                'jurusan.nama_jurusan',
            ])
            ->orderBy('pmb_offline_queues.id')
            ->chunk(200, function ($queues): void {
                foreach ($queues as $queue) {
                    $queueCode = $this->queueCodeFor(
                        (string) $queue->nama_jurusan,
                        (int) $queue->queue_number
                    );

                    $exists = DB::table('pmb_offline_queues')
                        ->where('session_id', $queue->session_id)
                        ->where('queue_code', $queueCode)
                        ->where('id', '<>', $queue->id)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    DB::table('pmb_offline_queues')->where('id', $queue->id)->update([
                        'queue_code' => $queueCode,
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Kode antrian lama tidak dikembalikan otomatis.
    }

    private function queueCodeFor(string $program, int $number): string
    {
        $program = strtoupper($program);
        $prefix = match (true) {
            str_contains($program, 'GIZI') => 'GZ',
            str_contains($program, 'KEBIDANAN') => 'KB',
            str_contains($program, 'FARMASI') => 'FAR',
            default => 'Q',
        };

        return $prefix . str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }
};
