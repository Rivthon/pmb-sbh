<?php

namespace App\Http\Controllers\Admin;

use App\Events\PmbQueueUpdated;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Gelombang;
use App\Models\Jurusan;
use App\Models\Periode;
use App\Models\PmbOfflineQueue;
use App\Models\PmbQueueCallLog;
use App\Models\PmbQueueStage;
use App\Models\PmbQueueStageStep;
use App\Models\PmbQueueStatusLog;
use App\Models\PmbTestRoom;
use App\Models\PmbTestSession;
use App\Services\Pmb\PmbOfflineQueueWorkflowService;
use App\Services\Pmb\PmbOnlineSelectionService;
use App\Models\TesKesehatanAnamnesa;
use App\Models\TesTulis;
use App\Models\User;
use App\Models\WawancaraPmb;
use App\Services\Audit\ActivityLogger;
use App\Support\AdminPermissions;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PmbOfflineQueueController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
        private readonly PmbOfflineQueueWorkflowService $workflow,
        private readonly PmbOnlineSelectionService $onlineSelection,
    )
    {
    }

    public function index(Request $request): View
    {
        $onlineManagerOnly = $this->isOnlineSelectionManagerOnly();
        $sessions = PmbTestSession::query()
            ->with(['periode', 'gelombang', 'tesTulis'])
            ->withCount([
                'queues' => fn ($query) => $this->scopeQueuesForOnlineManager($query),
                'queues as checked_in_count' => fn ($query) => $this->scopeQueuesForOnlineManager($query)->whereNotNull('checked_in_at'),
                'queues as waiting_count' => fn ($query) => $this->scopeQueuesForOnlineManager($query)->whereIn('status', [
                    PmbOfflineQueue::STATUS_CHECKED_IN,
                    PmbOfflineQueue::STATUS_WAITING,
                ]),
                'queues as completed_count' => fn ($query) => $this->scopeQueuesForOnlineManager($query)->where('status', PmbOfflineQueue::STATUS_COMPLETED),
            ])
            ->when($onlineManagerOnly, fn ($query) => $query->whereHas('queues', fn ($queueQuery) => $queueQuery
                ->where('selection_mode', PmbOfflineQueue::MODE_ONLINE)))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('periode_id'), fn ($query) => $query->where('periode_id', $request->periode_id))
            ->latest('starts_at')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.pmb_queue.index', [
            'sessions' => $sessions,
            'periodes' => Periode::orderByDesc('created_at')->get(),
            'statusOptions' => PmbTestSession::statusOptions(),
        ]);
    }

    public function scanSessions(): View
    {
        $sessions = PmbTestSession::query()
            ->whereIn('status', [PmbTestSession::STATUS_SCHEDULED, PmbTestSession::STATUS_OPEN])
            ->with(['periode', 'gelombang'])
            ->withCount('queues')
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [PmbTestSession::STATUS_OPEN])
            ->orderByDesc('starts_at')
            ->get();

        return view('admin.pmb_queue.scan_sessions', compact('sessions'));
    }

    public function interviewSessions(): View
    {
        return $this->interviewSessionsView('both');
    }

    public function interviewOfflineSessions(): View
    {
        return $this->interviewSessionsView('offline');
    }

    public function interviewOnlineSessions(): View
    {
        return $this->interviewSessionsView('online');
    }

    private function interviewSessionsView(string $mode): View
    {
        $admin = auth('admin')->user()->loadMissing('jurusan');
        $sessions = PmbTestSession::query()
            ->where('status', PmbTestSession::STATUS_OPEN)
            ->with(['periode', 'gelombang', 'rooms' => fn ($query) => $query
                ->where('assigned_admin_id', $admin->id)
                ->whereHas('stage', fn ($stageQuery) => $stageQuery->where('key', PmbQueueStage::KEY_WAWANCARA_KAPRODI))])
            ->orderByDesc('starts_at')
            ->get();
        $offlineInterviewSummaries = $this->interviewSessionSummaries($sessions->pluck('id')->all(), $admin);

        return view('admin.pmb_queue.interview_sessions', compact('sessions', 'admin', 'offlineInterviewSummaries', 'mode'));
    }

    public function writtenTestSessions(): View
    {
        $sessions = PmbTestSession::query()
            ->where('status', PmbTestSession::STATUS_OPEN)
            ->whereHas('stages', fn ($query) => $query
                ->where('key', PmbQueueStage::KEY_TES_TULIS)
                ->where('is_active', true))
            ->with(['periode', 'gelombang'])
            ->withCount(['queues as active_test_count' => fn ($query) => $query
                ->whereHas('stageSteps', fn ($stepQuery) => $stepQuery
                    ->whereHas('stage', fn ($stageQuery) => $stageQuery->where('key', PmbQueueStage::KEY_TES_TULIS))
                    ->whereIn('status', [PmbQueueStageStep::STATUS_CALLED, PmbQueueStageStep::STATUS_PROCESSING]))])
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [PmbTestSession::STATUS_OPEN])
            ->orderByDesc('starts_at')
            ->get();

        return view('admin.pmb_queue.test_sessions', compact('sessions'));
    }

    public function scan(PmbTestSession $session): View
    {
        abort_unless(in_array($session->status, [PmbTestSession::STATUS_SCHEDULED, PmbTestSession::STATUS_OPEN], true), 404);

        return view('admin.pmb_queue.scan', compact('session'));
    }

    public function create(): View
    {
        return view('admin.pmb_queue.form', $this->formData(new PmbTestSession(), 'create'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSession($request);
        $validated['created_by_admin_id'] = auth('admin')->id();

        $session = PmbTestSession::create($validated);
        $this->workflow->ensureDefaultStages($session);
        $this->syncStageConfig($request, $session);

        $this->activityLogger->log(
            'pmb_queue',
            'session_created',
            'Sesi antrian tes offline dibuat.',
            $session,
            [],
            $session->toArray()
        );

        return redirect()
            ->route('admin.pmb-queues.show', $session)
            ->with('success', 'Sesi antrian berhasil dibuat.');
    }

    public function edit(PmbTestSession $session): View
    {
        $this->abortIfOnlineManagerCannotAccessSession($session);

        return view('admin.pmb_queue.form', $this->formData($session, 'edit'));
    }

    public function update(Request $request, PmbTestSession $session): RedirectResponse
    {
        $this->abortIfOnlineManagerCannotAccessSession($session);

        $validated = $this->validateSession($request);
        $oldValues = $session->only(array_keys($validated));

        $session->update($validated);
        $this->workflow->ensureDefaultStages($session);
        $this->syncStageConfig($request, $session);

        $this->activityLogger->log(
            'pmb_queue',
            'session_updated',
            'Sesi antrian tes offline diperbarui.',
            $session,
            $oldValues,
            $session->only(array_keys($validated))
        );

        return redirect()
            ->route('admin.pmb-queues.show', $session)
            ->with('success', 'Sesi antrian berhasil diperbarui.');
    }

    public function destroy(PmbTestSession $session): RedirectResponse
    {
        $session->delete();

        $this->activityLogger->log('pmb_queue', 'session_deleted', 'Sesi antrian tes offline dihapus.', $session);

        return redirect()
            ->route('admin.pmb-queues.index')
            ->with('success', 'Sesi antrian berhasil dihapus.');
    }

    public function show(Request $request, PmbTestSession $session): View
    {
        $this->abortIfOnlineManagerCannotAccessSession($session);

        $this->workflow->ensureDefaultStages($session);
        $session->load(['periode', 'gelombang', 'tesTulis', 'rooms.stage', 'stages']);

        return view('admin.pmb_queue.show', $this->sessionViewData($request, $session));
    }

    public function board(PmbTestSession $session): View
    {
        $session->load(['rooms.stage', 'periode', 'gelombang', 'stages']);
        $payload = $this->workflow->boardPayload($session, false);

        return view('admin.pmb_queue.board', [
            'session' => $session,
            'boardPayload' => $payload,
            'completedCount' => $session->queues()
                ->where('selection_mode', PmbOfflineQueue::MODE_OFFLINE)
                ->where('status', PmbOfflineQueue::STATUS_COMPLETED)
                ->count(),
        ]);
    }

    public function markOnlineHealthNotified(Request $request, PmbTestSession $session): RedirectResponse
    {
        $this->abortIfOnlineManagerCannotAccessSession($session);

        $validated = $request->validate([
            'queue_ids' => ['required', 'array', 'min:1'],
            'queue_ids.*' => ['integer'],
        ]);

        $updated = $session->queues()
            ->where('selection_mode', PmbOfflineQueue::MODE_ONLINE)
            ->whereIn('id', $validated['queue_ids'])
            ->whereNull('health_notified_at')
            ->update(['health_notified_at' => now()]);

        return back()->with('success', $updated . ' peserta online ditandai sudah diberitahukan. Tenggat surat kesehatan H+7 mulai dihitung.');
    }

    public function assignOnlineInterview(Request $request, PmbOfflineQueue $queue): RedirectResponse
    {
        abort_unless($queue->isOnlineSelection(), 404);
        $validated = $request->validate([
            'room_id' => ['required', 'integer', 'exists:pmb_test_rooms,id'],
            'stage_queue_number' => ['required', 'integer', 'min:1'],
        ]);

        $queue->loadMissing(['session', 'user.jurusan']);
        $room = PmbTestRoom::with(['stage', 'assignedOfficer.jurusan'])
            ->whereKey($validated['room_id'])
            ->where('session_id', $queue->session_id)
            ->firstOrFail();

        if ($room->stage?->key !== PmbQueueStage::KEY_WAWANCARA_KAPRODI || !$room->assignedOfficer) {
            throw ValidationException::withMessages(['room_id' => 'Pilih breakout room yang memiliki dosen pewawancara.']);
        }

        $poolIds = $room->assignedOfficer->jurusan?->interviewPoolIds() ?? [];
        if (!in_array((int) $queue->user?->jurusan_id, $poolIds, true)) {
            throw ValidationException::withMessages(['room_id' => 'Dosen tidak berada dalam pool program studi peserta.']);
        }

        $step = $this->onlineSelection->ensureSteps($queue)
            ->first(fn ($item) => $item->stage?->key === PmbQueueStage::KEY_WAWANCARA_KAPRODI);
        $duplicate = PmbQueueStageStep::where('stage_id', $step->stage_id)
            ->where('stage_queue_number', $validated['stage_queue_number'])
            ->where('id', '<>', $step->id)
            ->exists();
        if ($duplicate) {
            throw ValidationException::withMessages(['stage_queue_number' => 'Nomor urut tersebut sudah dipakai pada sesi ini.']);
        }

        $formStatus = WawancaraPmb::where('calon_mahasiswa_id', $queue->user_id)->value('status');
        $stepStatus = match ($formStatus) {
            'reviewed', 'locked' => PmbQueueStageStep::STATUS_COMPLETED,
            'submitted' => PmbQueueStageStep::STATUS_WAITING,
            default => PmbQueueStageStep::STATUS_PENDING,
        };
        $step->update([
            'room_id' => $room->id,
            'assigned_officer_id' => $room->assigned_admin_id,
            'stage_queue_number' => $validated['stage_queue_number'],
            'stage_queue_code' => 'W-' . str_pad((string) $validated['stage_queue_number'], 3, '0', STR_PAD_LEFT),
            'status' => $stepStatus,
            'queued_at' => $formStatus ? ($step->queued_at ?: now()) : null,
            'completed_at' => in_array($formStatus, ['reviewed', 'locked'], true) ? ($step->completed_at ?: now()) : null,
        ]);

        return back()->with('success', 'Dosen, breakout room, dan nomor urut wawancara berhasil ditetapkan.');
    }

    public function officerOnlineWawancara(PmbTestSession $session): View
    {
        $admin = auth('admin')->user()->loadMissing('jurusan');
        $jurusanIds = $admin->jurusan?->interviewPoolIds() ?? [];
        $steps = collect();

        if ($jurusanIds) {
            $steps = PmbQueueStageStep::with(['queue.user.jurusan', 'queue.session', 'room', 'stage', 'officer'])
                ->where(function ($query) use ($admin): void {
                    $query->whereNull('assigned_officer_id')
                        ->orWhere('assigned_officer_id', $admin->id);
                })
                ->whereHas('queue', fn ($query) => $query
                    ->where('session_id', $session->id)
                    ->where('selection_mode', PmbOfflineQueue::MODE_ONLINE)
                    ->whereHas('user', fn ($userQuery) => $userQuery->whereIn('jurusan_id', $jurusanIds)))
                ->whereHas('stage', fn ($query) => $query->where('key', PmbQueueStage::KEY_WAWANCARA_KAPRODI))
                ->whereHas('queue.user', fn ($query) => $query->whereHas('wawancaraPmb', fn ($wawancaraQuery) => $wawancaraQuery
                    ->whereIn('status', ['submitted', 'reviewed', 'locked'])))
                ->orderByRaw('CASE WHEN assigned_officer_id = ? THEN 0 ELSE 1 END', [$admin->id])
                ->orderBy('stage_queue_number')
                ->orderBy('id')
                ->get();
        }

        $wawancaraByUser = WawancaraPmb::whereIn('calon_mahasiswa_id', $steps->pluck('queue.user_id'))
            ->get()->keyBy('calon_mahasiswa_id');

        return view('admin.pmb_queue.officer.wawancara_online', compact('session', 'steps', 'wawancaraByUser', 'admin'));
    }

    public function startOnlineInterview(PmbOfflineQueue $queue): RedirectResponse
    {
        $admin = auth('admin')->user()->loadMissing('jurusan');
        $wawancara = null;

        try {
            DB::transaction(function () use ($queue, $admin, &$wawancara): void {
                abort_unless($queue->isOnlineSelection(), 404);

                $jurusanIds = $admin->jurusan?->interviewPoolIds() ?? [];
                abort_if(!$jurusanIds || !in_array((int) $queue->user?->jurusan_id, $jurusanIds, true), 403, 'Program studi peserta tidak sesuai dengan akun dosen.');

                $wawancara = WawancaraPmb::where('calon_mahasiswa_id', $queue->user_id)
                    ->where('status', 'submitted')
                    ->firstOrFail();

                $step = PmbQueueStageStep::where('queue_id', $queue->id)
                    ->whereHas('stage', fn ($query) => $query->where('key', PmbQueueStage::KEY_WAWANCARA_KAPRODI))
                    ->lockForUpdate()
                    ->firstOrFail();

                abort_if($step->assigned_officer_id && (int) $step->assigned_officer_id !== (int) $admin->id, 403, 'Peserta ini sudah dipilih dosen lain.');
                abort_unless(in_array($step->status, [PmbQueueStageStep::STATUS_WAITING, PmbQueueStageStep::STATUS_PROCESSING, PmbQueueStageStep::STATUS_REENTRY], true), 422);

                $step->update([
                    'assigned_officer_id' => $admin->id,
                    'status' => PmbQueueStageStep::STATUS_PROCESSING,
                    'started_at' => $step->started_at ?: now(),
                ]);
            });
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return back()->with('error', 'Form wawancara peserta belum tersedia atau belum dikirim.');
        }

        return redirect()
            ->route('admin.wawancara.review.form', ['wawancara' => $wawancara, 'queue_uuid' => $queue->uuid])
            ->with('success', 'Peserta berhasil dipilih. Silakan isi review wawancara.');
    }

    public function allowOnlineInterviewReentry(PmbOfflineQueue $queue): RedirectResponse
    {
        $this->authorizedOnlineInterviewStep($queue);
        $wawancara = WawancaraPmb::where('calon_mahasiswa_id', $queue->user_id)->firstOrFail();
        abort_if($wawancara->status === 'locked', 403, 'Hasil wawancara sudah dikunci final.');
        $wawancara->update(['status' => 'submitted']);
        $this->onlineSelection->markInterviewReentry($queue, auth('admin')->id());

        return back()->with('success', 'Peserta diizinkan masuk kembali ke dosen yang sama. Jawaban dan hasil sebelumnya tetap tersimpan.');
    }

    public function stats(Request $request, PmbTestSession $session)
    {
        $this->abortIfOnlineManagerCannotAccessSession($session);

        $data = $this->sessionViewData($request, $session, false);

        return response()->json([
            'stats' => view('admin.pmb_queue.partials.stats', $data)->render(),
            'call_console' => view('admin.pmb_queue.partials.call_console', $data)->render(),
            'lanes_health' => view('admin.pmb_queue.partials.lanes', array_merge($data, ['stageKey' => \App\Models\PmbQueueStage::KEY_TES_KESEHATAN]))->render(),
            'lanes_interview' => view('admin.pmb_queue.partials.lanes', array_merge($data, ['stageKey' => \App\Models\PmbQueueStage::KEY_WAWANCARA_KAPRODI]))->render(),
            'rows' => view('admin.pmb_queue.partials.rows', $data)->render(),
        ]);
    }

    public function officerTesTulis(PmbTestSession $session): View
    {
        $this->workflow->ensureDefaultStages($session);
        $this->workflow->autoStartTesTulis($session, auth('admin')->id());

        return view('admin.pmb_queue.officer.tes_tulis', $this->officerViewData($session, PmbQueueStage::KEY_TES_TULIS, [
            'processingSteps' => $this->stageSteps($session, PmbQueueStage::KEY_TES_TULIS, [
                PmbQueueStageStep::STATUS_PROCESSING,
                PmbQueueStageStep::STATUS_CALLED,
            ])->get(),
        ]));
    }

    public function officerKesehatan(PmbTestSession $session): View
    {
        $this->workflow->ensureDefaultStages($session);

        $currentStep = $this->currentOfficerStep($session, PmbQueueStage::KEY_TES_KESEHATAN);
        $anamnesa = null;

        if ($currentStep?->queue?->user) {
            $anamnesa = TesKesehatanAnamnesa::firstOrCreate(
                ['user_id' => $currentStep->queue->user_id],
                [
                    'admin_id' => auth('admin')->id(),
                    'status' => 'belum diperiksa',
                ]
            )->load(['user.jurusan', 'pemeriksaan']);
        }

        return view('admin.pmb_queue.officer.kesehatan', $this->officerViewData($session, PmbQueueStage::KEY_TES_KESEHATAN, [
            'currentStep' => $currentStep,
            'anamnesa' => $anamnesa,
        ]));
    }

    public function officerKesehatanReady(PmbTestSession $session): RedirectResponse
    {
        $this->workflow->ensureDefaultStages($session);

        try {
            $step = $this->workflow->autoDispatchForOfficer($session, PmbQueueStage::KEY_TES_KESEHATAN, auth('admin')->id());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.pmb-queues.officer.kesehatan', $session)
            ->with($step ? 'success' : 'info', $step ? 'Peserta berikutnya berhasil dipanggil.' : 'Belum ada peserta menunggu Tes Kesehatan.');
    }

    public function officerWawancara(PmbTestSession $session): View
    {
        $this->workflow->ensureDefaultStages($session);

        abort_unless(in_array($session->status, [PmbTestSession::STATUS_SCHEDULED, PmbTestSession::STATUS_OPEN], true), 404);

        $admin = auth('admin')->user()->loadMissing('jurusan');
        $interviewStage = $session->stages()->where('key', PmbQueueStage::KEY_WAWANCARA_KAPRODI)->first();
        $interviewRoom = $interviewStage
            ? $session->rooms()->where('stage_id', $interviewStage->id)->where('assigned_admin_id', $admin->id)->first()
            : null;
        $interviewRoom ??= $interviewStage && $admin->jurusan_id
            ? $this->programInterviewRoomForOfficer($session, $interviewStage, $admin)
            : null;
        $configurationError = !$admin->jurusan_id
            ? 'Program studi akun dosen belum ditentukan. Hubungi admin PMB.'
            : (!$interviewRoom ? 'Meja Wawancara untuk program studi akun dosen belum tersedia pada sesi ini.' : null);

        $currentStep = $this->currentOfficerStep($session, PmbQueueStage::KEY_WAWANCARA_KAPRODI);
        $wawancara = null;
        $healthExam = null;
        $healthAnamnesa = null;

        if ($currentStep?->queue?->user) {
            $wawancara = WawancaraPmb::firstOrCreate(
                ['calon_mahasiswa_id' => $currentStep->queue->user_id],
                ['status' => 'submitted']
            );

            if ($wawancara->status === 'draft') {
                $wawancara->update(['status' => 'submitted']);
            }

            $healthAnamnesa = TesKesehatanAnamnesa::with('pemeriksaan')
                ->where('user_id', $currentStep->queue->user_id)
                ->first();
            $healthExam = $healthAnamnesa?->pemeriksaan;
        }

        return view('admin.pmb_queue.officer.wawancara', $this->officerViewData($session, PmbQueueStage::KEY_WAWANCARA_KAPRODI, [
            'currentStep' => $currentStep,
            'wawancara' => $wawancara,
            'healthExam' => $healthExam,
            'healthAnamnesa' => $healthAnamnesa,
            'interviewRoom' => $interviewRoom,
            'configurationError' => $configurationError,
        ]));
    }

    public function officerWawancaraReady(PmbTestSession $session): RedirectResponse
    {
        try {
            $step = $this->workflow->autoDispatchForOfficer(
                $session,
                PmbQueueStage::KEY_WAWANCARA_KAPRODI,
                auth('admin')->id()
            );
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.pmb-queues.officer.wawancara', $session)
            ->with($step ? 'success' : 'info', $step ? 'Peserta berikutnya berhasil dipanggil.' : 'Belum ada peserta wawancara yang siap untuk program studi Anda.');
    }

    public function storeRoom(Request $request, PmbTestSession $session)
    {
        $validated = $this->validatedRoomData($request, $session);
        $stage = $validated['stage_id'] ? PmbQueueStage::find($validated['stage_id']) : null;
        $prefix = str_contains(strtolower($stage?->key ?? ''), 'kesehatan') ? 'KES' : 'WAW';

        $room = DB::transaction(function () use ($session, $validated, $prefix): PmbTestRoom {
            PmbTestSession::whereKey($session->id)->lockForUpdate()->firstOrFail();
            $nextNumber = $this->nextRoomNumber($session, $prefix);

            return $session->rooms()->create(array_merge($validated, [
                'code' => $prefix . '-' . $nextNumber,
                'name' => match ($prefix) {
                    'KES' => 'Meja ' . $nextNumber,
                    'WAW' => 'Meja Wawancara ' . $nextNumber,
                    default => $validated['name'],
                },
                'capacity' => 1,
                'sort_order' => $nextNumber,
            ]));
        });

        $this->activityLogger->log('pmb_queue', 'room_created', 'Ruangan tes dibuat.', $room, [], $room->toArray());

        $message = 'Ruangan berhasil ditambahkan.';

        if ($request->expectsJson() || $request->ajax()) {
            return $this->queueFragments($request, $session, $message, ['ok' => true]);
        }

        return back()->with('success', $message);
    }

    public function updateRoomStatus(Request $request, PmbTestRoom $room)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', array_keys(PmbTestRoom::statusOptions()))],
        ]);

        $old = ['status' => $room->status];
        $room->update($validated);

        $this->activityLogger->log('pmb_queue', 'room_status_updated', 'Status ruangan diperbarui.', $room, $old, $validated);

        $message = 'Status ruangan diperbarui.';

        if ($request->expectsJson() || $request->ajax()) {
            return $this->queueFragments($request, $room->session, $message, ['ok' => true]);
        }

        return back()->with('success', $message);
    }

    public function updateRoom(Request $request, PmbTestRoom $room)
    {
        $validated = $this->validatedRoomData($request, $room->session, $room);

        $validated['code'] = $room->code;
        $validated['capacity'] = $room->capacity ?: 1;
        $validated['sort_order'] = $room->sort_order;

        $old = $room->toArray();
        $room->update($validated);

        $this->activityLogger->log('pmb_queue', 'room_updated', 'Data ruangan diperbarui.', $room, $old, $validated);

        $message = 'Ruangan berhasil diperbarui.';

        if ($request->expectsJson() || $request->ajax()) {
            return $this->queueFragments($request, $room->session, $message, ['ok' => true]);
        }

        return back()->with('success', $message);
    }

    public function destroyRoom(Request $request, PmbTestRoom $room)
    {
        $hasActiveQueue = $room->queues()->whereIn('status', [
            PmbOfflineQueue::STATUS_CALLED,
            PmbOfflineQueue::STATUS_IN_ROOM,
        ])->exists();
        $hasActiveStep = $room->stageSteps()->whereIn('status', [
            PmbQueueStageStep::STATUS_CALLED,
            PmbQueueStageStep::STATUS_PROCESSING,
        ])->exists();

        if ($hasActiveQueue || $hasActiveStep) {
            $errMessage = 'Ruangan masih memiliki peserta yang sedang dipanggil atau berada di ruangan.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'message' => $errMessage], 422);
            }
            return back()->with('error', $errMessage);
        }

        $session = $room->session;
        $roomSnapshot = $room->toArray();

        try {
            DB::transaction(function () use ($room): void {
                PmbQueueStage::where('default_room_id', $room->id)->update(['default_room_id' => null]);
                PmbOfflineQueue::where('room_id', $room->id)->update(['room_id' => null]);
                PmbQueueStageStep::where('room_id', $room->id)->update(['room_id' => null]);
                PmbQueueCallLog::where('room_id', $room->id)->update(['room_id' => null]);
                $room->delete();
            });
        } catch (\Throwable $e) {
            report($e);
            $errMessage = 'Loket belum dapat dihapus. Muat ulang halaman lalu coba kembali.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'message' => $errMessage], 422);
            }

            return back()->with('error', $errMessage);
        }

        $this->activityLogger->log('pmb_queue', 'room_deleted', 'Ruangan tes dihapus.', null, $roomSnapshot, []);

        $message = 'Ruangan berhasil dihapus.';

        if ($request->expectsJson() || $request->ajax()) {
            return $this->queueFragments($request, $session, $message, ['ok' => true]);
        }

        return back()->with('success', $message);
    }

    public function assignParticipants(Request $request, PmbTestSession $session): RedirectResponse
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'selection_mode' => ['required', 'string', 'in:' . PmbOfflineQueue::MODE_OFFLINE . ',' . PmbOfflineQueue::MODE_ONLINE],
            'test_flow' => ['required', 'string', 'in:tes_tulis,bebas_tes_tulis'],
            'check_in_after_add' => ['nullable', 'boolean'],
            'redirect_to' => ['nullable', 'string', 'in:participant_picker'],
        ]);

        $adminId = auth('admin')->id();
        $added = 0;
        $skipped = 0;
        $checkedIn = 0;
        $eligibleUserIds = User::query()
            ->whereIn('id', $validated['user_ids'])
            ->where('role', User::USER_ROLE)
            ->isPmbVerified()
            ->pluck('id')
            ->all();

        foreach ($eligibleUserIds as $userId) {
            $queue = PmbOfflineQueue::firstOrCreate(
                [
                    'session_id' => $session->id,
                    'user_id' => $userId,
                ],
                [
                    'status' => PmbOfflineQueue::STATUS_REGISTERED,
                    'priority' => 0,
                    'selection_mode' => $validated['selection_mode'],
                    'created_by_admin_id' => $adminId,
                ]
            );

            if (!$queue->wasRecentlyCreated && !$queue->checked_in_at) {
                $queue->update(['selection_mode' => $validated['selection_mode']]);
            }

            $this->workflow->markQueueTesTulisRequirement($queue, $validated['test_flow'] === 'tes_tulis');

            if ($queue->isOnlineSelection()) {
                $this->onlineSelection->syncWrittenRequirement($queue);
            }

            if ($queue->wasRecentlyCreated) {
                $added++;
                $this->writeStatusLog($queue, null, PmbOfflineQueue::STATUS_REGISTERED, 'Peserta ditambahkan ke sesi.');
            }

            if (!empty($validated['check_in_after_add']) && !$queue->checked_in_at && $queue->isOfflineSelection()) {
                $this->workflow->checkIn($queue, $adminId);
                $checkedIn++;
            }
        }

        $skipped = count($validated['user_ids']) - count($eligibleUserIds);
        $message = "{$added} peserta baru berhasil ditambahkan ke sesi.";
        if ($checkedIn > 0) {
            $message .= " {$checkedIn} peserta langsung check-in.";
        }
        if ($skipped > 0) {
            $message .= " {$skipped} peserta dilewati karena belum verified atau bukan akun peserta.";
        }

        if (($validated['redirect_to'] ?? null) === 'participant_picker') {
            return redirect()
                ->route('admin.pmb-queues.participants.add', ['session_id' => $session->id])
                ->with($added > 0 ? 'success' : 'error', $message);
        }

        return back()->with($added > 0 ? 'success' : 'error', $message);
    }

    public function participantPicker(Request $request)
    {
        $sessions = PmbTestSession::with(['periode', 'gelombang'])
            ->latest('starts_at')
            ->latest()
            ->get();

        $selectedSession = $request->filled('session_id')
            ? PmbTestSession::with(['periode', 'gelombang'])->find($request->integer('session_id'))
            : $sessions->first();

        $usedUserIds = $selectedSession
            ? $selectedSession->queues()->pluck('user_id')
            : collect();

        // Determine effective periode: from session, from filter, or fallback to active periode
        $effectivePeriodeId = $request->filled('periode_id')
            ? $request->integer('periode_id')
            : ($selectedSession?->periode_id ?? Periode::where('status_periode', 'aktif')->value('id'));

        // Determine effective gelombang: from session or from filter
        $effectiveGelombangId = $request->filled('gelombang_id')
            ? $request->integer('gelombang_id')
            : null;

        $participants = User::query()
            ->where('role', User::USER_ROLE)
            ->isPmbVerified()
            ->when($effectivePeriodeId, fn ($query) => $query->where('periode_id', $effectivePeriodeId))
            ->when($effectiveGelombangId, fn ($query) => $query->where('gelombang_id', $effectiveGelombangId))
            ->when($request->filled('jurusan_id'), fn ($query) => $query->where('jurusan_id', $request->jurusan_id))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->search;
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->whereNotIn('id', $usedUserIds)
            ->with(['jurusan', 'periode', 'gelombang'])
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.pmb_queue.partials.participants_table', [
                'participants' => $participants,
                'selectedSession' => $selectedSession,
            ]);
        }

        $periodeList = Periode::orderByDesc('created_at')->get();
        $gelombangList = $effectivePeriodeId
            ? Gelombang::where('periode_id', $effectivePeriodeId)->orderBy('nama_gelombang')->get()
            : Gelombang::orderByDesc('created_at')->get();

        return view('admin.pmb_queue.participants_add', [
            'sessions' => $sessions,
            'selectedSession' => $selectedSession,
            'participants' => $participants,
            'jurusanList' => Jurusan::orderBy('nama_jurusan')->get(),
            'periodeList' => $periodeList,
            'gelombangList' => $gelombangList,
            'effectivePeriodeId' => $effectivePeriodeId,
            'effectiveGelombangId' => $effectiveGelombangId,
        ]);
    }

    public function removeParticipant(PmbOfflineQueue $queue): RedirectResponse
    {
        if ($queue->status !== PmbOfflineQueue::STATUS_REGISTERED
            || ($queue->isOnlineSelection() && $queue->overall_status !== PmbOfflineQueue::OVERALL_NOT_STARTED)) {
            return back()->with('error', 'Peserta yang sudah check-in atau diproses tidak dapat dihapus dari sesi.');
        }

        $queue->delete();

        return back()->with('success', 'Peserta berhasil dihapus dari sesi.');
    }

    public function manualCheckIn(Request $request, PmbTestSession $session): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255'],
        ]);

        $queue = $this->findQueueFromCode($session, $validated['code']);

        if (!$queue) {
            return back()->with('error', 'Kode QR/nomor peserta tidak ditemukan pada sesi ini.');
        }

        if ($queue->isOnlineSelection()) {
            return back()->with('error', 'Peserta mode online tidak menggunakan check-in/antrian fisik kampus.');
        }

        return $this->checkInAndRedirect($queue);
    }

    public function bulkCheckIn(Request $request, PmbTestSession $session): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'queue_ids' => ['required', 'array', 'min:1'],
            'queue_ids.*' => ['integer'],
        ]);

        $queues = PmbOfflineQueue::with(['session', 'user'])
            ->where('session_id', $session->id)
            ->whereIn('id', $validated['queue_ids'])
            ->get();

        $success = 0;
        $failed = [];

        foreach ($queues as $queue) {
            if ($queue->checked_in_at) {
                $failed[] = ($queue->queue_code ?: $queue->user?->name ?: 'Peserta') . ' sudah check-in.';
                continue;
            }

            if ($queue->isOnlineSelection()) {
                $failed[] = ($queue->queue_code ?: $queue->user?->name ?: 'Peserta') . ' adalah peserta online.';
                continue;
            }

            if (in_array($queue->status, [PmbOfflineQueue::STATUS_CANCELLED, PmbOfflineQueue::STATUS_NO_SHOW], true)) {
                $failed[] = ($queue->queue_code ?: $queue->user?->name ?: 'Peserta') . ' tidak bisa check-in dari status saat ini.';
                continue;
            }



            try {
                $this->workflow->checkIn($queue, auth('admin')->id());
                $success++;
            } catch (DomainException $e) {
                $failed[] = ($queue->queue_code ?: $queue->user?->name ?: 'Peserta') . ' ' . $e->getMessage();
            }
        }

        $message = "{$success} peserta berhasil check-in.";
        if ($failed) {
            $message .= ' ' . count($failed) . ' peserta dilewati.';
        }

        if (!$request->expectsJson() && !$request->ajax()) {
            return back()
                ->with($success > 0 ? 'success' : 'error', $message)
                ->with('bulk_errors', $failed);
        }

        return $this->queueFragments($request, $session, $message, [
            'ok' => $success > 0,
            'success_count' => $success,
            'failed_count' => count($failed),
            'errors' => $failed,
        ], $success > 0 ? 200 : 422);
    }

    public function checkInLink(PmbOfflineQueue $queue): RedirectResponse
    {
        return $this->checkInAndRedirect($queue);
    }

    public function callNext(Request $request, PmbTestSession $session)
    {
        $validated = $request->validate([
            'room_id' => ['nullable', 'integer', 'exists:pmb_test_rooms,id'],
            'stage_id' => ['nullable', 'integer', 'exists:pmb_queue_stages,id'],
            'queue_id' => ['nullable', 'integer', 'exists:pmb_offline_queues,id'],
        ]);

        $room = isset($validated['room_id'])
            ? PmbTestRoom::where('session_id', $session->id)->find($validated['room_id'])
            : null;
        $stage = isset($validated['stage_id'])
            ? PmbQueueStage::where('session_id', $session->id)->find($validated['stage_id'])
            : null;

        if (!$this->canOperateStage($stage, $room)) {
            $message = 'Anda tidak memiliki akses untuk memproses tahap ini.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'message' => $message], 403);
            }

            return back()->with('error', $message);
        }

        try {
            $step = $this->workflow->callNext($session, $stage, $room, $validated['queue_id'] ?? null, auth('admin')->id());
        } catch (DomainException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

        if (!$step) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Tidak ada peserta menunggu untuk dipanggil.'
                ], 422);
            }
            return back()->with('error', 'Tidak ada peserta menunggu untuk dipanggil.');
        }

        $message = 'Peserta berhasil dipanggil.';

        if ($request->expectsJson() || $request->ajax()) {
            return $this->queueFragments($request, $session, $message, ['ok' => true]);
        }

        return back()->with('success', $message);
    }

    public function updateQueueStatus(Request $request, PmbOfflineQueue $queue): RedirectResponse
    {
        $result = $this->processQueueAction($request, $queue);

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function updateQueueStatusJson(Request $request, PmbOfflineQueue $queue): JsonResponse
    {
        $result = $this->processQueueAction($request, $queue);

        return $this->queueFragments($request, $queue->session, $result['message'], [
            'ok' => $result['ok'],
        ], $result['ok'] ? 200 : 422);
    }

    public function bulkStatus(Request $request, PmbTestSession $session): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:complete_test_tulis,hold_health_form,release_health_form,complete_health,complete'],
            'queue_ids' => ['required', 'array', 'min:1'],
            'queue_ids.*' => ['integer'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $queues = PmbOfflineQueue::with(['session', 'user', 'stageSteps.stage'])
            ->where('session_id', $session->id)
            ->whereIn('id', $validated['queue_ids'])
            ->get();

        $success = 0;
        $failed = [];

        foreach ($queues as $queue) {
            $step = $this->activeStepForQueue($queue);

            if (!$step || !$this->bulkActionMatchesStage($validated['action'], $step)) {
                $failed[] = ($queue->queue_code ?: $queue->user?->name ?: 'Peserta') . ' tidak cocok dengan aksi bulk.';
                continue;
            }

            if (!$this->canOperateStage($step->stage, $step->room)) {
                $failed[] = ($queue->queue_code ?: $queue->user?->name ?: 'Peserta') . ' tidak dapat diproses oleh akun ini.';
                continue;
            }

            try {
                $this->workflow->updateStep($step, $validated['action'], null, auth('admin')->id(), $validated['note'] ?? null);
                $success++;
            } catch (DomainException $e) {
                $failed[] = ($queue->queue_code ?: $queue->user?->name ?: 'Peserta') . ' ' . $e->getMessage();
            }
        }

        $message = "{$success} peserta berhasil diproses.";
        if ($failed) {
            $message .= ' ' . count($failed) . ' peserta dilewati.';
        }

        if (!$request->expectsJson() && !$request->ajax()) {
            return back()
                ->with($success > 0 ? 'success' : 'error', $message)
                ->with('bulk_errors', $failed);
        }

        return $this->queueFragments($request, $session, $message, [
            'ok' => $success > 0,
            'success_count' => $success,
            'failed_count' => count($failed),
            'errors' => $failed,
        ], $success > 0 ? 200 : 422);
    }

    private function processQueueAction(Request $request, PmbOfflineQueue $queue): array
    {
        $validated = $request->validate([
            'action' => ['required', 'in:in_room,complete,complete_test_tulis,complete_health,hold_health_form,release_health_form,skip,recall,no_show,cancel,hold,release,problem,reactivate,set_tes_tulis,set_bebas_tes_tulis'],
            'room_id' => ['nullable', 'integer', 'exists:pmb_test_rooms,id'],
            'step_id' => ['nullable', 'integer', 'exists:pmb_queue_stage_steps,id'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if (in_array($validated['action'], ['set_tes_tulis', 'set_bebas_tes_tulis'], true)) {
            if (!$this->canManageQueue()) {
                return ['ok' => false, 'message' => 'Anda tidak memiliki akses untuk mengubah jalur tes peserta.'];
            }

            if ($queue->checked_in_at
                || $queue->status !== PmbOfflineQueue::STATUS_REGISTERED
                || ($queue->isOnlineSelection() && $queue->overall_status !== PmbOfflineQueue::OVERALL_NOT_STARTED)) {
                return ['ok' => false, 'message' => 'Jalur tes hanya bisa diubah sebelum peserta check-in atau sebelum proses seleksi online dimulai.'];
            }

            $requiresTesTulis = $validated['action'] === 'set_tes_tulis';
            $this->workflow->markQueueTesTulisRequirement($queue, $requiresTesTulis);
            if ($queue->isOnlineSelection()) {
                $this->onlineSelection->syncWrittenRequirement($queue);
            }

            return [
                'ok' => true,
                'message' => $requiresTesTulis
                    ? 'Peserta ditandai ikut Tes Tulis.'
                    : 'Peserta ditandai Bebas Tes Tulis.',
            ];
        }

        if ($validated['action'] === 'cancel') {
            try {
                $this->workflow->cancelQueue($queue, auth('admin')->id(), $validated['note'] ?? null);
            } catch (DomainException $e) {
                return ['ok' => false, 'message' => $e->getMessage()];
            }

            return ['ok' => true, 'message' => 'Peserta berhasil dibatalkan.'];
        }

        if ($validated['action'] === 'no_show') {
            try {
                $this->workflow->markNoShow($queue, auth('admin')->id(), $validated['note'] ?? null);
            } catch (DomainException $e) {
                return ['ok' => false, 'message' => $e->getMessage()];
            }

            return ['ok' => true, 'message' => 'Peserta ditandai tidak hadir.'];
        }

        if ($validated['action'] === 'reactivate') {
            try {
                $this->workflow->reactivateQueue($queue, auth('admin')->id(), $validated['note'] ?? null);
            } catch (DomainException $e) {
                return ['ok' => false, 'message' => $e->getMessage()];
            }

            return ['ok' => true, 'message' => 'Peserta berhasil diaktifkan kembali.'];
        }

        $step = isset($validated['step_id'])
            ? PmbQueueStageStep::with(['stage', 'queue.session'])->where('queue_id', $queue->id)->find($validated['step_id'])
            : $this->activeStepForQueue($queue);

        if (!$step) {
            $this->workflow->ensureSteps($queue->load('session'));
            $step = $this->activeStepForQueue($queue->fresh(['stageSteps.stage']));
        }

        if (!$step) {
            return ['ok' => false, 'message' => 'Tahap aktif peserta belum tersedia. Check-in peserta terlebih dahulu.'];
        }

        if (!$this->canOperateStage($step->stage, $step->room) && !$this->isManualOverrideAction($validated['action'])) {
            return ['ok' => false, 'message' => 'Anda tidak memiliki akses untuk memproses tahap ini.'];
        }

        if ($this->isManualOverrideAction($validated['action']) && !$this->canManualOverride()) {
            return ['ok' => false, 'message' => 'Anda tidak memiliki akses manual override.'];
        }

        $action = $validated['action'] === 'in_room' ? 'start' : $validated['action'];
        try {
            $this->workflow->updateStep($step, $action, $validated['room_id'] ?? null, auth('admin')->id(), $validated['note'] ?? null);
        } catch (DomainException $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }

        return ['ok' => true, 'message' => $this->queueActionMessage($validated['action'], $step)];
    }

    private function activeStepForQueue(PmbOfflineQueue $queue): ?PmbQueueStageStep
    {
        $queue->loadMissing(['stageSteps.stage']);

        return $queue->stageSteps->firstWhere('stage_id', $queue->current_stage_id)
            ?: $queue->stageSteps
                ->whereIn('status', [
                    PmbQueueStageStep::STATUS_WAITING,
                    PmbQueueStageStep::STATUS_CALLED,
                    PmbQueueStageStep::STATUS_PROCESSING,
                    PmbQueueStageStep::STATUS_HELD,
                    PmbQueueStageStep::STATUS_PROBLEM,
                ])
                ->sortBy(fn (PmbQueueStageStep $step) => $step->stage->sort_order ?? 999)
                ->first();
    }

    private function bulkActionMatchesStage(string $action, PmbQueueStageStep $step): bool
    {
        if (in_array($step->status, [PmbQueueStageStep::STATUS_COMPLETED, PmbQueueStageStep::STATUS_SKIPPED, PmbQueueStageStep::STATUS_PENDING], true)) {
            return false;
        }

        return match ($action) {
            'complete_test_tulis' => $step->stage?->key === PmbQueueStage::KEY_TES_TULIS,
            'hold_health_form' => in_array($step->stage?->key, [PmbQueueStage::KEY_TES_TULIS, PmbQueueStage::KEY_TES_KESEHATAN], true),
            'release_health_form' => $step->stage?->key === PmbQueueStage::KEY_TES_KESEHATAN && $step->status === PmbQueueStageStep::STATUS_HELD,
            'complete_health' => $step->stage?->key === PmbQueueStage::KEY_TES_KESEHATAN,
            'complete' => $step->stage?->key === PmbQueueStage::KEY_WAWANCARA_KAPRODI,
            default => false,
        };
    }

    private function queueActionMessage(string $action, PmbQueueStageStep $step): string
    {
        return match ($action) {
            'complete_test_tulis' => 'Tes Tulis di-ACC. Peserta masuk tahap Tes Kesehatan.',
            'hold_health_form' => 'Peserta ditahan untuk menunggu berkas kesehatan manual.',
            'release_health_form' => 'Berkas kesehatan manual diterima. Peserta masuk Tes Kesehatan.',
            'complete_health' => 'Tes Kesehatan selesai. Peserta masuk antrean Wawancara sesuai urutan selesai kesehatan.',
            'complete' => match ($step->stage?->key) {
                PmbQueueStage::KEY_PEMBERKASAN => 'Berkas di-ACC. Peserta masuk Tes Tulis.',
                PmbQueueStage::KEY_TES_TULIS => 'Tes Tulis di-ACC. Peserta masuk Tes Kesehatan.',
                PmbQueueStage::KEY_TES_KESEHATAN => 'Tes Kesehatan selesai. Peserta masuk antrean Wawancara Kaprodi.',
                PmbQueueStage::KEY_WAWANCARA_KAPRODI => 'Wawancara selesai. Proses PMB offline peserta selesai.',
                default => 'Tahap di-ACC dan peserta dipindahkan sesuai alur.',
            },
            'in_room' => 'Peserta mulai diproses pada tahap aktif.',
            'hold' => 'Peserta ditahan pada tahap aktif.',
            'release' => 'Peserta dilepas dan kembali menunggu pada tahap aktif.',
            'skip' => 'Peserta dilewati sementara pada tahap aktif.',
            'recall' => 'Peserta dipanggil ulang pada tahap aktif.',
            'problem' => 'Peserta ditandai bermasalah pada tahap aktif.',
            default => 'Status tahap peserta berhasil diperbarui.',
        };
    }

    private function queueFragments(Request $request, PmbTestSession $session, ?string $message = null, array $meta = [], int $status = 200): JsonResponse
    {
        $data = $this->sessionViewData($request, $session, false);

        return response()->json(array_merge([
            'message' => $message,
            'stats' => view('admin.pmb_queue.partials.stats', $data)->render(),
            'call_console' => view('admin.pmb_queue.partials.call_console', $data)->render(),
            'lanes_health' => view('admin.pmb_queue.partials.lanes', array_merge($data, ['stageKey' => \App\Models\PmbQueueStage::KEY_TES_KESEHATAN]))->render(),
            'lanes_interview' => view('admin.pmb_queue.partials.lanes', array_merge($data, ['stageKey' => \App\Models\PmbQueueStage::KEY_WAWANCARA_KAPRODI]))->render(),
            'rows' => view('admin.pmb_queue.partials.rows', $data)->render(),
        ], $meta), $status);
    }

    public function pass(PmbOfflineQueue $queue): View
    {
        abort_unless($queue->isOfflineSelection(), 404);
        $queue->load(['session.periode', 'session.gelombang', 'user.jurusan', 'room', 'currentStage', 'stageSteps.stage', 'stageSteps.room']);

        return view('admin.pmb_queue.pass', ['queue' => $queue]);
    }

    public function export(PmbTestSession $session)
    {
        $fileName = 'laporan-antrian-' . $session->code . '.csv';
        $stages = $this->workflow->ensureDefaultStages($session);

        return Response::streamDownload(function () use ($session, $stages): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array_merge(
                ['Nomor Kedatangan', 'Nama', 'Email', 'Program Studi', 'Status Global', 'Tahap Saat Ini', 'Ruangan', 'Check-in', 'Dipanggil', 'Selesai Global'],
                $stages->map(fn (PmbQueueStage $stage) => 'Tahap ' . $stage->name)->all()
            ));

            $this->baseQueueQuery($session)
                ->orderBy('queue_number')
                ->orderBy('created_at')
                ->chunk(200, function ($queues) use ($handle, $stages): void {
                    foreach ($queues as $queue) {
                        $steps = $queue->stageSteps->keyBy('stage_id');
                        fputcsv($handle, array_merge([
                            $queue->queue_code ?? '-',
                            $queue->user->name ?? '-',
                            $queue->user->email ?? '-',
                            $queue->user->jurusan->nama_jurusan ?? '-',
                            $queue->statusLabel(),
                            $queue->currentStage->name ?? '-',
                            $queue->room->name ?? '-',
                            optional($queue->checked_in_at)->format('Y-m-d H:i:s') ?? '-',
                            optional($queue->last_called_at)->format('Y-m-d H:i:s') ?? '-',
                            optional($queue->completed_at)->format('Y-m-d H:i:s') ?? '-',
                        ], $stages->map(function (PmbQueueStage $stage) use ($steps, $queue): string {
                            $step = $steps->get($stage->id);

                            return $step ? $step->statusLabel() . ' (' . ($queue->queue_code ?? '-') . ')' : '-';
                        })->all()));
                    }
                });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    private function validateSession(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'type' => ['required', 'in:' . implode(',', array_keys(PmbTestSession::typeOptions()))],
            'periode_id' => ['nullable', 'integer', 'exists:periodes,id'],
            'gelombang_id' => ['nullable', 'integer', 'exists:gelombangs,id'],
            'tes_tulis_id' => ['nullable', 'integer', 'exists:tes_tulis,id'],
            'session_date' => ['nullable', 'date'],
            'checkin_date' => ['nullable', 'date'],
            'online_test_starts_at' => ['nullable', 'date'],
            'online_test_ends_at' => ['nullable', 'date', 'after_or_equal:online_test_starts_at'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'health_starts_at' => ['nullable', 'date'],
            'health_ends_at' => ['nullable', 'date', 'after_or_equal:health_starts_at'],
            'interview_starts_at' => ['nullable', 'date'],
            'interview_ends_at' => ['nullable', 'date', 'after_or_equal:interview_starts_at'],
            'zoom_url' => ['nullable', 'url', 'max:2000'],
            'checkin_open_at' => ['nullable', 'date'],
            'checkin_close_at' => ['nullable', 'date', 'after_or_equal:checkin_open_at'],
            'status' => ['required', 'in:' . implode(',', array_keys(PmbTestSession::statusOptions()))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $sessionDate = $validated['session_date'] ?? null;
        $checkinDate = $validated['checkin_date'] ?? null;

        unset(
            $validated['session_date'],
            $validated['checkin_date']
        );

        if ($sessionDate) {
            $validated['starts_at'] = Carbon::parse($sessionDate);
            $validated['ends_at'] = null;
        }

        if ($checkinDate) {
            $checkinOpenAt = Carbon::parse($checkinDate);
            $validated['checkin_open_at'] = $checkinOpenAt;
            $validated['checkin_close_at'] = $checkinOpenAt->copy()->endOfDay();
        }

        return $validated;
    }

    private function formData(PmbTestSession $session, string $mode): array
    {
        return [
            'session' => $session,
            'mode' => $mode,
            'periodes' => Periode::orderByDesc('created_at')->get(),
            'gelombangs' => Gelombang::orderByDesc('created_at')->get(),
            'tesTulis' => TesTulis::orderBy('nama_tes')->get(),
            'statusOptions' => PmbTestSession::statusOptions(),
            'typeOptions' => PmbTestSession::typeOptions(),
            'stageDefaults' => PmbQueueStage::defaults(),
            'configuredStages' => $session->exists ? $this->workflow->ensureDefaultStages($session)->keyBy('key') : collect(),
        ];
    }

    private function authorizedOnlineInterviewStep(PmbOfflineQueue $queue): PmbQueueStageStep
    {
        abort_unless($queue->isOnlineSelection(), 404);

        if ($this->canManageOnlineInterviewAdministration()) {
            return PmbQueueStageStep::with(['stage', 'room'])
                ->where('queue_id', $queue->id)
                ->whereHas('stage', fn ($query) => $query->where('key', PmbQueueStage::KEY_WAWANCARA_KAPRODI))
                ->firstOrFail();
        }

        $step = PmbQueueStageStep::with(['stage', 'room'])
            ->where('queue_id', $queue->id)
            ->where('assigned_officer_id', auth('admin')->id())
            ->whereHas('stage', fn ($query) => $query->where('key', PmbQueueStage::KEY_WAWANCARA_KAPRODI))
            ->firstOrFail();

        return $step;
    }

    private function syncStageConfig(Request $request, PmbTestSession $session): void
    {
        if (!$request->has('stage_active')) {
            return;
        }

        $active = $request->input('stage_active', []);
        $required = $request->input('stage_required', []);

        foreach ($this->workflow->ensureDefaultStages($session) as $stage) {
            $stage->update([
                'is_active' => array_key_exists($stage->key, $active),
                'is_required' => array_key_exists($stage->key, $required),
            ]);
        }
    }

    private function sessionViewData(Request $request, PmbTestSession $session, bool $includeAvailableParticipants = true): array
    {
        $this->workflow->ensureDefaultStages($session);
        $this->autoDispatchForCurrentOfficer($session);

        $queues = $this->baseQueueQuery($session)
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('room_id'), fn ($query) => $query->where('room_id', $request->room_id))
            ->when($request->filled('stage_id') || $request->filled('stage_status'), function ($query) use ($request): void {
                $query->whereHas('stageSteps', function ($stepQuery) use ($request): void {
                    if ($request->filled('stage_id')) {
                        $stepQuery->where('stage_id', $request->stage_id);
                    }
                    if ($request->filled('stage_status')) {
                        $stepQuery->where('status', $request->stage_status);
                    }
                });
            })
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->search;
                $query->where(function ($q) use ($search): void {
                    $q->where('queue_code', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search): void {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByRaw('CASE WHEN queue_number IS NULL THEN 1 ELSE 0 END')
            ->orderBy('queue_number')
            ->orderBy('created_at')
            ->paginate(25)
            ->withQueryString();
        $queues->setPath(route('admin.pmb-queues.show', $session));

        $availableParticipants = collect();
        if ($includeAvailableParticipants) {
            $usedUserIds = $session->queues()->pluck('user_id');
            $effectivePeriodeId = $session->periode_id ?? Periode::where('status_periode', 'aktif')->value('id');
            $availableParticipants = User::query()
                ->where('role', User::USER_ROLE)
                ->isPmbVerified()
                ->when($effectivePeriodeId, fn ($query) => $query->where('periode_id', $effectivePeriodeId))
                ->whereNotIn('id', $usedUserIds)
                ->with('jurusan')
                ->orderBy('name')
                ->limit(300)
                ->get();
        }

        $boardPayload = $this->workflow->boardPayload($session);

        return [
            'session' => $session->loadMissing(['rooms.stage', 'rooms.assignedOfficer.jurusan', 'rooms.stageSteps.queue.user', 'periode', 'gelombang', 'tesTulis', 'stages']),
            'queues' => $queues,
            'rooms' => $session->rooms,
            'stages' => $session->stages,
            'availableParticipants' => $availableParticipants,
            'statusOptions' => PmbOfflineQueue::statusOptions(),
            'stageStatusOptions' => PmbQueueStageStep::statusOptions(),
            'roomStatusOptions' => PmbTestRoom::statusOptions(),
            'healthOfficers' => Admin::role(AdminPermissions::LEGACY_ROLE_PETUGAS_MEDIS)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
            'interviewers' => Admin::query()
                ->where('is_active', true)
                ->whereHas('roles', fn ($query) => $query->whereIn('name', [
                    AdminPermissions::ROLE_DOSEN,
                    AdminPermissions::LEGACY_ROLE_DOSEN_PEWAWANCARA,
                ]))
                ->with('jurusan:id,nama_jurusan')
                ->orderBy('name')
                ->get(['id', 'jurusan_id', 'name', 'email']),
            'stats' => $this->queueStats($session),
            'boardPayload' => $boardPayload,
            'stageBoard' => collect($boardPayload['stages']),
            'recentLogs' => PmbQueueStatusLog::with(['queue.user', 'admin'])
                ->where('session_id', $session->id)
                ->latest()
                ->limit(8)
                ->get(),
            'canManageQueue' => $this->canManageQueue(),
            'canManageOnlineSelection' => $this->canManageOnlineSelection(),
            'canScanQueue' => auth('admin')->user()?->can(AdminPermissions::PMB_QUEUE_SCAN) || $this->canManageQueue(),
            'canManualOverride' => $this->canManualOverride(),
            'canOperateTesTulis' => $this->canOperateStageKey(PmbQueueStage::KEY_TES_TULIS),
            'canOperateHealth' => $this->canOperateStageKey(PmbQueueStage::KEY_TES_KESEHATAN),
            'canOperateInterview' => $this->canOperateStageKey(PmbQueueStage::KEY_WAWANCARA_KAPRODI),
            'isMonitorMode' => true,
        ];
    }

    private function baseQueueQuery(PmbTestSession $session)
    {
        return $this->scopeQueuesForOnlineManager(PmbOfflineQueue::with(['user.jurusan', 'user.gelombang', 'room', 'currentStage', 'stageSteps.stage', 'stageSteps.room', 'stageSteps.officer']))
            ->where('session_id', $session->id)
            ->whereHas('user', fn ($query) => $query->isPmbVerified());
    }

    private function validatedRoomData(Request $request, PmbTestSession $session, ?PmbTestRoom $room = null): array
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'stage_id' => ['nullable', 'integer', 'exists:pmb_queue_stages,id'],
            'assigned_admin_id' => ['nullable', 'integer', 'exists:admins,id'],
            'status' => ['required', 'in:' . implode(',', array_keys(PmbTestRoom::statusOptions()))],
        ]);

        $stage = !empty($validated['stage_id']) ? PmbQueueStage::find($validated['stage_id']) : null;
        if ($stage && $stage->session_id !== $session->id) {
            throw ValidationException::withMessages(['stage_id' => 'Tahap tidak termasuk dalam sesi ini.']);
        }

        if ($stage?->key === PmbQueueStage::KEY_TES_KESEHATAN) {
            if (empty($validated['assigned_admin_id'])) {
                throw ValidationException::withMessages(['assigned_admin_id' => 'Pilih akun petugas medis untuk loket ini.']);
            }

            $officer = Admin::whereKey($validated['assigned_admin_id'])
                ->where('is_active', true)
                ->first();

            if (!$officer || !$officer->hasRole(AdminPermissions::LEGACY_ROLE_PETUGAS_MEDIS)) {
                throw ValidationException::withMessages(['assigned_admin_id' => 'Akun yang dipilih bukan Petugas Medis aktif.']);
            }

            $alreadyAssigned = PmbTestRoom::where('session_id', $session->id)
                ->where('stage_id', $stage->id)
                ->where('assigned_admin_id', $officer->id)
                ->when($room, fn ($query) => $query->where('id', '<>', $room->id))
                ->exists();

            if ($alreadyAssigned) {
                throw ValidationException::withMessages(['assigned_admin_id' => 'Akun petugas ini sudah memiliki loket pada sesi tersebut.']);
            }

            $validated['name'] = filled($validated['name'] ?? null)
                ? trim((string) $validated['name'])
                : 'Petugas Kesehatan ' . $officer->name;
        } elseif ($stage?->key === PmbQueueStage::KEY_WAWANCARA_KAPRODI) {
            if (empty($validated['assigned_admin_id'])) {
                throw ValidationException::withMessages(['assigned_admin_id' => 'Pilih akun dosen pewawancara untuk meja ini.']);
            }

            $officer = Admin::whereKey($validated['assigned_admin_id'])->where('is_active', true)->first();
            if (!$officer || !$officer->hasAnyRole([
                AdminPermissions::ROLE_DOSEN,
                AdminPermissions::LEGACY_ROLE_DOSEN_PEWAWANCARA,
            ])) {
                throw ValidationException::withMessages(['assigned_admin_id' => 'Akun yang dipilih bukan Dosen Pewawancara aktif.']);
            }
            if (!$officer->jurusan_id) {
                throw ValidationException::withMessages(['assigned_admin_id' => 'Program studi akun dosen belum ditentukan.']);
            }

            $alreadyAssigned = PmbTestRoom::where('session_id', $session->id)
                ->where('stage_id', $stage->id)
                ->where('assigned_admin_id', $officer->id)
                ->when($room, fn ($query) => $query->where('id', '<>', $room->id))
                ->exists();
            if ($alreadyAssigned) {
                throw ValidationException::withMessages(['assigned_admin_id' => 'Akun dosen ini sudah memiliki meja pada sesi tersebut.']);
            }

            $validated['name'] = $room?->name ?: 'Meja Wawancara';
        } else {
            if (empty(trim((string) ($validated['name'] ?? '')))) {
                throw ValidationException::withMessages(['name' => 'Nama loket wajib diisi.']);
            }

            $validated['assigned_admin_id'] = null;
        }

        return $validated;
    }

    private function nextRoomNumber(PmbTestSession $session, string $prefix): int
    {
        $usedNumbers = $session->rooms()
            ->where('code', 'like', $prefix . '-%')
            ->pluck('code')
            ->map(function (string $code) use ($prefix): ?int {
                return preg_match('/^' . preg_quote($prefix, '/') . '-(\d+)$/', $code, $matches)
                    ? (int) $matches[1]
                    : null;
            })
            ->filter(fn (?int $number) => $number !== null);

        return ((int) $usedNumbers->max()) + 1;
    }

    private function officerViewData(PmbTestSession $session, string $stageKey, array $extra = []): array
    {
        $stage = PmbQueueStage::where('session_id', $session->id)
            ->where('key', $stageKey)
            ->first();
        $jurusanIds = $stageKey === PmbQueueStage::KEY_WAWANCARA_KAPRODI
            ? auth('admin')->user()?->loadMissing('jurusan')->jurusan?->interviewPoolIds() ?? []
            : [];
        $countSteps = fn (array $statuses): int => $this->stageSteps($session, $stageKey, $statuses)
            ->when($jurusanIds, fn ($query) => $query->whereHas('queue.user', fn ($userQuery) => $userQuery->whereIn('jurusan_id', $jurusanIds)))
            ->count();
        $remainingStatuses = [
            PmbQueueStageStep::STATUS_PENDING,
            PmbQueueStageStep::STATUS_WAITING,
            PmbQueueStageStep::STATUS_CALLED,
            PmbQueueStageStep::STATUS_PROCESSING,
            PmbQueueStageStep::STATUS_REENTRY,
        ];
        $remainingCount = $countSteps($remainingStatuses);
        $completedCount = $countSteps([PmbQueueStageStep::STATUS_COMPLETED]);

        return array_merge([
            'session' => $session->loadMissing(['periode', 'gelombang', 'tesTulis', 'stages']),
            'stage' => $stage,
            'stats' => [
                'waiting' => $countSteps([PmbQueueStageStep::STATUS_WAITING]),
                'processing' => $countSteps([PmbQueueStageStep::STATUS_PROCESSING, PmbQueueStageStep::STATUS_CALLED]),
                'completed' => $completedCount,
                'remaining' => $remainingCount,
                'total_program' => $remainingCount + $completedCount,
            ],
        ], $extra);
    }

    private function interviewSessionSummaries(array $sessionIds, Admin $admin): array
    {
        if (empty($sessionIds)) {
            return [];
        }

        $jurusanIds = $admin->loadMissing('jurusan')->jurusan?->interviewPoolIds() ?? [];

        if (!$jurusanIds) {
            return [];
        }

        $rows = PmbQueueStageStep::query()
            ->select('pmb_queue_stages.session_id', 'pmb_queue_stage_steps.status', DB::raw('count(*) as total'))
            ->join('pmb_queue_stages', 'pmb_queue_stages.id', '=', 'pmb_queue_stage_steps.stage_id')
            ->join('pmb_offline_queues', 'pmb_offline_queues.id', '=', 'pmb_queue_stage_steps.queue_id')
            ->join('users', 'users.id', '=', 'pmb_offline_queues.user_id')
            ->whereIn('pmb_queue_stages.session_id', $sessionIds)
            ->where('pmb_queue_stages.key', PmbQueueStage::KEY_WAWANCARA_KAPRODI)
            ->where('pmb_offline_queues.selection_mode', PmbOfflineQueue::MODE_OFFLINE)
            ->whereNotNull('pmb_offline_queues.checked_in_at')
            ->whereNotIn('pmb_offline_queues.status', [
                PmbOfflineQueue::STATUS_COMPLETED,
                PmbOfflineQueue::STATUS_CANCELLED,
                PmbOfflineQueue::STATUS_NO_SHOW,
            ])
            ->whereIn('users.jurusan_id', $jurusanIds)
            ->groupBy('pmb_queue_stages.session_id', 'pmb_queue_stage_steps.status')
            ->get();
        $remainingStatuses = [
            PmbQueueStageStep::STATUS_PENDING,
            PmbQueueStageStep::STATUS_WAITING,
            PmbQueueStageStep::STATUS_CALLED,
            PmbQueueStageStep::STATUS_PROCESSING,
            PmbQueueStageStep::STATUS_REENTRY,
        ];

        return $rows
            ->groupBy('session_id')
            ->map(function ($sessionRows) use ($remainingStatuses): array {
                $counts = $sessionRows->pluck('total', 'status');
                $remaining = collect($remainingStatuses)->sum(fn (string $status): int => (int) $counts->get($status, 0));
                $completed = (int) $counts->get(PmbQueueStageStep::STATUS_COMPLETED, 0);

                return [
                    'remaining' => $remaining,
                    'waiting' => (int) $counts->get(PmbQueueStageStep::STATUS_WAITING, 0),
                    'processing' => (int) $counts->get(PmbQueueStageStep::STATUS_PROCESSING, 0) + (int) $counts->get(PmbQueueStageStep::STATUS_CALLED, 0),
                    'completed' => $completed,
                    'total' => $remaining + $completed,
                ];
            })
            ->all();
    }

    private function programInterviewRoomForOfficer(PmbTestSession $session, PmbQueueStage $stage, Admin $officer): ?PmbTestRoom
    {
        $program = strtoupper((string) $officer->jurusan?->nama_jurusan);
        $code = match (true) {
            str_contains($program, 'KEBIDANAN') => 'W-D3-KEB',
            str_contains($program, 'FARMASI') => 'W-S1-FAR',
            str_contains($program, 'GIZI') => 'W-S1-GIZ',
            default => null,
        };

        $query = PmbTestRoom::where('session_id', $session->id)
            ->where('stage_id', $stage->id);

        if ($code) {
            $room = (clone $query)->where('code', $code)->first();

            if ($room) {
                return $room;
            }
        }

        return (clone $query)
            ->whereNull('assigned_admin_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first()
            ?: $query->orderBy('sort_order')->orderBy('id')->first();
    }

    private function currentOfficerStep(PmbTestSession $session, string $stageKey): ?PmbQueueStageStep
    {
        return $this->stageSteps($session, $stageKey, [
            PmbQueueStageStep::STATUS_PROCESSING,
            PmbQueueStageStep::STATUS_CALLED,
        ])
            ->where('assigned_officer_id', auth('admin')->id())
            ->orderBy('started_at')
            ->orderBy('called_at')
            ->first();
    }

    private function stageSteps(PmbTestSession $session, string $stageKey, array $statuses)
    {
        return PmbQueueStageStep::with(['queue.user.jurusan', 'stage', 'room'])
            ->whereHas('stage', fn ($query) => $query
                ->where('session_id', $session->id)
                ->where('key', $stageKey))
            ->whereIn('status', $statuses)
            ->whereHas('queue', fn ($query) => $query
                ->where('session_id', $session->id)
                ->whereNotNull('checked_in_at')
                ->whereNotIn('status', [
                    PmbOfflineQueue::STATUS_COMPLETED,
                    PmbOfflineQueue::STATUS_CANCELLED,
                    PmbOfflineQueue::STATUS_NO_SHOW,
                ]))
            ->orderBy(PmbOfflineQueue::select('queue_number')
                ->whereColumn('pmb_offline_queues.id', 'pmb_queue_stage_steps.queue_id')
                ->limit(1))
            ->orderBy('queued_at')
            ->orderBy('id');
    }

    private function queueStats(PmbTestSession $session): array
    {
        $verifiedQueues = $this->scopeQueuesForOnlineManager(PmbOfflineQueue::where('session_id', $session->id))
            ->whereHas('user', fn ($query) => $query->isPmbVerified());

        $counts = (clone $verifiedQueues)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total' => (clone $verifiedQueues)->count(),
            'registered' => $counts->get(PmbOfflineQueue::STATUS_REGISTERED, 0),
            'checked_in' => (clone $verifiedQueues)->whereNotNull('checked_in_at')->count(),
            'waiting' => $counts->get(PmbOfflineQueue::STATUS_WAITING, 0) + $counts->get(PmbOfflineQueue::STATUS_CHECKED_IN, 0),
            'called' => $counts->get(PmbOfflineQueue::STATUS_CALLED, 0),
            'in_room' => $counts->get(PmbOfflineQueue::STATUS_IN_ROOM, 0),
            'completed' => $counts->get(PmbOfflineQueue::STATUS_COMPLETED, 0),
            'skipped' => $counts->get(PmbOfflineQueue::STATUS_SKIPPED, 0),
            'no_show' => $counts->get(PmbOfflineQueue::STATUS_NO_SHOW, 0),
        ];
    }

    private function findQueueFromCode(PmbTestSession $session, string $code): ?PmbOfflineQueue
    {
        $code = trim($code);
        $uuid = $code;

        if (preg_match('/check-in\/([a-f0-9-]+)/i', $code, $matches)) {
            $uuid = $matches[1];
        }

        return PmbOfflineQueue::where('session_id', $session->id)
            ->where(function ($query) use ($code, $uuid): void {
                $query->where('uuid', $uuid)
                    ->orWhere('queue_code', $code)
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('code', $code)->orWhere('email', $code));
            })
            ->first();
    }

    private function checkInAndRedirect(PmbOfflineQueue $queue): RedirectResponse
    {
        $wasCheckedIn = (bool) $queue->checked_in_at;
        try {
            $checkedIn = $this->workflow->checkIn($queue, auth('admin')->id());
        } catch (DomainException $e) {
            return redirect()
                ->route('admin.pmb-queues.show', $queue->session)
                ->with('error', $e->getMessage());
        }
        $message = $wasCheckedIn
            ? 'Peserta sudah check-in sebelumnya.'
            : "Check-in berhasil. Nomor antrian {$checkedIn->queue_code}. Peserta masuk tahap {$checkedIn->currentStage?->name}.";

        $route = auth('admin')->user()?->can(AdminPermissions::PMB_QUEUE_SCAN)
            && !auth('admin')->user()?->can(AdminPermissions::PMB_EDIT)
            ? 'admin.pmb-queues.scan'
            : 'admin.pmb-queues.show';

        return redirect()
            ->route($route, $queue->session)
            ->with('success', $message);
    }

    private function transition(PmbOfflineQueue $queue, string $toStatus, string $note, array $extra = [], bool $writeCallLog = false): void
    {
        DB::transaction(function () use ($queue, $toStatus, $note, $extra, $writeCallLog): void {
            $locked = PmbOfflineQueue::whereKey($queue->id)->lockForUpdate()->firstOrFail();
            $fromStatus = $locked->status;

            $locked->update(array_merge(['status' => $toStatus], $extra));

            $this->writeStatusLog($locked, $fromStatus, $toStatus, $note);

            if ($writeCallLog) {
                PmbQueueCallLog::create([
                    'session_id' => $locked->session_id,
                    'queue_id' => $locked->id,
                    'room_id' => $locked->room_id,
                    'admin_id' => auth('admin')->id(),
                    'action' => $toStatus === PmbOfflineQueue::STATUS_CALLED ? 'call' : 'status',
                    'from_status' => $fromStatus,
                    'to_status' => $toStatus,
                    'call_number' => max(1, $locked->call_count),
                    'called_at' => now(),
                    'metadata' => ['note' => $note],
                ]);
            }

            $this->activityLogger->log(
                'pmb_queue',
                'participant_status_updated',
                $note,
                $locked,
                ['status' => $fromStatus],
                ['status' => $toStatus]
            );

            event(new PmbQueueUpdated($locked->fresh(['user.jurusan', 'room', 'session']), $toStatus));
        });
    }

    private function canManageQueue(): bool
    {
        return (bool) auth('admin')->user()?->can(AdminPermissions::PMB_EDIT);
    }

    private function canManageOnlineSelection(): bool
    {
        $admin = auth('admin')->user();

        return (bool) ($admin?->can(AdminPermissions::PMB_EDIT)
            || $admin?->can(AdminPermissions::PMB_QUEUE_ONLINE_MANAGE));
    }

    private function isOnlineSelectionManagerOnly(): bool
    {
        $admin = auth('admin')->user();

        return (bool) ($admin?->can(AdminPermissions::PMB_QUEUE_ONLINE_MANAGE)
            && !$admin?->can(AdminPermissions::PMB_EDIT));
    }

    private function canManageOnlineInterviewAdministration(): bool
    {
        $admin = auth('admin')->user();

        return (bool) ($admin?->can(AdminPermissions::PMB_QUEUE_ONLINE_MANAGE)
            && !$admin?->can(AdminPermissions::PMB_EDIT)
            && !$admin?->can(AdminPermissions::PMB_QUEUE_WAWANCARA)
            && !$admin?->can(AdminPermissions::WAWANCARA_REVIEW));
    }

    private function scopeQueuesForOnlineManager($query)
    {
        return $this->isOnlineSelectionManagerOnly()
            ? $query->where('selection_mode', PmbOfflineQueue::MODE_ONLINE)
            : $query;
    }

    private function abortIfOnlineManagerCannotAccessSession(PmbTestSession $session): void
    {
        if (!$this->isOnlineSelectionManagerOnly()) {
            return;
        }

        abort_unless($session->queues()
            ->where('selection_mode', PmbOfflineQueue::MODE_ONLINE)
            ->exists(), 404);
    }

    private function canManualOverride(): bool
    {
        return $this->canManageQueue();
    }

    private function canOperateStage(?PmbQueueStage $stage, ?PmbTestRoom $room = null): bool
    {
        if ($this->canManageQueue()) {
            return true;
        }

        if (!$stage) {
            return false;
        }

        return $this->canOperateStageKey($stage->key);
    }

    private function canOperateStageKey(?string $stageKey): bool
    {
        $admin = auth('admin')->user();

        if (!$admin) {
            return false;
        }

        if ($admin->can(AdminPermissions::PMB_EDIT)) {
            return true;
        }

        return match ($stageKey) {
            PmbQueueStage::KEY_TES_TULIS => $admin->can(AdminPermissions::PMB_QUEUE_TES_TULIS),
            PmbQueueStage::KEY_TES_KESEHATAN => $admin->can(AdminPermissions::PMB_QUEUE_KESEHATAN),
            PmbQueueStage::KEY_WAWANCARA_KAPRODI => $admin->can(AdminPermissions::PMB_QUEUE_WAWANCARA),
            default => false,
        };
    }

    private function isManualOverrideAction(string $action): bool
    {
        return in_array($action, [
            'cancel',
            'no_show',
            'reactivate',
            'hold',
            'release',
            'problem',
            'skip',
            'recall',
            'hold_health_form',
            'release_health_form',
        ], true);
    }

    private function autoDispatchForCurrentOfficer(PmbTestSession $session): void
    {
        $adminId = auth('admin')->id();

        if (!$adminId || $this->canManageQueue()) {
            return;
        }

        try {
            if ($this->canOperateStageKey(PmbQueueStage::KEY_TES_KESEHATAN)) {
                $this->workflow->autoDispatchForOfficer($session, PmbQueueStage::KEY_TES_KESEHATAN, $adminId);
            }

        } catch (DomainException) {
            // Auto-dispatch is opportunistic; the UI stays usable if no queue is ready.
        }
    }

    private function writeStatusLog(PmbOfflineQueue $queue, ?string $fromStatus, string $toStatus, ?string $note = null): void
    {
        PmbQueueStatusLog::create([
            'session_id' => $queue->session_id,
            'queue_id' => $queue->id,
            'admin_id' => auth('admin')->id(),
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'note' => $note,
        ]);
    }
}
