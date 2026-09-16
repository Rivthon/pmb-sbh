<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const PERIODE_DESC = 'PMB TEST ANTRIAN';
    private const GELOMBANG_NAME = 'Gelombang Test Antrian';
    private const JURUSAN_CODE = 9999;
    private const SESSION_CODE = 'PMB-TEST-QUEUE';
    private const SESSION_NAME = 'PMB TEST QUEUE';

    public function up(): void
    {
        $now = now();

        $periodeId = DB::table('periodes')->where('deskripsi', self::PERIODE_DESC)->value('id');
        $periodeInsert = [
            'tgl_mulai' => $now->toDateString(),
            'deskripsi' => self::PERIODE_DESC,
            'status_periode' => 'aktif',
            'tanggal_tes' => $now->toDateString(),
            'linked' => 'pmb-test-antrian',
            'created_at' => $now,
            'updated_at' => $now,
        ];
        $periodeUpdate = [
            'status_periode' => 'aktif',
            'tanggal_tes' => $now->toDateString(),
            'updated_at' => $now,
        ];

        if (Schema::hasColumn('periodes', 'file_lulus')) {
            $periodeInsert['file_lulus'] = 'pmb-test-antrian.pdf';
            $periodeUpdate['file_lulus'] = 'pmb-test-antrian.pdf';
        }

        if (Schema::hasColumn('periodes', 'deleted_at')) {
            $periodeUpdate['deleted_at'] = null;
        }

        if (!$periodeId) {
            $periodeId = DB::table('periodes')->insertGetId($periodeInsert);
        } else {
            DB::table('periodes')->where('id', $periodeId)->update($periodeUpdate);
        }

        $gelombangId = DB::table('gelombangs')
            ->where('periode_id', $periodeId)
            ->where('nama_gelombang', self::GELOMBANG_NAME)
            ->value('id');

        if (!$gelombangId) {
            $gelombangId = DB::table('gelombangs')->insertGetId([
                'periode_id' => $periodeId,
                'nama_gelombang' => self::GELOMBANG_NAME,
                'tgl_mulai' => $now->copy()->subDay()->toDateString(),
                'tgl_selesai' => $now->copy()->addMonth()->toDateString(),
                'status_gelombang' => 'aktif',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('gelombangs')->where('id', $gelombangId)->update([
                'status_gelombang' => 'aktif',
                'deleted_at' => null,
                'updated_at' => $now,
            ]);
        }

        $jurusanId = DB::table('jurusan')->where('kd_jurusan', self::JURUSAN_CODE)->value('id');

        if (!$jurusanId) {
            $jurusanId = DB::table('jurusan')->insertGetId([
                'kd_jurusan' => self::JURUSAN_CODE,
                'nama_jurusan' => 'Program Studi Test Antrian',
                'deskripsi' => 'Data jurusan khusus pengujian proses antrian PMB.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $sessionId = DB::table('pmb_test_sessions')->where('code', self::SESSION_CODE)->value('id');

        if (!$sessionId) {
            $sessionId = DB::table('pmb_test_sessions')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'code' => self::SESSION_CODE,
                'name' => self::SESSION_NAME,
                'type' => 'campuran',
                'periode_id' => $periodeId,
                'gelombang_id' => $gelombangId,
                'starts_at' => $now,
                'ends_at' => $now->copy()->addHours(6),
                'checkin_open_at' => $now->copy()->subHour(),
                'checkin_close_at' => $now->copy()->addHours(5),
                'status' => 'open',
                'notes' => 'Data uji proses antrian PMB multi-tahap.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('pmb_test_sessions')->where('id', $sessionId)->update([
                'name' => self::SESSION_NAME,
                'type' => 'campuran',
                'periode_id' => $periodeId,
                'gelombang_id' => $gelombangId,
                'starts_at' => $now,
                'ends_at' => $now->copy()->addHours(6),
                'checkin_open_at' => $now->copy()->subHour(),
                'checkin_close_at' => $now->copy()->addHours(5),
                'status' => 'open',
                'notes' => 'Data uji proses antrian PMB multi-tahap.',
                'deleted_at' => null,
                'updated_at' => $now,
            ]);
        }

        $stages = [
            'pemberkasan' => ['name' => 'Pemberkasan', 'sort_order' => 10, 'room_code' => 'MEJA-1', 'room_name' => 'Meja Pemberkasan 1'],
            'tes_tulis' => ['name' => 'Tes Tulis', 'sort_order' => 20, 'room_code' => 'LAB-1', 'room_name' => 'Lab Komputer 1'],
            'tes_kesehatan' => ['name' => 'Tes Kesehatan', 'sort_order' => 30, 'room_code' => 'KES-1', 'room_name' => 'Ruang Kesehatan 1'],
            'wawancara_kaprodi' => ['name' => 'Wawancara Kaprodi', 'sort_order' => 40, 'room_code' => 'KAPRODI-1', 'room_name' => 'Ruang Kaprodi 1'],
        ];

        foreach ($stages as $key => $stage) {
            $stageId = DB::table('pmb_queue_stages')
                ->where('session_id', $sessionId)
                ->where('key', $key)
                ->value('id');

            if (!$stageId) {
                $stageId = DB::table('pmb_queue_stages')->insertGetId([
                    'session_id' => $sessionId,
                    'key' => $key,
                    'name' => $stage['name'],
                    'sort_order' => $stage['sort_order'],
                    'is_required' => true,
                    'is_active' => true,
                    'metadata' => json_encode(['seed' => 'pmb_queue_test_flow']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('pmb_queue_stages')->where('id', $stageId)->update([
                    'name' => $stage['name'],
                    'sort_order' => $stage['sort_order'],
                    'is_required' => true,
                    'is_active' => true,
                    'updated_at' => $now,
                ]);
            }

            if ($key === 'tes_kesehatan') {
                $healthRooms = [
                    ['code' => 'KES-1', 'name' => 'Ruang Kesehatan 1', 'sort_order' => 301],
                    ['code' => 'KES-2', 'name' => 'Ruang Kesehatan 2', 'sort_order' => 302],
                    ['code' => 'KES-3', 'name' => 'Ruang Kesehatan 3', 'sort_order' => 303],
                ];
                $firstHealthRoomId = null;

                foreach ($healthRooms as $hr) {
                    $hrId = DB::table('pmb_test_rooms')
                        ->where('session_id', $sessionId)
                        ->where('code', $hr['code'])
                        ->value('id');

                    if (!$hrId) {
                        $hrId = DB::table('pmb_test_rooms')->insertGetId([
                            'uuid' => (string) Str::uuid(),
                            'session_id' => $sessionId,
                            'stage_id' => $stageId,
                            'name' => $hr['name'],
                            'code' => $hr['code'],
                            'capacity' => 1,
                            'sort_order' => $hr['sort_order'],
                            'status' => 'available',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    } else {
                        DB::table('pmb_test_rooms')->where('id', $hrId)->update([
                            'stage_id' => $stageId,
                            'name' => $hr['name'],
                            'sort_order' => $hr['sort_order'],
                            'status' => 'available',
                            'updated_at' => $now,
                        ]);
                    }

                    if (!$firstHealthRoomId) {
                        $firstHealthRoomId = $hrId;
                    }
                }

                DB::table('pmb_queue_stages')->where('id', $stageId)->update([
                    'default_room_id' => $firstHealthRoomId,
                    'updated_at' => $now,
                ]);
            } else {
                $roomId = DB::table('pmb_test_rooms')
                    ->where('session_id', $sessionId)
                    ->where('code', $stage['room_code'])
                    ->value('id');

                if (!$roomId) {
                    $roomId = DB::table('pmb_test_rooms')->insertGetId([
                        'uuid' => (string) Str::uuid(),
                        'session_id' => $sessionId,
                        'stage_id' => $stageId,
                        'name' => $stage['room_name'],
                        'code' => $stage['room_code'],
                        'capacity' => 1,
                        'sort_order' => $stage['sort_order'],
                        'status' => 'available',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                } else {
                    DB::table('pmb_test_rooms')->where('id', $roomId)->update([
                        'stage_id' => $stageId,
                        'name' => $stage['room_name'],
                        'capacity' => 1,
                        'sort_order' => $stage['sort_order'],
                        'status' => 'available',
                        'updated_at' => $now,
                    ]);
                }

                DB::table('pmb_queue_stages')->where('id', $stageId)->update([
                    'default_room_id' => $roomId,
                    'updated_at' => $now,
                ]);
            }
        }

        for ($number = 1; $number <= 10; $number++) {
            $email = sprintf('antrian.test%02d@sbh.test', $number);
            $code = sprintf('PMBTEST%02d', $number);

            $userId = DB::table('users')->where('email', $email)->value('id');

            $userData = [
                'code' => $code,
                'name' => sprintf('Peserta Test Antrian %02d', $number),
                'email' => $email,
                'phone' => sprintf('081230000%03d', $number),
                'address' => 'Alamat test antrian PMB',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'password_plaintext' => 'password',
                'role' => 'pengguna',
                'periode_id' => $periodeId,
                'gelombang_id' => $gelombangId,
                'jurusan_id' => $jurusanId,
                'status_pemb' => 'test_antrian',
                'status_biodata' => 'lengkap',
                'status_berkas' => 'lengkap',
                'deleted_at' => null,
                'updated_at' => $now,
            ];

            if (!$userId) {
                $userId = DB::table('users')->insertGetId(array_merge($userData, [
                    'uuid' => (string) Str::uuid(),
                    'created_at' => $now,
                ]));
            } else {
                DB::table('users')->where('id', $userId)->update($userData);
            }

            $queueId = DB::table('pmb_offline_queues')
                ->where('session_id', $sessionId)
                ->where('user_id', $userId)
                ->value('id');

            if (!$queueId) {
                $queueUuid = (string) Str::uuid();

                DB::table('pmb_offline_queues')->insert([
                    'uuid' => $queueUuid,
                    'session_id' => $sessionId,
                    'user_id' => $userId,
                    'qr_token_hash' => hash('sha256', $queueUuid . '|' . config('app.key')),
                    'status' => 'registered',
                    'arrival_status' => 'not_checked_in',
                    'overall_status' => 'not_started',
                    'priority' => 0,
                    'notes' => 'Peserta data uji antrian PMB.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('pmb_offline_queues')->where('id', $queueId)->update([
                    'status' => 'registered',
                    'arrival_status' => 'not_checked_in',
                    'overall_status' => 'not_started',
                    'current_stage_id' => null,
                    'room_id' => null,
                    'queue_number' => null,
                    'queue_code' => null,
                    'checked_in_at' => null,
                    'called_at' => null,
                    'last_called_at' => null,
                    'entered_at' => null,
                    'completed_at' => null,
                    'no_show_at' => null,
                    'cancelled_at' => null,
                    'call_count' => 0,
                    'notes' => 'Peserta data uji antrian PMB.',
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $sessionId = DB::table('pmb_test_sessions')->where('code', self::SESSION_CODE)->value('id');

        if ($sessionId) {
            DB::table('pmb_queue_stages')->where('session_id', $sessionId)->update(['default_room_id' => null]);
            DB::table('pmb_test_rooms')->where('session_id', $sessionId)->update(['stage_id' => null]);
            DB::table('pmb_offline_queues')->where('session_id', $sessionId)->delete();
            DB::table('pmb_queue_counters')->where('session_id', $sessionId)->delete();
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
    }
};
