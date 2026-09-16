<?php

namespace Tests\Feature;

use App\Enums\PmbStatus;
use App\Models\Admin;
use App\Models\Gelombang;
use App\Models\Jurusan;
use App\Models\Periode;
use App\Models\PmbOfflineQueue;
use App\Models\PmbQueueStage;
use App\Models\PmbQueueStageStep;
use App\Models\PmbTestRoom;
use App\Models\PmbTestSession;
use App\Models\HasilTesTulis;
use App\Models\TesKesehatanAnamnesa;
use App\Models\TesTulis;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\WawancaraPmb;
use App\Http\Controllers\Admin\PmbOfflineQueueController;
use App\Services\Pmb\PmbOfflineQueueWorkflowService;
use App\Services\Pmb\PmbOnlineSelectionService;
use App\Support\AdminPermissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PmbOfflineQueueWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    private PmbOfflineQueueWorkflowService $workflow;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workflow = app(PmbOfflineQueueWorkflowService::class);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([
            AdminPermissions::DASHBOARD_VIEW,
            AdminPermissions::PMB_VIEW,
            AdminPermissions::PMB_CREATE,
            AdminPermissions::PMB_EDIT,
            AdminPermissions::PMB_QUEUE_TES_TULIS,
            AdminPermissions::PMB_QUEUE_KESEHATAN,
            AdminPermissions::PMB_QUEUE_WAWANCARA,
            AdminPermissions::PMB_QUEUE_ONLINE_MANAGE,
            AdminPermissions::PMB_ONLINE_TES_TULIS_MANAGE,
            AdminPermissions::PMB_ONLINE_SOAL_MANAGE,
            AdminPermissions::PMB_ONLINE_HASIL_TES_VIEW,
            AdminPermissions::PMB_ONLINE_KESEHATAN_MANAGE,
            AdminPermissions::PMB_ONLINE_WAWANCARA_MANAGE,
            AdminPermissions::KESEHATAN_REVIEW,
            AdminPermissions::WAWANCARA_REVIEW,
        ] as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => AdminPermissions::GUARD,
            ]);
        }
    }

    public function test_admin_adds_only_verified_participants_without_wave_locking_or_duplicates(): void
    {
        [$periode, $gelombang1, $gelombang3, $jurusan] = $this->masterData();
        $admin = $this->adminWith(AdminPermissions::PMB_EDIT);
        $session = $this->offlineSession($periode, $gelombang3);

        $verifiedWave1 = $this->participant($periode, $gelombang1, $jurusan, PmbStatus::Verified);
        $verifiedWave3 = $this->participant($periode, $gelombang3, $jurusan, PmbStatus::Verified);
        $unverified = $this->participant($periode, $gelombang1, $jurusan, PmbStatus::MenungguValidasi);

        $this->actingAs($admin, 'admin');
        $response = $this->assignParticipants($session, [$verifiedWave1->id, $verifiedWave3->id, $unverified->id]);
        $this->assertTrue($response->isRedirection());

        $this->assertDatabaseHas('pmb_offline_queues', [
            'session_id' => $session->id,
            'user_id' => $verifiedWave1->id,
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
        ]);
        $this->assertDatabaseHas('pmb_offline_queues', [
            'session_id' => $session->id,
            'user_id' => $verifiedWave3->id,
        ]);
        $this->assertDatabaseMissing('pmb_offline_queues', [
            'session_id' => $session->id,
            'user_id' => $unverified->id,
        ]);

        $response = $this->assignParticipants($session, [$verifiedWave1->id]);
        $this->assertTrue($response->isRedirection());

        $this->assertSame(1, PmbOfflineQueue::where('session_id', $session->id)->where('user_id', $verifiedWave1->id)->count());
        $this->assertNotNull(PmbOfflineQueue::where('session_id', $session->id)->where('user_id', $verifiedWave1->id)->first()?->signedCheckInUrl());
    }

    public function test_check_in_auto_starts_tes_tulis(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $queue = $this->queueFor($session, $this->participant($periode, $gelombang, $jurusan));

        $checkedIn = $this->workflow->checkIn($queue, $this->adminWith(AdminPermissions::PMB_EDIT)->id);
        $step = $checkedIn->stageSteps()->whereHas('stage', fn ($query) => $query->where('key', PmbQueueStage::KEY_TES_TULIS))->first();

        $this->assertSame(PmbOfflineQueue::STATUS_IN_ROOM, $checkedIn->status);
        $this->assertSame(PmbQueueStageStep::STATUS_PROCESSING, $step?->status);
        $this->assertNotNull($checkedIn->queue_code);
    }

    public function test_check_in_skip_tes_tulis_flow_goes_directly_to_health_queue(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $queue = $this->queueFor($session, $this->participant($periode, $gelombang, $jurusan));
        $this->workflow->markQueueTesTulisRequirement($queue, false);

        $checkedIn = $this->workflow->checkIn($queue, $this->adminWith(AdminPermissions::PMB_EDIT)->id);
        $healthStep = $checkedIn->stageSteps()->whereHas('stage', fn ($query) => $query->where('key', PmbQueueStage::KEY_TES_KESEHATAN))->first();
        $tesTulisStep = $checkedIn->stageSteps()->whereHas('stage', fn ($query) => $query->where('key', PmbQueueStage::KEY_TES_TULIS))->first();

        $this->assertSame(PmbOfflineQueue::STATUS_WAITING, $checkedIn->status);
        $this->assertSame($healthStep?->stage_id, $checkedIn->current_stage_id);
        $this->assertSame(PmbQueueStageStep::STATUS_WAITING, $healthStep?->status);
        $this->assertSame(PmbQueueStageStep::STATUS_SKIPPED, $tesTulisStep?->status);
    }

    public function test_assign_participants_can_mark_skip_tes_tulis_before_check_in(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $admin = $this->adminWith(AdminPermissions::PMB_EDIT);
        $session = $this->offlineSession($periode, $gelombang);
        $participant = $this->participant($periode, $gelombang, $jurusan);

        $this->actingAs($admin, 'admin');
        $response = $this->assignParticipants($session, [$participant->id], 'bebas_tes_tulis');
        $this->assertTrue($response->isRedirection());

        $queue = PmbOfflineQueue::where('session_id', $session->id)->where('user_id', $participant->id)->firstOrFail();
        $this->assertStringContainsString(PmbOfflineQueueWorkflowService::NOTES_FLAG_SKIP_TES_TULIS, (string) $queue->notes);
    }

    public function test_assign_participants_check_in_skip_tes_tulis_goes_directly_to_health(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $admin = $this->adminWith(AdminPermissions::PMB_EDIT);
        $session = $this->offlineSession($periode, $gelombang);
        $participant = $this->participant($periode, $gelombang, $jurusan);

        $this->actingAs($admin, 'admin');
        $response = $this->assignParticipants($session, [$participant->id], 'bebas_tes_tulis', true);
        $this->assertTrue($response->isRedirection());

        $queue = PmbOfflineQueue::with(['stageSteps.stage'])->where('session_id', $session->id)->where('user_id', $participant->id)->firstOrFail();
        $healthStep = $queue->stageSteps->first(fn (PmbQueueStageStep $step) => $step->stage?->key === PmbQueueStage::KEY_TES_KESEHATAN);

        $this->assertSame(PmbOfflineQueue::STATUS_WAITING, $queue->status);
        $this->assertSame($healthStep?->stage_id, $queue->current_stage_id);
    }

    public function test_manual_barcode_check_in_preserves_preconfigured_test_flow(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $admin = $this->adminWith(AdminPermissions::PMB_EDIT);
        $session = $this->offlineSession($periode, $gelombang);
        $participant = $this->participant($periode, $gelombang, $jurusan);
        $queue = $this->queueFor($session, $participant);
        $this->workflow->markQueueTesTulisRequirement($queue, false);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.pmb-queues.check-in', $session), [
                'code' => $queue->uuid,
                'test_flow' => 'tes_tulis',
            ])
            ->assertRedirect(route('admin.pmb-queues.show', $session));

        $queue->refresh()->load('stageSteps.stage');
        $healthStep = $queue->stageSteps->first(fn (PmbQueueStageStep $step) => $step->stage?->key === PmbQueueStage::KEY_TES_KESEHATAN);

        $this->assertSame(PmbOfflineQueue::STATUS_WAITING, $queue->status);
        $this->assertSame($healthStep?->stage_id, $queue->current_stage_id);
        $this->assertStringContainsString(PmbOfflineQueueWorkflowService::NOTES_FLAG_SKIP_TES_TULIS, (string) $queue->notes);
    }

    public function test_bulk_check_in_preserves_each_participants_preconfigured_test_flow(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $admin = $this->adminWith(AdminPermissions::PMB_EDIT);
        $session = $this->offlineSession($periode, $gelombang);
        $tesTulisQueue = $this->queueFor($session, $this->participant($periode, $gelombang, $jurusan));
        $healthQueue = $this->queueFor($session, $this->participant($periode, $gelombang, $jurusan));
        $this->workflow->markQueueTesTulisRequirement($healthQueue, false);

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.pmb-queues.bulk-check-in', $session), [
                'queue_ids' => [$tesTulisQueue->id, $healthQueue->id],
                'test_flow' => 'tes_tulis',
            ])
            ->assertOk();

        $tesTulisQueue->refresh()->load('currentStage');
        $healthQueue->refresh()->load('currentStage');

        $this->assertSame(PmbQueueStage::KEY_TES_TULIS, $tesTulisQueue->currentStage?->key);
        $this->assertSame(PmbQueueStage::KEY_TES_KESEHATAN, $healthQueue->currentStage?->key);
        $this->assertTrue($tesTulisQueue->requiresTesTulis());
        $this->assertFalse($healthQueue->requiresTesTulis());
    }

    public function test_check_in_pages_do_not_offer_test_flow_override(): void
    {
        [$periode, $gelombang] = $this->masterData();
        $admin = $this->adminWith(AdminPermissions::PMB_EDIT);
        $admin->givePermissionTo(AdminPermissions::PMB_QUEUE_SCAN);
        $session = $this->offlineSession($periode, $gelombang);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.pmb-queues.show', $session))
            ->assertOk()
            ->assertDontSee('checkInTestFlow')
            ->assertDontSee('name="test_flow"', false);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.pmb-queues.scan', $session))
            ->assertOk()
            ->assertDontSee('Jalur tes pada kartu')
            ->assertDontSee('name="test_flow"', false);
    }

    public function test_health_shared_queue_allows_multiple_officers_without_double_call(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $participants = collect(range(1, 4))->map(fn () => $this->participant($periode, $gelombang, $jurusan));
        $healthStage = $this->workflow->ensureDefaultStages($session)->firstWhere('key', PmbQueueStage::KEY_TES_KESEHATAN);
        $tesTulisStage = $this->workflow->ensureDefaultStages($session)->firstWhere('key', PmbQueueStage::KEY_TES_TULIS);
        $officers = collect(range(1, 3))->map(fn () => $this->adminWith(AdminPermissions::PMB_QUEUE_KESEHATAN));

        $participants->each(function (User $participant) use ($session, $tesTulisStage): void {
            $queue = $this->workflow->checkIn($this->queueFor($session, $participant));
            $this->workflow->updateStep($queue->fresh(['stageSteps.stage'])->stageSteps->firstWhere('stage_id', $tesTulisStage->id), 'complete_test_tulis');
        });

        $called = $officers->map(fn (Admin $officer) => $this->workflow->autoDispatchForOfficer($session, PmbQueueStage::KEY_TES_KESEHATAN, $officer->id));

        $this->assertCount(3, $called->pluck('queue_id')->unique());
        $this->assertEqualsCanonicalizing($officers->pluck('id')->all(), $called->pluck('assigned_officer_id')->all());
        $this->assertSame(1, PmbQueueStageStep::where('stage_id', $healthStage->id)->where('status', PmbQueueStageStep::STATUS_WAITING)->count());

        $this->workflow->updateStep($called->first(), 'complete_health', adminId: $officers->first()->id);
        $next = PmbQueueStageStep::where('stage_id', $healthStage->id)
            ->where('assigned_officer_id', $officers->first()->id)
            ->where('status', PmbQueueStageStep::STATUS_PROCESSING)
            ->first();

        $this->assertNotNull($next);
        $this->assertFalse($called->pluck('queue_id')->contains($next->queue_id));
    }

    public function test_health_dispatch_uses_the_room_assigned_to_the_logged_in_officer(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $stages = $this->workflow->ensureDefaultStages($session);
        $tesTulisStage = $stages->firstWhere('key', PmbQueueStage::KEY_TES_TULIS);
        $healthStage = $stages->firstWhere('key', PmbQueueStage::KEY_TES_KESEHATAN);
        $officerDiah = $this->adminWith(AdminPermissions::PMB_QUEUE_KESEHATAN);
        $officerDiah->update(['name' => 'Diah']);
        $officerMellisa = $this->adminWith(AdminPermissions::PMB_QUEUE_KESEHATAN);
        $officerMellisa->update(['name' => 'Mellisa']);

        $roomDiah = $session->rooms()->create([
            'stage_id' => $healthStage->id,
            'assigned_admin_id' => $officerDiah->id,
            'name' => 'Petugas Kesehatan Diah',
            'code' => 'KES-DIAH',
            'capacity' => 1,
            'sort_order' => 1,
            'status' => PmbTestRoom::STATUS_AVAILABLE,
        ]);
        $roomMellisa = $session->rooms()->create([
            'stage_id' => $healthStage->id,
            'assigned_admin_id' => $officerMellisa->id,
            'name' => 'Petugas Kesehatan Mellisa',
            'code' => 'KES-MELLISA',
            'capacity' => 1,
            'sort_order' => 2,
            'status' => PmbTestRoom::STATUS_AVAILABLE,
        ]);

        collect(range(1, 2))->each(function () use ($periode, $gelombang, $jurusan, $session, $tesTulisStage): void {
            $queue = $this->workflow->checkIn($this->queueFor($session, $this->participant($periode, $gelombang, $jurusan)));
            $this->workflow->updateStep(
                $queue->fresh(['stageSteps.stage'])->stageSteps->firstWhere('stage_id', $tesTulisStage->id),
                'complete_test_tulis'
            );
        });

        // Mellisa login lebih dulu, tetapi tetap harus menerima loket Mellisa.
        $mellisaStep = $this->workflow->autoDispatchForOfficer(
            $session,
            PmbQueueStage::KEY_TES_KESEHATAN,
            $officerMellisa->id
        );
        $diahStep = $this->workflow->autoDispatchForOfficer(
            $session,
            PmbQueueStage::KEY_TES_KESEHATAN,
            $officerDiah->id
        );

        $this->assertSame($roomMellisa->id, $mellisaStep->room_id);
        $this->assertSame($officerMellisa->id, $mellisaStep->assigned_officer_id);
        $this->assertSame($roomDiah->id, $diahStep->room_id);
        $this->assertSame($officerDiah->id, $diahStep->assigned_officer_id);
    }

    public function test_legacy_health_room_becomes_a_normal_room_after_an_officer_is_assigned(): void
    {
        [$periode, $gelombang] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $healthStage = $this->workflow->ensureDefaultStages($session)
            ->firstWhere('key', PmbQueueStage::KEY_TES_KESEHATAN);
        $officer = $this->adminWith(AdminPermissions::PMB_QUEUE_KESEHATAN);
        $officer->update(['name' => 'Haryanti']);
        $legacyRoom = $session->rooms()->where('code', 'KES-SHARED')->firstOrFail();

        $legacyRoom->update([
            'assigned_admin_id' => $officer->id,
            'name' => 'Petugas Kesehatan Haryanti',
        ]);

        $this->workflow->ensureDefaultStages($session);

        $this->assertDatabaseHas('pmb_test_rooms', [
            'id' => $legacyRoom->id,
            'stage_id' => $healthStage->id,
            'assigned_admin_id' => $officer->id,
            'name' => 'Petugas Kesehatan Haryanti',
        ]);
    }

    public function test_deleted_legacy_health_room_is_not_recreated_when_account_rooms_exist(): void
    {
        [$periode, $gelombang] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $healthStage = $this->workflow->ensureDefaultStages($session)
            ->firstWhere('key', PmbQueueStage::KEY_TES_KESEHATAN);
        $legacyRoom = $session->rooms()->where('code', 'KES-SHARED')->firstOrFail();
        $officer = $this->adminWith(AdminPermissions::PMB_QUEUE_KESEHATAN);

        $session->rooms()->create([
            'stage_id' => $healthStage->id,
            'assigned_admin_id' => $officer->id,
            'name' => 'Petugas Kesehatan Diah',
            'code' => 'KES-DIAH',
            'capacity' => 1,
            'sort_order' => 1,
            'status' => PmbTestRoom::STATUS_AVAILABLE,
        ]);

        $healthStage->update(['default_room_id' => null]);
        $legacyRoom->delete();
        $this->workflow->ensureDefaultStages($session);

        $this->assertDatabaseMissing('pmb_test_rooms', [
            'session_id' => $session->id,
            'code' => 'KES-SHARED',
        ]);
    }

    public function test_adding_health_room_uses_the_next_unused_session_code(): void
    {
        [$periode, $gelombang] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $stages = $this->workflow->ensureDefaultStages($session);
        $healthStage = $stages->firstWhere('key', PmbQueueStage::KEY_TES_KESEHATAN);
        $interviewStage = $stages->firstWhere('key', PmbQueueStage::KEY_WAWANCARA_KAPRODI);
        $officer = $this->adminWith(AdminPermissions::PMB_EDIT);
        $role = Role::firstOrCreate([
            'name' => AdminPermissions::LEGACY_ROLE_PETUGAS_MEDIS,
            'guard_name' => AdminPermissions::GUARD,
        ]);
        $officer->assignRole($role);

        $session->rooms()->create([
            'stage_id' => $interviewStage->id,
            'name' => 'Kode Lama',
            'code' => 'KES-3',
            'capacity' => 1,
            'sort_order' => 3,
            'status' => PmbTestRoom::STATUS_AVAILABLE,
        ]);

        $this->actingAs($officer, 'admin');
        $request = Request::create('/admin/pmb-queues/' . $session->uuid . '/rooms', 'POST', [
            'stage_id' => $healthStage->id,
            'name' => 'Ruang Kesehatan 1',
            'assigned_admin_id' => $officer->id,
            'status' => PmbTestRoom::STATUS_AVAILABLE,
        ], [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);

        app(PmbOfflineQueueController::class)->storeRoom($request, $session);

        $this->assertDatabaseHas('pmb_test_rooms', [
            'session_id' => $session->id,
            'stage_id' => $healthStage->id,
            'assigned_admin_id' => $officer->id,
            'code' => 'KES-4',
            'name' => 'Ruang Kesehatan 1',
        ]);
    }

    public function test_health_room_name_can_be_edited_and_falls_back_to_officer_name(): void
    {
        [$periode, $gelombang] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $healthStage = $this->workflow->ensureDefaultStages($session)->firstWhere('key', PmbQueueStage::KEY_TES_KESEHATAN);
        $role = Role::firstOrCreate([
            'name' => AdminPermissions::LEGACY_ROLE_PETUGAS_MEDIS,
            'guard_name' => AdminPermissions::GUARD,
        ]);
        $officer = $this->adminWith(AdminPermissions::PMB_EDIT);
        $officer->assignRole($role);

        $this->actingAs($officer, 'admin');
        $fallbackRequest = Request::create('/admin/pmb-queues/' . $session->uuid . '/rooms', 'POST', [
            'stage_id' => $healthStage->id,
            'assigned_admin_id' => $officer->id,
            'status' => PmbTestRoom::STATUS_AVAILABLE,
        ], [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);

        app(PmbOfflineQueueController::class)->storeRoom($fallbackRequest, $session);
        $room = $session->rooms()
            ->where('stage_id', $healthStage->id)
            ->where('assigned_admin_id', $officer->id)
            ->firstOrFail();
        $this->assertSame('Petugas Kesehatan ' . $officer->name, $room->name);

        $editRequest = Request::create('/admin/pmb-queues/rooms/' . $room->uuid, 'PUT', [
            'stage_id' => $healthStage->id,
            'name' => 'Ruang Kesehatan Melati',
            'assigned_admin_id' => $officer->id,
            'status' => PmbTestRoom::STATUS_PAUSED,
        ], [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);

        app(PmbOfflineQueueController::class)->updateRoom($editRequest, $room);
        $room->refresh();
        $this->assertSame('Ruang Kesehatan Melati', $room->name);
        $this->assertSame(PmbTestRoom::STATUS_PAUSED, $room->status);

        $duplicateRequest = Request::create('/admin/pmb-queues/' . $session->uuid . '/rooms', 'POST', [
            'stage_id' => $healthStage->id,
            'name' => 'Loket Duplikat',
            'assigned_admin_id' => $officer->id,
            'status' => PmbTestRoom::STATUS_AVAILABLE,
        ], [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);

        $this->expectException(ValidationException::class);
        app(PmbOfflineQueueController::class)->storeRoom($duplicateRequest, $session);
    }

    public function test_health_exam_form_completes_queue_and_auto_dispatches_next_participant(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $participants = collect(range(1, 2))->map(fn () => $this->participant($periode, $gelombang, $jurusan));
        $stages = $this->workflow->ensureDefaultStages($session);
        $tesTulisStage = $stages->firstWhere('key', PmbQueueStage::KEY_TES_TULIS);
        $healthStage = $stages->firstWhere('key', PmbQueueStage::KEY_TES_KESEHATAN);
        $officer = $this->adminWith(AdminPermissions::PMB_QUEUE_KESEHATAN);
        $officer->givePermissionTo(AdminPermissions::KESEHATAN_REVIEW);

        $participants->each(function (User $participant) use ($session, $tesTulisStage): void {
            $queue = $this->workflow->checkIn($this->queueFor($session, $participant));
            $this->workflow->updateStep($queue->fresh(['stageSteps.stage'])->stageSteps->firstWhere('stage_id', $tesTulisStage->id), 'complete_test_tulis');

            TesKesehatanAnamnesa::create([
                'user_id' => $participant->id,
                'tanggal_pengisian' => now(),
                'riwayat_penyakit_keluarga' => 0,
                'riwayat_penyakit_pribadi' => 0,
                'riwayat_operasi' => 0,
                'konsumsi_obat_rutin' => 0,
                'penyakit_menular' => 0,
                'status' => 'belum diperiksa',
            ]);
        });

        $first = $this->workflow->autoDispatchForOfficer($session, PmbQueueStage::KEY_TES_KESEHATAN, $officer->id);
        $anamnesa = TesKesehatanAnamnesa::where('user_id', $first->queue->user_id)->firstOrFail();

        $this->actingAs($officer, 'admin')
            ->post(route('admin.tes-kesehatan.store-pemeriksaan', $anamnesa), [
                'queue_uuid' => $first->queue->uuid,
                'nama_pemeriksa' => $officer->name,
                'tanggal_pemeriksaan' => now()->toDateString(),
                'tinggi_badan' => 165,
                'berat_badan' => 55,
                'tekanan_darah' => '120/80',
                'rekomendasi' => 'layak',
            ])
            ->assertRedirect(route('admin.pmb-queues.officer.kesehatan', $session));

        $this->assertDatabaseHas('pmb_queue_stage_steps', [
            'id' => $first->id,
            'status' => PmbQueueStageStep::STATUS_COMPLETED,
        ]);
        $this->assertDatabaseHas('tes_kesehatan_pemeriksaan', [
            'tes_kesehatan_anamnesa_id' => $anamnesa->id,
            'nama_pemeriksa' => $officer->name,
        ]);

        $next = PmbQueueStageStep::where('stage_id', $healthStage->id)
            ->where('assigned_officer_id', $officer->id)
            ->where('status', PmbQueueStageStep::STATUS_PROCESSING)
            ->first();

        $this->assertNotNull($next);
        $this->assertNotSame($first->queue_id, $next->queue_id);
    }

    public function test_health_only_officer_dashboard_redirects_to_health_desk(): void
    {
        [$periode, $gelombang] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $officer = $this->adminWith(AdminPermissions::PMB_QUEUE_KESEHATAN);
        $role = Role::firstOrCreate(['name' => 'Petugas Medis', 'guard_name' => AdminPermissions::GUARD]);
        $role->givePermissionTo(AdminPermissions::DASHBOARD_VIEW, AdminPermissions::PMB_QUEUE_KESEHATAN, AdminPermissions::KESEHATAN_REVIEW);
        $officer->assignRole($role);

        $this->actingAs($officer, 'admin')
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.pmb-queues.officer.kesehatan', $session));
    }

    public function test_interview_only_officer_dashboard_redirects_to_session_picker(): void
    {
        $lecturer = $this->adminWith(AdminPermissions::PMB_QUEUE_WAWANCARA);

        $this->actingAs($lecturer, 'admin')
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.pmb-queues.interview.sessions'));
    }

    public function test_written_test_officer_has_dedicated_role_and_session_picker(): void
    {
        [$periode, $gelombang] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $role = Role::firstOrCreate([
            'name' => AdminPermissions::ROLE_PETUGAS_TES_TULIS,
            'guard_name' => AdminPermissions::GUARD,
        ]);
        $role->syncPermissions([AdminPermissions::PMB_QUEUE_TES_TULIS]);
        $officer = $this->adminWith(AdminPermissions::PMB_QUEUE_TES_TULIS);
        $officer->assignRole($role);

        $this->actingAs($officer, 'admin')
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.pmb-queues.written-test.sessions'));

        $this->actingAs($officer, 'admin')
            ->get(route('admin.pmb-queues.written-test.sessions'))
            ->assertOk()
            ->assertSee($session->name);

        $this->actingAs($officer, 'admin')
            ->get(route('admin.pmb-queues.show', $session))
            ->assertForbidden();

        $this->assertSame(
            [AdminPermissions::PMB_QUEUE_TES_TULIS],
            AdminPermissions::roleMap()[AdminPermissions::ROLE_PETUGAS_TES_TULIS]
        );
    }

    public function test_interview_queue_is_per_program_and_multiple_lecturers_can_take_different_participants(): void
    {
        [$periode, $gelombang, , $farmasi] = $this->masterData('S1 Farmasi');
        $session = $this->offlineSession($periode, $gelombang);
        $farmasiKaryawan = Jurusan::create(['nama_jurusan' => 'S1 Farmasi Karyawan']);
        $interviewerPrograms = Jurusan::interviewAccountOptions();
        $this->assertTrue($interviewerPrograms->contains('id', $farmasi->id));
        $this->assertFalse($interviewerPrograms->contains('id', $farmasiKaryawan->id));

        $participants = collect([
            $this->participant($periode, $gelombang, $farmasi),
            $this->participant($periode, $gelombang, $farmasiKaryawan),
        ]);
        $stages = $this->workflow->ensureDefaultStages($session);
        $tesTulisStage = $stages->firstWhere('key', PmbQueueStage::KEY_TES_TULIS);
        $healthStage = $stages->firstWhere('key', PmbQueueStage::KEY_TES_KESEHATAN);
        $interviewStage = $stages->firstWhere('key', PmbQueueStage::KEY_WAWANCARA_KAPRODI);
        $lecturerA = $this->adminWith(AdminPermissions::PMB_QUEUE_WAWANCARA);
        $lecturerB = $this->adminWith(AdminPermissions::PMB_QUEUE_WAWANCARA);
        $lecturerA->update(['jurusan_id' => $farmasi->id]);
        $lecturerB->update(['jurusan_id' => $farmasi->id]);
        $roomA = $this->interviewDesk($session, $interviewStage, $lecturerA, 1);
        $roomB = $this->interviewDesk($session, $interviewStage, $lecturerB, 2);

        $participants->each(function (User $participant) use ($session, $tesTulisStage, $healthStage): void {
            $queue = $this->workflow->checkIn($this->queueFor($session, $participant));
            $this->workflow->updateStep($queue->fresh(['stageSteps.stage'])->stageSteps->firstWhere('stage_id', $tesTulisStage->id), 'complete_test_tulis');
            $this->workflow->callNext($session, $healthStage);
            $this->workflow->updateStep($queue->fresh(['stageSteps.stage'])->stageSteps->firstWhere('stage_id', $healthStage->id), 'complete_health');
        });

        $first = $this->workflow->autoDispatchForOfficer($session, PmbQueueStage::KEY_WAWANCARA_KAPRODI, $lecturerA->id);
        $second = $this->workflow->autoDispatchForOfficer($session, PmbQueueStage::KEY_WAWANCARA_KAPRODI, $lecturerB->id);

        $this->assertNotNull($first);
        $this->assertNotNull($second);
        $this->assertNotSame($first->queue_id, $second->queue_id);
        $this->assertSame($lecturerA->id, $first->assigned_officer_id);
        $this->assertSame($lecturerB->id, $second->assigned_officer_id);
        $this->assertSame($roomA->id, $first->room_id);
        $this->assertSame($roomB->id, $second->room_id);
        $this->assertEqualsCanonicalizing(
            [$farmasi->id, $farmasiKaryawan->id],
            [$first->queue->user->jurusan_id, $second->queue->user->jurusan_id]
        );

        $publicBoard = $this->workflow->boardPayload($session, false);
        $this->assertCount(1, $publicBoard['interviews']);
        $this->assertSame('S1 Farmasi', $publicBoard['interviews'][0]['name']);
        $this->assertContains($farmasi->id, $publicBoard['interviews'][0]['jurusan_ids']);
        $this->assertContains($farmasiKaryawan->id, $publicBoard['interviews'][0]['jurusan_ids']);
        $this->assertCount(2, $publicBoard['interviews'][0]['rooms']);

        $wawancara = WawancaraPmb::create([
            'calon_mahasiswa_id' => $first->queue->user_id,
            'status' => 'submitted',
        ]);
        $lecturerB->givePermissionTo(AdminPermissions::WAWANCARA_REVIEW);

        $this->actingAs($lecturerB, 'admin')
            ->post(route('admin.wawancara.review', $wawancara), [
                'queue_uuid' => $first->queue->uuid,
                'kesimpulan_kaprodi' => 'Peserta ini bukan penugasan meja dosen yang sedang login.',
                'rekomendasi' => 'direkomendasikan',
            ])
            ->assertForbidden();
        $this->assertSame('submitted', $wawancara->fresh()->status);
    }

    public function test_wawancara_review_form_completes_queue_and_auto_dispatches_next_participant(): void
    {
        [$periode, $gelombang, , $farmasi] = $this->masterData('S1 Farmasi');
        $session = $this->offlineSession($periode, $gelombang);
        $farmasiKaryawan = Jurusan::create(['nama_jurusan' => 'S1 Farmasi Karyawan']);
        $participants = collect([
            $this->participant($periode, $gelombang, $farmasi),
            $this->participant($periode, $gelombang, $farmasiKaryawan),
        ]);
        $stages = $this->workflow->ensureDefaultStages($session);
        $tesTulisStage = $stages->firstWhere('key', PmbQueueStage::KEY_TES_TULIS);
        $healthStage = $stages->firstWhere('key', PmbQueueStage::KEY_TES_KESEHATAN);
        $interviewStage = $stages->firstWhere('key', PmbQueueStage::KEY_WAWANCARA_KAPRODI);
        $lecturer = $this->adminWith(AdminPermissions::PMB_QUEUE_WAWANCARA);
        $lecturer->givePermissionTo(AdminPermissions::WAWANCARA_REVIEW);
        $lecturer->update(['jurusan_id' => $farmasi->id]);
        $this->interviewDesk($session, $interviewStage, $lecturer, 1);

        $participants->each(function (User $participant) use ($session, $tesTulisStage, $healthStage): void {
            $queue = $this->workflow->checkIn($this->queueFor($session, $participant));
            $this->workflow->updateStep($queue->fresh(['stageSteps.stage'])->stageSteps->firstWhere('stage_id', $tesTulisStage->id), 'complete_test_tulis');
            $this->workflow->callNext($session, $healthStage);
            $this->workflow->updateStep($queue->fresh(['stageSteps.stage'])->stageSteps->firstWhere('stage_id', $healthStage->id), 'complete_health');

            WawancaraPmb::create([
                'calon_mahasiswa_id' => $participant->id,
                'jawaban_1' => 'Jawaban calon mahasiswa',
                'jawaban_2' => 'Jawaban calon mahasiswa',
                'status' => 'submitted',
            ]);
        });

        $first = $this->workflow->autoDispatchForOfficer($session, PmbQueueStage::KEY_WAWANCARA_KAPRODI, $lecturer->id);
        $wawancara = WawancaraPmb::where('calon_mahasiswa_id', $first->queue->user_id)->firstOrFail();

        $this->actingAs($lecturer, 'admin')
            ->post(route('admin.wawancara.review', $wawancara), [
                'queue_uuid' => $first->queue->uuid,
                'kesimpulan_kaprodi' => 'Peserta layak diterima berdasarkan hasil wawancara.',
                'rekomendasi' => 'direkomendasikan',
            ])
            ->assertRedirect(route('admin.pmb-queues.officer.wawancara', $session));

        $this->assertDatabaseHas('pmb_queue_stage_steps', [
            'id' => $first->id,
            'status' => PmbQueueStageStep::STATUS_COMPLETED,
        ]);

        $next = PmbQueueStageStep::where('stage_id', $interviewStage->id)
            ->where('assigned_officer_id', $lecturer->id)
            ->where('status', PmbQueueStageStep::STATUS_PROCESSING)
            ->first();

        $this->assertNotNull($next);
        $this->assertNotSame($first->queue_id, $next->queue_id);
    }

    public function test_public_board_payload_does_not_expose_private_participant_data(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $participant = $this->participant($periode, $gelombang, $jurusan);
        $participant->update(['name' => 'Muhamad Rafa Aryasatya Wijayakusumah']);
        $waitingParticipant = $this->participant($periode, $gelombang, $jurusan);
        $waitingParticipant->update(['name' => 'Alya']);
        $queuedParticipant = $this->participant($periode, $gelombang, $jurusan);
        $queuedParticipant->update(['name' => 'Budi Santoso']);
        $queue = $this->workflow->checkIn($this->queueFor($session, $participant));
        $waitingQueue = $this->workflow->checkIn($this->queueFor($session, $waitingParticipant));
        $queuedQueue = $this->workflow->checkIn($this->queueFor($session, $queuedParticipant));
        $tesTulisStage = $this->workflow->ensureDefaultStages($session)->firstWhere('key', PmbQueueStage::KEY_TES_TULIS);
        $healthStage = $this->workflow->ensureDefaultStages($session)->firstWhere('key', PmbQueueStage::KEY_TES_KESEHATAN);

        $this->workflow->updateStep($queue->fresh(['stageSteps.stage'])->stageSteps->firstWhere('stage_id', $tesTulisStage->id), 'complete_test_tulis');
        $this->workflow->updateStep($waitingQueue->fresh(['stageSteps.stage'])->stageSteps->firstWhere('stage_id', $tesTulisStage->id), 'complete_test_tulis');
        $this->workflow->updateStep($queuedQueue->fresh(['stageSteps.stage'])->stageSteps->firstWhere('stage_id', $tesTulisStage->id), 'complete_test_tulis');
        $this->workflow->callNext($session, $healthStage);
        $this->workflow->callNext($session, $healthStage);

        $payload = $this->workflow->boardPayload($session, false);
        $encoded = json_encode($payload);
        $currentCall = collect($payload['health']['current_calls'])->firstWhere('queue_id', $queue->id);
        $singleNameCall = collect($payload['health']['current_calls'])->firstWhere('queue_id', $waitingQueue->id);
        $waitingCall = collect($payload['health']['waiting'])->firstWhere('queue_id', $queuedQueue->id);

        $this->assertStringNotContainsString($queue->user->name, $encoded);
        $this->assertStringNotContainsString($queue->user->email, $encoded);
        $this->assertStringContainsString($queue->fresh()->queue_code, $encoded);
        $this->assertSame('Muhamad Rafa W.', $currentCall['display_name']);
        $this->assertSame('Muhamad Rafa', $currentCall['spoken_name']);
        $this->assertSame('Alya', $singleNameCall['display_name']);
        $this->assertSame('Alya', $singleNameCall['spoken_name']);
        $this->assertArrayNotHasKey('participant', $currentCall);
        $this->assertArrayNotHasKey('display_name', $waitingCall);
        $this->assertArrayNotHasKey('spoken_name', $waitingCall);
    }

    private function masterData(string $jurusanName = 'D3 Kebidanan'): array
    {
        $periode = Periode::forceCreate([
            'tgl_mulai' => now()->toDateString(),
            'deskripsi' => 'Periode Test ' . uniqid(),
            'linked' => 'periode-test-' . uniqid(),
            'file_lulus' => '',
            'status_periode' => 'aktif',
        ]);
        $gelombang1 = Gelombang::create([
            'periode_id' => $periode->id,
            'nama_gelombang' => 'Gelombang 1 ' . uniqid(),
            'tgl_mulai' => now()->toDateString(),
            'tgl_selesai' => now()->addDays(7)->toDateString(),
            'status_gelombang' => 'aktif',
        ]);
        $gelombang3 = Gelombang::create([
            'periode_id' => $periode->id,
            'nama_gelombang' => 'Gelombang 3 ' . uniqid(),
            'tgl_mulai' => now()->addDays(14)->toDateString(),
            'tgl_selesai' => now()->addDays(21)->toDateString(),
            'status_gelombang' => 'aktif',
        ]);
        $jurusan = Jurusan::create(['nama_jurusan' => $jurusanName]);

        return [$periode, $gelombang1, $gelombang3, $jurusan];
    }

    private function offlineSession(Periode $periode, Gelombang $gelombang): PmbTestSession
    {
        $session = PmbTestSession::create([
            'name' => 'Sesi Offline ' . uniqid(),
            'type' => PmbTestSession::TYPE_CAMPURAN,
            'periode_id' => $periode->id,
            'gelombang_id' => $gelombang->id,
            'starts_at' => now()->addDay(),
            'status' => PmbTestSession::STATUS_OPEN,
        ]);

        $this->workflow->ensureDefaultStages($session);

        return $session;
    }

    private function participant(Periode $periode, Gelombang $gelombang, Jurusan $jurusan, PmbStatus $status = PmbStatus::Verified): User
    {
        return User::factory()->create([
            'role' => User::USER_ROLE,
            'periode_id' => $periode->id,
            'gelombang_id' => $gelombang->id,
            'jurusan_id' => $jurusan->id,
            'status_pemb' => $status->value,
            'code' => 'T' . substr(uniqid(), -8),
        ]);
    }

    private function selectionReadyParticipant(Periode $periode, Gelombang $gelombang, Jurusan $jurusan): User
    {
        $user = $this->participant($periode, $gelombang, $jurusan);
        $user->update([
            'status_biodata' => 1,
            'status_berkas' => 1,
            'img_kk' => 'uploads/kk/test.jpg',
            'img_ktp' => 'uploads/ktp/test.jpg',
            'img_bukti' => 'uploads/bukti/test.jpg',
        ]);

        return $user->refresh();
    }

    private function queueFor(PmbTestSession $session, User $participant): PmbOfflineQueue
    {
        return PmbOfflineQueue::firstOrCreate([
            'session_id' => $session->id,
            'user_id' => $participant->id,
        ], [
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
        ]);
    }

    private function adminWith(string $permission): Admin
    {
        return $this->adminWithPermissions([$permission]);
    }

    private function adminWithPermissions(array $permissions): Admin
    {
        $admin = Admin::create([
            'name' => 'Admin Test ' . uniqid(),
            'email' => uniqid('admin') . '@example.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $admin->givePermissionTo($permissions);

        return $admin;
    }

    public function test_online_assignment_has_no_qr_and_cannot_check_in(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $session->update(['starts_at' => now()->subMinute()]);
        $queue = PmbOfflineQueue::create([
            'session_id' => $session->id,
            'user_id' => $this->selectionReadyParticipant($periode, $gelombang, $jurusan)->id,
            'selection_mode' => PmbOfflineQueue::MODE_ONLINE,
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
        ]);

        $this->assertNull($queue->qr_token_hash);

        try {
            $queue->signedCheckInUrl();
            $this->fail('Peserta online tidak boleh memiliki signed check-in URL.');
        } catch (\DomainException $exception) {
            $this->assertStringContainsString('tidak menggunakan QR', $exception->getMessage());
        }

        $this->actingAs($this->adminWith(AdminPermissions::PMB_EDIT), 'admin')
            ->post(route('admin.pmb-queues.check-in', $session), ['code' => $queue->uuid])
            ->assertSessionHas('error');
        $this->assertNull($queue->fresh()->checked_in_at);

        $this->expectException(\DomainException::class);
        $this->workflow->checkIn($queue);
    }

    public function test_online_stages_are_parallel_and_exempt_written_test_is_skipped(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $queue = PmbOfflineQueue::create([
            'session_id' => $session->id,
            'user_id' => $this->selectionReadyParticipant($periode, $gelombang, $jurusan)->id,
            'selection_mode' => PmbOfflineQueue::MODE_ONLINE,
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
        ]);
        $this->workflow->markQueueTesTulisRequirement($queue, false);

        $steps = app(PmbOnlineSelectionService::class)->ensureSteps($queue->fresh(['session']))->keyBy(fn ($step) => $step->stage->key);

        $this->assertSame(PmbQueueStageStep::STATUS_SKIPPED, $steps[PmbQueueStage::KEY_PEMBERKASAN]->status);
        $this->assertSame(PmbQueueStageStep::STATUS_SKIPPED, $steps[PmbQueueStage::KEY_TES_TULIS]->status);
        $this->assertSame(PmbQueueStageStep::STATUS_PENDING, $steps[PmbQueueStage::KEY_TES_KESEHATAN]->status);
        $this->assertSame(PmbQueueStageStep::STATUS_PENDING, $steps[PmbQueueStage::KEY_WAWANCARA_KAPRODI]->status);
    }

    public function test_online_selection_completes_only_after_all_required_progress(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $queue = PmbOfflineQueue::create([
            'session_id' => $session->id,
            'user_id' => $this->selectionReadyParticipant($periode, $gelombang, $jurusan)->id,
            'selection_mode' => PmbOfflineQueue::MODE_ONLINE,
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
        ]);
        $online = app(PmbOnlineSelectionService::class);
        $online->ensureSteps($queue);
        $online->markWrittenCompleted($queue);
        $online->markAnamnesisSubmitted($queue);
        $online->markInterviewFormSubmitted($queue);
        $online->markInterviewCompleted($queue);

        $this->assertSame(PmbOfflineQueue::OVERALL_IN_PROCESS, $queue->fresh()->overall_status);

        $online->markHealthLetterUploaded($queue);
        $this->assertSame(PmbOfflineQueue::OVERALL_COMPLETED, $queue->fresh()->overall_status);
        $this->assertSame(PmbOfflineQueue::STATUS_COMPLETED, $queue->fresh()->status);
    }

    public function test_health_deadline_is_h_plus_seven_and_late_upload_is_allowed(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $notifiedAt = now()->subDays(8)->startOfMinute();
        $queue = PmbOfflineQueue::create([
            'session_id' => $session->id,
            'user_id' => $this->selectionReadyParticipant($periode, $gelombang, $jurusan)->id,
            'selection_mode' => PmbOfflineQueue::MODE_ONLINE,
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
            'health_notified_at' => $notifiedAt,
        ]);

        $this->assertTrue($queue->healthLetterDueAt()->equalTo($notifiedAt->copy()->addDays(7)));
        $this->assertTrue($queue->isHealthLetterLate(now()));
        app(PmbOnlineSelectionService::class)->markHealthLetterUploaded($queue);
        $this->assertDatabaseHas('pmb_queue_stage_steps', [
            'queue_id' => $queue->id,
            'status' => PmbQueueStageStep::STATUS_COMPLETED,
        ]);
    }

    public function test_online_waiting_interview_does_not_appear_on_offline_board(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $queue = PmbOfflineQueue::create([
            'session_id' => $session->id,
            'user_id' => $this->selectionReadyParticipant($periode, $gelombang, $jurusan)->id,
            'selection_mode' => PmbOfflineQueue::MODE_ONLINE,
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
        ]);
        app(PmbOnlineSelectionService::class)->markInterviewFormSubmitted($queue);

        $payload = $this->workflow->boardPayload($session, false);
        $this->assertStringNotContainsString($queue->uuid, json_encode($payload));
        $this->assertSame(0, collect($payload['stages'])->sum(fn ($lane) => data_get($lane, 'counts.waiting', 0)));
    }

    public function test_admin_assigns_online_interviewer_and_manual_order_from_matching_program_pool(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData('S1 Farmasi Karyawan');
        $regular = Jurusan::create(['nama_jurusan' => 'S1 Farmasi']);
        $session = $this->offlineSession($periode, $gelombang);
        $queue = PmbOfflineQueue::create([
            'session_id' => $session->id,
            'user_id' => $this->selectionReadyParticipant($periode, $gelombang, $jurusan)->id,
            'selection_mode' => PmbOfflineQueue::MODE_ONLINE,
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
        ]);
        app(PmbOnlineSelectionService::class)->ensureSteps($queue);
        $lecturer = $this->adminWith(AdminPermissions::WAWANCARA_REVIEW);
        $lecturer->update(['jurusan_id' => $regular->id]);
        $stage = $session->stages()->where('key', PmbQueueStage::KEY_WAWANCARA_KAPRODI)->firstOrFail();
        $room = $this->interviewDesk($session, $stage, $lecturer, 91);

        $this->actingAs($this->adminWith(AdminPermissions::PMB_EDIT), 'admin')
            ->post(route('admin.pmb-queues.online.interview-assignment', $queue), [
                'room_id' => $room->id,
                'stage_queue_number' => 17,
            ])->assertRedirect();

        $step = $queue->stageSteps()->where('stage_id', $stage->id)->firstOrFail();
        $this->assertSame($lecturer->id, $step->assigned_officer_id);
        $this->assertSame($room->id, $step->room_id);
        $this->assertSame(17, $step->stage_queue_number);
    }

    public function test_online_lecturer_claims_submitted_interview_by_matching_program_pool(): void
    {
        [$periode, $gelombang, , $farmasi] = $this->masterData('S1 Farmasi');
        $gizi = Jurusan::create(['nama_jurusan' => 'S1 Gizi']);
        $session = $this->offlineSession($periode, $gelombang);
        $scheduledSession = $this->offlineSession($periode, $gelombang);
        $scheduledSession->update([
            'name' => 'Sesi Belum Check-in Dibuka ' . uniqid(),
            'status' => PmbTestSession::STATUS_SCHEDULED,
        ]);

        $farmasiQueue = PmbOfflineQueue::create([
            'session_id' => $session->id,
            'user_id' => $this->selectionReadyParticipant($periode, $gelombang, $farmasi)->id,
            'selection_mode' => PmbOfflineQueue::MODE_ONLINE,
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
        ]);
        $giziQueue = PmbOfflineQueue::create([
            'session_id' => $session->id,
            'user_id' => $this->selectionReadyParticipant($periode, $gelombang, $gizi)->id,
            'selection_mode' => PmbOfflineQueue::MODE_ONLINE,
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
        ]);
        $draftQueue = PmbOfflineQueue::create([
            'session_id' => $session->id,
            'user_id' => $this->selectionReadyParticipant($periode, $gelombang, $farmasi)->id,
            'selection_mode' => PmbOfflineQueue::MODE_ONLINE,
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
        ]);

        app(PmbOnlineSelectionService::class)->markInterviewFormSubmitted($farmasiQueue);
        app(PmbOnlineSelectionService::class)->markInterviewFormSubmitted($giziQueue);
        app(PmbOnlineSelectionService::class)->ensureSteps($draftQueue);

        $farmasiInterview = WawancaraPmb::create([
            'calon_mahasiswa_id' => $farmasiQueue->user_id,
            'jawaban_1' => 'Saya memilih Farmasi karena minat pada obat dan pelayanan.',
            'status' => 'submitted',
        ]);
        WawancaraPmb::create([
            'calon_mahasiswa_id' => $giziQueue->user_id,
            'jawaban_1' => 'Saya memilih Gizi karena minat pada nutrisi.',
            'status' => 'submitted',
        ]);
        WawancaraPmb::create([
            'calon_mahasiswa_id' => $draftQueue->user_id,
            'jawaban_1' => 'Masih draft.',
            'status' => 'draft',
        ]);

        $lecturer = $this->adminWith(AdminPermissions::PMB_QUEUE_WAWANCARA);
        $lecturer->update(['jurusan_id' => $farmasi->id]);
        $otherFarmasiLecturer = $this->adminWith(AdminPermissions::PMB_QUEUE_WAWANCARA);
        $otherFarmasiLecturer->update(['jurusan_id' => $farmasi->id]);

        $this->actingAs($lecturer, 'admin')
            ->get(route('admin.pmb-queues.interview.sessions'))
            ->assertOk()
            ->assertSee('Wawancara Online')
            ->assertSee('Buka Wawancara Offline')
            ->assertDontSee($scheduledSession->name);

        $this->actingAs($lecturer, 'admin')
            ->get(route('admin.pmb-queues.officer.wawancara-online', $session))
            ->assertOk()
            ->assertSee($farmasiQueue->user->name)
            ->assertSee('Mulai & Review', false)
            ->assertDontSee($giziQueue->user->name)
            ->assertDontSee($draftQueue->user->name);

        $this->actingAs($lecturer, 'admin')
            ->post(route('admin.pmb-queues.online.interview-start', $farmasiQueue))
            ->assertRedirect(route('admin.wawancara.review.form', ['wawancara' => $farmasiInterview, 'queue_uuid' => $farmasiQueue->uuid]));

        $step = $farmasiQueue->fresh(['stageSteps.stage'])->stageSteps
            ->first(fn ($item) => $item->stage?->key === PmbQueueStage::KEY_WAWANCARA_KAPRODI);
        $this->assertSame($lecturer->id, $step->assigned_officer_id);
        $this->assertSame(PmbQueueStageStep::STATUS_PROCESSING, $step->status);

        $this->actingAs($otherFarmasiLecturer, 'admin')
            ->get(route('admin.wawancara.review.form', ['wawancara' => $farmasiInterview, 'queue_uuid' => $farmasiQueue->uuid]))
            ->assertForbidden();

        $this->actingAs($lecturer, 'admin')
            ->get(route('admin.wawancara.review.form', ['wawancara' => $farmasiInterview, 'queue_uuid' => $farmasiQueue->uuid]))
            ->assertOk()
            ->assertSee('Hasil Wawancara Online')
            ->assertSee('Jawaban Form Mahasiswa')
            ->assertSee('Saya memilih Farmasi karena minat pada obat dan pelayanan.')
            ->assertDontSee('Hasil Wawancara Offline');

        $this->actingAs($lecturer, 'admin')
            ->post(route('admin.wawancara.review', $farmasiInterview), [
                'queue_uuid' => $farmasiQueue->uuid,
                'kesimpulan_kaprodi' => 'Peserta memiliki motivasi kuat dan komunikasi baik.',
                'rekomendasi' => 'direkomendasikan',
            ])
            ->assertRedirect(route('admin.pmb-queues.officer.wawancara-online', $session));

        $this->assertDatabaseHas('wawancara_pmb', [
            'id' => $farmasiInterview->id,
            'kaprodi_id' => $lecturer->id,
            'rekomendasi' => 'direkomendasikan',
            'status' => 'reviewed',
        ]);
        $this->assertSame(PmbQueueStageStep::STATUS_COMPLETED, $step->fresh()->status);
    }

    public function test_online_selection_manager_only_handles_online_participants(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $offlineQueue = $this->queueFor($session, $this->participant($periode, $gelombang, $jurusan));
        $onlineQueue = PmbOfflineQueue::create([
            'session_id' => $session->id,
            'user_id' => $this->participant($periode, $gelombang, $jurusan)->id,
            'selection_mode' => PmbOfflineQueue::MODE_ONLINE,
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
        ]);
        app(PmbOnlineSelectionService::class)->ensureSteps($onlineQueue);
        $manager = $this->adminWithPermissions([
            AdminPermissions::PMB_QUEUE_ONLINE_MANAGE,
            AdminPermissions::PMB_ONLINE_TES_TULIS_MANAGE,
            AdminPermissions::PMB_ONLINE_SOAL_MANAGE,
            AdminPermissions::PMB_ONLINE_HASIL_TES_VIEW,
            AdminPermissions::PMB_ONLINE_KESEHATAN_MANAGE,
            AdminPermissions::PMB_ONLINE_WAWANCARA_MANAGE,
        ]);

        $this->actingAs($manager, 'admin')
            ->get(route('admin.pmb-queues.show', $session))
            ->assertOk()
            ->assertSee($onlineQueue->user->name)
            ->assertDontSee($offlineQueue->user->name)
            ->assertSee('Atur Jadwal Sesi')
            ->assertSee('Tes Tulis & Soal', false)
            ->assertSee('Hasil Tes Tulis')
            ->assertSee('Kesehatan Online')
            ->assertSee('Wawancara Online')
            ->assertSee('Diberitahukan H+7')
            ->assertDontSee('Check-in Kedatangan')
            ->assertDontSee('Bulk Check-in')
            ->assertDontSee('Tambah Peserta')
            ->assertDontSee('Kartu / QR Pass')
            ->assertDontSee('href="#loket"', false)
            ->assertDontSee('Board');

        $this->actingAs($manager, 'admin')->get(route('admin.pmb-queues.edit', $session))->assertOk();
        $this->actingAs($manager, 'admin')->get(route('admin.tes-tulis.index'))->assertOk();
        $this->actingAs($manager, 'admin')->get(route('admin.hasil-tes.index'))->assertOk();
        $this->actingAs($manager, 'admin')->get(route('admin.tes-kesehatan.index'))->assertOk();
        $this->actingAs($manager, 'admin')->get(route('admin.wawancara.index'))->assertOk();

        $this->actingAs($this->adminWith(AdminPermissions::PMB_EDIT), 'admin')
            ->get(route('admin.pmb-queues.show', $session))
            ->assertOk()
            ->assertSee('Board')
            ->assertSee('Check-in Kedatangan')
            ->assertSee('Loket');

        $this->actingAs($manager, 'admin')
            ->post(route('admin.pmb-queues.online.health-notified', $session), [
                'queue_ids' => [$offlineQueue->id, $onlineQueue->id],
            ])
            ->assertRedirect();

        $this->assertNull($offlineQueue->fresh()->health_notified_at);
        $this->assertNotNull($onlineQueue->fresh()->health_notified_at);
    }

    public function test_online_selection_manager_sees_only_online_written_health_and_interview_results(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $offlineQueue = $this->queueFor($session, $this->participant($periode, $gelombang, $jurusan));
        $onlineQueue = PmbOfflineQueue::create([
            'session_id' => $session->id,
            'user_id' => $this->participant($periode, $gelombang, $jurusan)->id,
            'selection_mode' => PmbOfflineQueue::MODE_ONLINE,
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
        ]);
        app(PmbOnlineSelectionService::class)->ensureSteps($onlineQueue);
        $tesTulis = TesTulis::create([
            'nama_tes' => 'Tes Online ' . uniqid(),
            'durasi_menit' => 60,
            'skor_lulus' => 60,
            'status_aktif' => true,
        ]);

        $offlineResult = HasilTesTulis::create([
            'user_id' => $offlineQueue->user_id,
            'tes_tulis_id' => $tesTulis->id,
            'skor' => 55,
            'jawaban_benar' => 11,
            'jawaban_salah' => 9,
            'selesai' => true,
            'status' => 'selesai',
            'waktu_mulai' => now()->subHour(),
            'waktu_selesai' => now(),
        ]);
        $onlineResult = HasilTesTulis::create([
            'user_id' => $onlineQueue->user_id,
            'tes_tulis_id' => $tesTulis->id,
            'skor' => 85,
            'jawaban_benar' => 17,
            'jawaban_salah' => 3,
            'selesai' => true,
            'status' => 'selesai',
            'waktu_mulai' => now()->subHour(),
            'waktu_selesai' => now(),
        ]);
        $offlineHealth = TesKesehatanAnamnesa::create([
            'user_id' => $offlineQueue->user_id,
            'tanggal_pengisian' => now(),
            'status' => 'belum diperiksa',
        ]);
        $onlineHealth = TesKesehatanAnamnesa::create([
            'user_id' => $onlineQueue->user_id,
            'tanggal_pengisian' => now(),
            'status' => 'belum diperiksa',
        ]);
        $offlineInterview = WawancaraPmb::create([
            'calon_mahasiswa_id' => $offlineQueue->user_id,
            'jawaban_1' => 'Jawaban wawancara offline.',
            'status' => 'submitted',
        ]);
        $onlineInterview = WawancaraPmb::create([
            'calon_mahasiswa_id' => $onlineQueue->user_id,
            'jawaban_1' => 'Jawaban wawancara online.',
            'status' => 'submitted',
        ]);

        $manager = $this->adminWithPermissions([
            AdminPermissions::PMB_QUEUE_ONLINE_MANAGE,
            AdminPermissions::PMB_ONLINE_HASIL_TES_VIEW,
            AdminPermissions::PMB_ONLINE_KESEHATAN_MANAGE,
            AdminPermissions::PMB_ONLINE_WAWANCARA_MANAGE,
        ]);

        $this->actingAs($manager, 'admin')
            ->get(route('admin.hasil-tes.index'))
            ->assertOk()
            ->assertSee($onlineQueue->user->name)
            ->assertDontSee($offlineQueue->user->name);

        $this->actingAs($manager, 'admin')
            ->get(route('admin.hasil-tes.show', $onlineResult))
            ->assertOk();

        $this->actingAs($manager, 'admin')
            ->get(route('admin.hasil-tes.show', $offlineResult))
            ->assertNotFound();

        $this->actingAs($manager, 'admin')
            ->get(route('admin.tes-kesehatan.index'))
            ->assertOk()
            ->assertSee($onlineQueue->user->name)
            ->assertDontSee($offlineQueue->user->name);

        $this->actingAs($manager, 'admin')
            ->get(route('admin.tes-kesehatan.show', $onlineHealth))
            ->assertOk()
            ->assertDontSee('Form Pemeriksaan Fisik');

        $this->actingAs($manager, 'admin')
            ->post(route('admin.tes-kesehatan.store-pemeriksaan', $onlineHealth), [
                'nama_pemeriksa' => $manager->name,
                'tanggal_pemeriksaan' => now()->toDateString(),
            ])
            ->assertForbidden();

        $this->actingAs($manager, 'admin')
            ->put(route('admin.tes-kesehatan.update-status', $onlineHealth), ['status' => 'lulus'])
            ->assertRedirect(route('admin.tes-kesehatan.show', $onlineHealth));
        $this->assertSame('lulus', $onlineHealth->fresh()->status);

        $this->actingAs($manager, 'admin')
            ->put(route('admin.tes-kesehatan.update-status', $offlineHealth), ['status' => 'lulus'])
            ->assertNotFound();

        $this->actingAs($manager, 'admin')
            ->get(route('admin.wawancara.index'))
            ->assertOk()
            ->assertSee($onlineQueue->user->name)
            ->assertDontSee($offlineQueue->user->name);

        $this->actingAs($manager, 'admin')
            ->get(route('admin.wawancara.show', $onlineInterview))
            ->assertOk();

        $this->actingAs($manager, 'admin')
            ->get(route('admin.wawancara.show', $offlineInterview))
            ->assertNotFound();

        $this->actingAs($manager, 'admin')
            ->get(route('admin.wawancara.review.form', $onlineInterview))
            ->assertForbidden();
    }

    public function test_online_selection_manager_controls_online_stage_windows(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $queue = PmbOfflineQueue::create([
            'session_id' => $session->id,
            'user_id' => $this->selectionReadyParticipant($periode, $gelombang, $jurusan)->id,
            'selection_mode' => PmbOfflineQueue::MODE_ONLINE,
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
        ]);
        app(PmbOnlineSelectionService::class)->ensureSteps($queue);
        $manager = $this->adminWith(AdminPermissions::PMB_QUEUE_ONLINE_MANAGE);

        $payload = [
            'name' => $session->name,
            'type' => $session->type,
            'periode_id' => $periode->id,
            'gelombang_id' => $gelombang->id,
            'tes_tulis_id' => '',
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'health_starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'health_ends_at' => now()->addDays(4)->format('Y-m-d H:i:s'),
            'interview_starts_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'interview_ends_at' => now()->addDays(6)->format('Y-m-d H:i:s'),
            'zoom_url' => 'https://zoom.us/j/123456789',
            'checkin_open_at' => '',
            'checkin_close_at' => '',
            'status' => PmbTestSession::STATUS_OPEN,
            'notes' => 'Jadwal online diatur PJ Seleksi Online.',
        ];

        $this->actingAs($manager, 'admin')
            ->put(route('admin.pmb-queues.update', $session), $payload)
            ->assertRedirect(route('admin.pmb-queues.show', $session));

        $session->refresh();
        $this->assertSame($payload['health_starts_at'], $session->health_starts_at?->format('Y-m-d H:i:s'));
        $this->assertSame($payload['health_ends_at'], $session->health_ends_at?->format('Y-m-d H:i:s'));
        $this->assertSame($payload['interview_starts_at'], $session->interview_starts_at?->format('Y-m-d H:i:s'));
        $this->assertSame($payload['interview_ends_at'], $session->interview_ends_at?->format('Y-m-d H:i:s'));
        $this->assertFalse($session->isOnlineHealthOpen());
        $this->assertFalse($session->isOnlineInterviewFormOpen());

        $this->actingAs($queue->user)
            ->get(route('dashboard.tes-kesehatan.anamnesa.create'))
            ->assertRedirect(route('dashboard.profile.cetakKartu'));

        $this->actingAs($queue->user)
            ->get(route('dashboard.wawancara.index'))
            ->assertOk()
            ->assertSee('Form wawancara belum dibuka')
            ->assertDontSee('Kirim Wawancara');

        $this->actingAs($queue->user)
            ->post(route('dashboard.wawancara.submit'), [
                'jawaban_1' => 'Saya menceritakan diri dengan lengkap.',
                'jawaban_2' => 'Orang tua saya bekerja dan mendukung kuliah.',
                'kelebihan' => 'Tekun',
                'kekurangan' => 'Gugup',
                'jawaban_4' => 'Saya memilih jurusan ini karena sesuai minat.',
                'jawaban_5' => 'Saya memilih STIKes Bogor Husada karena reputasinya.',
                'jawaban_6' => 'Saya ingin menjadi tenaga kesehatan profesional.',
            ])
            ->assertRedirect(route('dashboard.profile.cetakKartu'));
    }

    public function test_online_selection_manager_can_manage_written_tests_and_questions(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        PmbOfflineQueue::create([
            'session_id' => $session->id,
            'user_id' => $this->participant($periode, $gelombang, $jurusan)->id,
            'selection_mode' => PmbOfflineQueue::MODE_ONLINE,
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
        ]);
        $manager = $this->adminWithPermissions([
            AdminPermissions::PMB_QUEUE_ONLINE_MANAGE,
            AdminPermissions::PMB_ONLINE_TES_TULIS_MANAGE,
            AdminPermissions::PMB_ONLINE_SOAL_MANAGE,
        ]);

        $this->assertContains(AdminPermissions::PMB_ONLINE_TES_TULIS_MANAGE, AdminPermissions::roleMap()[AdminPermissions::ROLE_PJ_SELEKSI_ONLINE]);
        $this->assertContains(AdminPermissions::PMB_ONLINE_SOAL_MANAGE, AdminPermissions::roleMap()[AdminPermissions::ROLE_PJ_SELEKSI_ONLINE]);

        $this->actingAs($manager, 'admin')
            ->get(route('admin.pmb-queues.index'))
            ->assertOk()
            ->assertSee('Tes Tulis & Soal Online', false);

        $this->actingAs($manager, 'admin')
            ->get(route('admin.pmb-queues.edit', $session))
            ->assertOk()
            ->assertSee('Tes Tulis Online Dibuka')
            ->assertSee('Kesehatan Online Dibuka')
            ->assertSee('Wawancara Online Dibuka');

        $this->actingAs($manager, 'admin')
            ->post(route('admin.tes-tulis.store'), [
                'nama_tes' => 'Tes Online PJ ' . uniqid(),
                'deskripsi' => 'Dikelola PJ Seleksi Online',
                'durasi_menit' => 45,
                'skor_lulus' => 70,
                'acak_soal' => 1,
                'acak_pilihan' => 1,
                'status_aktif' => 1,
            ])
            ->assertRedirect(route('admin.tes-tulis.index'));

        $tesTulis = TesTulis::where('nama_tes', 'like', 'Tes Online PJ%')->latest('id')->firstOrFail();

        $this->actingAs($manager, 'admin')
            ->get(route('admin.soal.index', $tesTulis))
            ->assertOk();

        $this->actingAs($manager, 'admin')
            ->post(route('admin.soal.store', $tesTulis), [
                'pertanyaan' => 'Apa alasan Anda memilih STIKes Bogor Husada?',
                'skor' => 5,
            ])
            ->assertRedirect(route('admin.soal.index', $tesTulis));

        $this->assertDatabaseHas('soal_tes', [
            'tes_tulis_id' => $tesTulis->id,
            'pertanyaan' => 'Apa alasan Anda memilih STIKes Bogor Husada?',
        ]);
    }

    public function test_lecturer_cannot_review_online_participant_assigned_to_another_lecturer(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $queue = PmbOfflineQueue::create([
            'session_id' => $session->id,
            'user_id' => $this->participant($periode, $gelombang, $jurusan)->id,
            'selection_mode' => PmbOfflineQueue::MODE_ONLINE,
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
        ]);
        $assigned = $this->adminWith(AdminPermissions::WAWANCARA_REVIEW);
        $assigned->update(['jurusan_id' => $jurusan->id]);
        $other = $this->adminWith(AdminPermissions::WAWANCARA_REVIEW);
        $other->update(['jurusan_id' => $jurusan->id]);
        $stage = $session->stages()->where('key', PmbQueueStage::KEY_WAWANCARA_KAPRODI)->firstOrFail();
        $room = $this->interviewDesk($session, $stage, $assigned, 92);
        $step = app(PmbOnlineSelectionService::class)->ensureSteps($queue)->firstWhere('stage_id', $stage->id);
        $step->update([
            'assigned_officer_id' => $assigned->id,
            'room_id' => $room->id,
            'status' => PmbQueueStageStep::STATUS_WAITING,
        ]);
        $wawancara = WawancaraPmb::create([
            'calon_mahasiswa_id' => $queue->user_id,
            'jawaban_1' => 'Jawaban peserta untuk bahan wawancara.',
            'status' => 'submitted',
        ]);

        $this->actingAs($other, 'admin')
            ->get(route('admin.wawancara.review.form', ['wawancara' => $wawancara, 'queue_uuid' => $queue->uuid]))
            ->assertForbidden();
    }

    public function test_latest_online_assignment_hides_stale_offline_card_and_qr(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $user = $this->selectionReadyParticipant($periode, $gelombang, $jurusan);
        $offline = $this->offlineSession($periode, $gelombang);
        $oldQueue = $this->queueFor($offline, $user);
        $oldQueue->update(['queue_code' => 'OLD-QR-999']);
        $online = $this->offlineSession($periode, $gelombang);
        $online->update(['name' => 'Seleksi Online Terbaru', 'starts_at' => now()->subMinute()]);
        PmbOfflineQueue::create([
            'session_id' => $online->id,
            'user_id' => $user->id,
            'selection_mode' => PmbOfflineQueue::MODE_ONLINE,
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.profile.cetakKartu'))
            ->assertOk()
            ->assertSee('Status PMB')
            ->assertSee('Timeline PMB')
            ->assertDontSee('Seleksi Online Terbaru')
            ->assertDontSee('OLD-QR-999')
            ->assertDontSee('Cetak Kartu Ujian');

        $this->actingAs($user)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('Buka Seleksi Online')
            ->assertSee(route('dashboard.selection.index'), false)
            ->assertDontSee('Seleksi Online Terbaru')
            ->assertDontSee('OLD-QR-999')
            ->assertDontSee('Cetak Kartu Ujian');

        $this->actingAs($user)
            ->get(route('dashboard.selection.index'))
            ->assertOk()
            ->assertSee('Seleksi Online Terbaru')
            ->assertDontSee('OLD-QR-999')
            ->assertDontSee('Cetak Kartu Ujian');
    }

    public function test_student_can_print_registered_offline_queue_card(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $student = $this->selectionReadyParticipant($periode, $gelombang, $jurusan);
        $session = $this->offlineSession($periode, $gelombang);
        $queue = $this->queueFor($session, $student);
        $queue->update([
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
            'queue_code' => 'FAR999',
        ]);

        $this->actingAs($student)
            ->get(route('dashboard.pmb-queue.show', $queue))
            ->assertOk()
            ->assertSee('Cetak Kartu')
            ->assertSee(route('dashboard.pmb-queue.print', $queue), false);

        $this->actingAs($student)
            ->get(route('dashboard.pmb-queue.print', $queue))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_student_cannot_print_unverified_other_or_online_queue_card(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $student = $this->selectionReadyParticipant($periode, $gelombang, $jurusan);
        $unverified = $this->selectionReadyParticipant($periode, $gelombang, $jurusan);
        $unverified->update(['status_pemb' => PmbStatus::MenungguValidasi->value]);
        $other = $this->selectionReadyParticipant($periode, $gelombang, $jurusan);

        $verifiedQueue = $this->queueFor($session, $student);
        $unverifiedQueue = $this->queueFor($session, $unverified);
        $onlineSession = $this->offlineSession($periode, $gelombang);
        $onlineQueue = PmbOfflineQueue::create([
            'session_id' => $onlineSession->id,
            'user_id' => $student->id,
            'selection_mode' => PmbOfflineQueue::MODE_ONLINE,
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
        ]);

        $this->actingAs($unverified)
            ->get(route('dashboard.pmb-queue.print', $unverifiedQueue))
            ->assertRedirect(route('dashboard.pmb-queue.show', $unverifiedQueue));

        $this->actingAs($other)
            ->get(route('dashboard.pmb-queue.print', $verifiedQueue))
            ->assertForbidden();

        $this->actingAs($student)
            ->get(route('dashboard.pmb-queue.print', $onlineQueue))
            ->assertNotFound();
    }

    public function test_student_online_stage_menus_are_visible_before_selection_opens(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $session->update([
            'starts_at' => now()->addDays(6),
            'tes_tulis_id' => TesTulis::create([
                'nama_tes' => 'Tes Online Mahasiswa ' . uniqid(),
                'durasi_menit' => 60,
                'skor_lulus' => 60,
                'status_aktif' => true,
            ])->id,
        ]);
        $student = $this->selectionReadyParticipant($periode, $gelombang, $jurusan);
        $queue = PmbOfflineQueue::create([
            'session_id' => $session->id,
            'user_id' => $student->id,
            'selection_mode' => PmbOfflineQueue::MODE_ONLINE,
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
        ]);
        app(PmbOnlineSelectionService::class)->ensureSteps($queue);

        $this->actingAs($queue->user)
            ->get(route('dashboard.profile.cetakKartu'))
            ->assertOk()
            ->assertSee('Status PMB')
            ->assertSee('Timeline PMB')
            ->assertDontSee('Seleksi Online')
            ->assertDontSee('Tes Tulis Online');

        $this->actingAs($queue->user)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('Buka Seleksi Online')
            ->assertSee(route('dashboard.selection.index'), false)
            ->assertDontSee('Tes Tulis Online');

        $this->actingAs($queue->user)
            ->get(route('dashboard.selection.index'))
            ->assertOk()
            ->assertSee('Seleksi Tes PMB')
            ->assertSee('Ringkasan Seleksi')
            ->assertSee('Tes Tulis')
            ->assertSee('Kesehatan Online')
            ->assertSee('Form Wawancara')
            ->assertDontSee('Status &amp; Kartu Ujian', false)
            ->assertSee(route('dashboard.user.tes-tulis.index'), false)
            ->assertSee(route('dashboard.tes-kesehatan.anamnesa.create'), false)
            ->assertSee(route('dashboard.wawancara.index'), false)
            ->assertSee('Seleksi dibuka');

        $this->actingAs($queue->user)
            ->get(route('dashboard.user.tes-tulis.index'))
            ->assertOk()
            ->assertSee('Belum Dibuka')
            ->assertSee('Tes tulis online dibuka')
            ->assertDontSee('Mulai Kerjakan');

        $this->actingAs($queue->user)
            ->get(route('dashboard.tes-tulis.show', $session->tes_tulis_id))
            ->assertRedirect(route('dashboard.user.tes-tulis.index'));

        $this->actingAs($queue->user)
            ->get(route('dashboard.tes-kesehatan.anamnesa.create'))
            ->assertOk();

        $this->actingAs($queue->user)
            ->get(route('dashboard.wawancara.index'))
            ->assertOk();
    }

    public function test_student_online_stage_menus_hide_written_test_when_exempt_before_selection_opens(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $session->update([
            'starts_at' => now()->addDays(6),
            'tes_tulis_id' => TesTulis::create([
                'nama_tes' => 'Tes Online Exempt ' . uniqid(),
                'durasi_menit' => 60,
                'skor_lulus' => 60,
                'status_aktif' => true,
            ])->id,
        ]);
        $student = $this->selectionReadyParticipant($periode, $gelombang, $jurusan);
        $queue = PmbOfflineQueue::create([
            'session_id' => $session->id,
            'user_id' => $student->id,
            'selection_mode' => PmbOfflineQueue::MODE_ONLINE,
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
        ]);
        $this->workflow->markQueueTesTulisRequirement($queue, false);
        app(PmbOnlineSelectionService::class)->ensureSteps($queue);

        $this->actingAs($queue->user)
            ->get(route('dashboard.profile.cetakKartu'))
            ->assertOk()
            ->assertSee('Status PMB')
            ->assertSee('Timeline PMB')
            ->assertDontSee('Seleksi Online')
            ->assertDontSee('Tes Tulis Online');

        $this->actingAs($queue->user)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('Buka Seleksi Online')
            ->assertSee(route('dashboard.selection.index'), false)
            ->assertDontSee('Tes Tulis Online');

        $this->actingAs($queue->user)
            ->get(route('dashboard.selection.index'))
            ->assertOk()
            ->assertSee('Seleksi Tes PMB')
            ->assertSee('Ringkasan Seleksi')
            ->assertDontSee(route('dashboard.user.tes-tulis.index'), false)
            ->assertSee(route('dashboard.tes-kesehatan.anamnesa.create'), false)
            ->assertSee(route('dashboard.wawancara.index'), false);
    }

    public function test_student_selection_summary_without_assignment_shows_empty_state(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $user = $this->participant($periode, $gelombang, $jurusan);

        $this->actingAs($user)
            ->get(route('dashboard.selection.index'))
            ->assertOk()
            ->assertSee('Seleksi Tes PMB')
            ->assertSee('Belum ada sesi seleksi aktif')
            ->assertDontSee('Seleksi Online');
    }

    public function test_student_written_test_requires_complete_manual_answers_but_accepts_partial_auto_submit(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $kategori = \App\Models\KategoriSoal::create([
            'nama_kategori' => 'Bahasa Inggris',
            'cerita_bacaan' => 'Read the following passage before answering the questions.',
        ]);
        $tesTulis = TesTulis::create([
            'nama_tes' => 'Tes Online Validasi ' . uniqid(),
            'deskripsi' => 'Validasi submit manual dan otomatis.',
            'durasi_menit' => 60,
            'skor_lulus' => 60,
            'status_aktif' => true,
        ]);
        $soalA = $tesTulis->soal()->create([
            'kategori_id' => $kategori->id,
            'pertanyaan' => 'Question one?',
            'skor' => 1,
        ]);
        $soalB = $tesTulis->soal()->create([
            'kategori_id' => $kategori->id,
            'pertanyaan' => 'Question two?',
            'skor' => 1,
        ]);
        $pilihanA = $soalA->pilihanJawaban()->create([
            'kode_pilihan' => 'A',
            'teks_pilihan' => 'Correct',
            'benar' => true,
            'urutan' => 1,
        ]);
        $soalB->pilihanJawaban()->create([
            'kode_pilihan' => 'A',
            'teks_pilihan' => 'Correct',
            'benar' => true,
            'urutan' => 1,
        ]);
        $session = $this->offlineSession($periode, $gelombang);
        $session->update([
            'tes_tulis_id' => $tesTulis->id,
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addHour(),
            'status' => PmbTestSession::STATUS_OPEN,
        ]);
        $student = $this->selectionReadyParticipant($periode, $gelombang, $jurusan);
        $queue = PmbOfflineQueue::create([
            'session_id' => $session->id,
            'user_id' => $student->id,
            'selection_mode' => PmbOfflineQueue::MODE_ONLINE,
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
        ]);
        app(PmbOnlineSelectionService::class)->ensureSteps($queue);

        $this->actingAs($student)
            ->get(route('dashboard.tes-tulis.show', $tesTulis))
            ->assertOk()
            ->assertSee('Read the following passage before answering the questions.');

        $this->actingAs($student)
            ->postJson(route('dashboard.tes-tulis.submit', $tesTulis), [
                'jawaban' => [$soalA->id => $pilihanA->id],
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Lengkapi semua jawaban sebelum mengirim tes tulis.');

        $this->actingAs($student)
            ->postJson(route('dashboard.tes-tulis.submit', $tesTulis), [
                'jawaban' => [$soalA->id => $pilihanA->id],
                'is_auto' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('hasil_tes_tulis', [
            'user_id' => $student->id,
            'tes_tulis_id' => $tesTulis->id,
            'status' => 'selesai',
        ]);
        $this->assertDatabaseHas('tes_tulis_jawaban', [
            'soal_id' => $soalB->id,
            'pilihan_jawaban_id' => null,
            'benar' => false,
        ]);
    }

    public function test_student_written_test_can_show_multiple_passages_inside_one_category(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $kategori = \App\Models\KategoriSoal::create(['nama_kategori' => 'Bahasa Inggris']);
        $tesTulis = TesTulis::create([
            'nama_tes' => 'Tes Bacaan Inggris ' . uniqid(),
            'deskripsi' => 'Validasi bacaan bertingkat.',
            'durasi_menit' => 60,
            'skor_lulus' => 60,
            'status_aktif' => true,
            'acak_soal' => true,
        ]);

        foreach ([
            ['First passage for questions 1-2.', 'Question from first passage?'],
            [null, 'Another question from first passage?'],
            ['Second passage for question 3.', 'Question from second passage?'],
        ] as [$passage, $question]) {
            $soal = $tesTulis->soal()->create([
                'kategori_id' => $kategori->id,
                'cerita_bacaan' => $passage,
                'pertanyaan' => $question,
                'skor' => 1,
            ]);
            $soal->pilihanJawaban()->create([
                'kode_pilihan' => 'A',
                'teks_pilihan' => 'Correct',
                'benar' => true,
                'urutan' => 1,
            ]);
        }

        $session = $this->offlineSession($periode, $gelombang);
        $session->update([
            'tes_tulis_id' => $tesTulis->id,
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addHour(),
            'status' => PmbTestSession::STATUS_OPEN,
        ]);
        $student = $this->selectionReadyParticipant($periode, $gelombang, $jurusan);
        $queue = PmbOfflineQueue::create([
            'session_id' => $session->id,
            'user_id' => $student->id,
            'selection_mode' => PmbOfflineQueue::MODE_ONLINE,
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
        ]);
        app(PmbOnlineSelectionService::class)->ensureSteps($queue);

        $this->actingAs($student)
            ->get(route('dashboard.tes-tulis.show', $tesTulis))
            ->assertOk()
            ->assertSee('First passage for questions 1-2.')
            ->assertSee('Second passage for question 3.');
    }

    public function test_student_written_test_complete_manual_submit_succeeds(): void
    {
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $kategori = \App\Models\KategoriSoal::create(['nama_kategori' => 'Logika']);
        $tesTulis = TesTulis::create([
            'nama_tes' => 'Tes Online Lengkap ' . uniqid(),
            'durasi_menit' => 60,
            'skor_lulus' => 60,
            'status_aktif' => true,
        ]);
        $soalA = $tesTulis->soal()->create([
            'kategori_id' => $kategori->id,
            'pertanyaan' => 'Question one?',
            'skor' => 1,
        ]);
        $soalB = $tesTulis->soal()->create([
            'kategori_id' => $kategori->id,
            'pertanyaan' => 'Question two?',
            'skor' => 1,
        ]);
        $pilihanA = $soalA->pilihanJawaban()->create([
            'kode_pilihan' => 'A',
            'teks_pilihan' => 'Correct',
            'benar' => true,
            'urutan' => 1,
        ]);
        $pilihanB = $soalB->pilihanJawaban()->create([
            'kode_pilihan' => 'A',
            'teks_pilihan' => 'Correct',
            'benar' => true,
            'urutan' => 1,
        ]);
        $session = $this->offlineSession($periode, $gelombang);
        $session->update([
            'tes_tulis_id' => $tesTulis->id,
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addHour(),
            'status' => PmbTestSession::STATUS_OPEN,
        ]);
        $student = $this->selectionReadyParticipant($periode, $gelombang, $jurusan);
        $queue = PmbOfflineQueue::create([
            'session_id' => $session->id,
            'user_id' => $student->id,
            'selection_mode' => PmbOfflineQueue::MODE_ONLINE,
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
        ]);
        app(PmbOnlineSelectionService::class)->ensureSteps($queue);

        $this->actingAs($student)
            ->postJson(route('dashboard.tes-tulis.submit', $tesTulis), [
                'jawaban' => [
                    $soalA->id => $pilihanA->id,
                    $soalB->id => $pilihanB->id,
                ],
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('hasil_tes_tulis', [
            'user_id' => $student->id,
            'tes_tulis_id' => $tesTulis->id,
            'status' => 'selesai',
            'jawaban_benar' => 2,
        ]);
    }

    public function test_student_health_letter_is_visible_and_anamnesis_menu_does_not_repeat_form(): void
    {
        Storage::fake('public');
        [$periode, $gelombang, , $jurusan] = $this->masterData();
        $session = $this->offlineSession($periode, $gelombang);
        $queue = PmbOfflineQueue::create([
            'session_id' => $session->id,
            'user_id' => $this->participant($periode, $gelombang, $jurusan)->id,
            'selection_mode' => PmbOfflineQueue::MODE_ONLINE,
            'status' => PmbOfflineQueue::STATUS_REGISTERED,
        ]);
        app(PmbOnlineSelectionService::class)->ensureSteps($queue);
        TesKesehatanAnamnesa::create([
            'user_id' => $queue->user_id,
            'tanggal_pengisian' => now(),
            'riwayat_penyakit_keluarga' => false,
            'riwayat_penyakit_pribadi' => false,
            'riwayat_operasi' => false,
            'konsumsi_obat_rutin' => false,
            'penyakit_menular' => false,
            'masalah_kulit' => false,
            'tumor_benjolan' => false,
            'epilepsi' => false,
            'cedera_kepala' => false,
            'batuk_kronis' => false,
            'gangguan_pencernaan' => false,
            'gangguan_keseimbangan' => false,
            'claustrophobia' => false,
            'takut_darah' => false,
            'kacamata' => false,
            'gagap' => false,
            'alat_bantu_tulang' => false,
            'lemah_otot' => false,
            'pikiran_bunuh_diri' => false,
            'kelainan_darah' => false,
            'riwayat_psikolog' => false,
            'status' => 'belum diperiksa',
        ]);

        $this->actingAs($queue->user)
            ->post(route('dashboard.tes-kesehatan.surat.store'), [
                'surat_kesehatan' => UploadedFile::fake()->create('surat-klinik.pdf', 120, 'application/pdf'),
            ])
            ->assertRedirect();

        $anamnesa = TesKesehatanAnamnesa::where('user_id', $queue->user_id)->firstOrFail();
        Storage::disk('public')->assertExists($anamnesa->surat_kesehatan_path);

        $this->actingAs($queue->user)
            ->get(route('dashboard.tes-kesehatan.anamnesa.show'))
            ->assertOk()
            ->assertSee('Surat kesehatan sudah diunggah')
            ->assertSee('Lihat File Surat')
            ->assertSee(basename($anamnesa->surat_kesehatan_path));

        $this->actingAs($queue->user)
            ->get(route('dashboard.tes-kesehatan.anamnesa.create'))
            ->assertRedirect(route('dashboard.tes-kesehatan.anamnesa.show'));
    }

    private function interviewDesk(PmbTestSession $session, PmbQueueStage $stage, Admin $lecturer, int $number): PmbTestRoom
    {
        return $session->rooms()->create([
            'stage_id' => $stage->id,
            'assigned_admin_id' => $lecturer->id,
            'name' => 'Meja Wawancara ' . $number,
            'code' => 'WAW-' . $number,
            'capacity' => 1,
            'sort_order' => $number,
            'status' => PmbTestRoom::STATUS_AVAILABLE,
        ]);
    }

    private function assignParticipants(PmbTestSession $session, array $userIds, string $testFlow = 'tes_tulis', bool $checkInAfterAdd = false)
    {
        $request = Request::create('/admin/antrian-tes-offline/' . $session->uuid . '/participants', 'POST', [
            'user_ids' => $userIds,
            'selection_mode' => PmbOfflineQueue::MODE_OFFLINE,
            'test_flow' => $testFlow,
            'check_in_after_add' => $checkInAfterAdd ? 1 : null,
        ]);

        return app(PmbOfflineQueueController::class)->assignParticipants($request, $session);
    }
}
