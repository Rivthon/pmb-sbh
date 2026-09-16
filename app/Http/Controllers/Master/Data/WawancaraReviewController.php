<?php

namespace App\Http\Controllers\Master\Data;

use App\Models\WawancaraPmb;
use App\Models\PmbOfflineQueue;
use App\Models\PmbQueueStage;
use App\Models\PmbQueueStageStep;
use App\Services\Pmb\PmbOfflineQueueWorkflowService;
use App\Support\AdminPermissions;
use App\Services\Pmb\PmbOnlineSelectionService;
use DomainException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Support\PmbOnlineSelectionAccess;


class WawancaraReviewController extends Controller
{
    public function __construct(
        private readonly PmbOfflineQueueWorkflowService $workflow,
        private readonly PmbOnlineSelectionService $onlineSelection,
    )
    {
    }

    /**
     * =========================
     * LIST WAWANCARA
     * =========================
     */
    public function index(Request $request)
    {
        $query = WawancaraPmb::query()
            ->with([
                'user:id,name,email,jurusan_id',
                'user.jurusan:id,nama_jurusan',
                'pewawancara:id,name',
            ])
            ->whereIn('status', ['submitted', 'reviewed', 'locked']);

        if ($this->isOnlineWawancaraOnly()) {
            PmbOnlineSelectionAccess::constrainToLatestOnlineUsers($query, 'calon_mahasiswa_id');
        }

        if ($this->isInterviewOfficerOnly()) {
            $admin = auth('admin')->user()->loadMissing('jurusan');
            $jurusanIds = $admin->jurusan?->interviewPoolIds() ?? [];

            if (!$jurusanIds) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where(function ($officerQuery) use ($admin, $jurusanIds): void {
                    PmbOnlineSelectionAccess::constrainToLatestOnlineUsers($officerQuery, 'calon_mahasiswa_id');
                    $officerQuery
                        ->whereHas('user', fn ($userQuery) => $userQuery->whereIn('jurusan_id', $jurusanIds))
                        ->whereHas('user.pmbOfflineQueues', fn ($queueQuery) => $queueQuery
                            ->where('selection_mode', PmbOfflineQueue::MODE_ONLINE)
                            ->whereHas('stageSteps', fn ($stepQuery) => $stepQuery
                                ->whereHas('stage', fn ($stageQuery) => $stageQuery->where('key', PmbQueueStage::KEY_WAWANCARA_KAPRODI))
                                ->where(fn ($claimQuery) => $claimQuery
                                    ->whereNull('assigned_officer_id')
                                    ->orWhere('assigned_officer_id', $admin->id))));
                });
            }
        }

        // 🔍 SEARCH
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })

                    ->orWhereHas('user.jurusan', function ($jq) use ($search) {
                        $jq->where('nama_jurusan', 'like', "%{$search}%");
                    })

                    ->orWhereHas('pewawancara', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $data = $query->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.wawancara.index', compact('data'));
    }

    /**
     * =========================
     * SHOW (READ ONLY)
     * =========================
     */
    public function show(WawancaraPmb $wawancara)
    {
        $wawancara->load([
            'user.jurusan',
            'pewawancara',
        ]);
        $this->abortIfCannotAccessWawancara($wawancara);

        return view('admin.wawancara.show', compact('wawancara'));
    }

    /**
     * =========================
     * REVIEW KAPRODI
     * =========================
     */
    public function edit(Request $request, WawancaraPmb $wawancara)
    {
        $this->abortIfOnlineManagerOnlyReviewer();

        if ($wawancara->status === 'locked') {
            abort(403, 'Wawancara sudah dikunci.');
        }

        $wawancara->load(['user.jurusan', 'pewawancara']);
        $this->authorizeInterviewAssignment($request, $wawancara);
        $queue = $this->resolveInterviewQueue($request, $wawancara);

        return view('admin.wawancara.review', compact('wawancara', 'queue'));
    }


    public function review(Request $request, WawancaraPmb $wawancara)
    {
        $this->abortIfOnlineManagerOnlyReviewer();

        // ⛔ Tidak boleh review jika sudah final
        if ($wawancara->status === 'locked') {
            abort(403, 'Wawancara sudah dikunci.');
        }

        // ⛔ Review hanya boleh dari status submitted
        if ($wawancara->status !== 'submitted') {
            abort(403, 'Wawancara tidak dapat direview.');
        }

        $this->authorizeInterviewAssignment($request, $wawancara);

        $validated = $request->validate([
            'kesimpulan_kaprodi' => 'required|string|min:10',
            'rekomendasi'        => 'required|in:direkomendasikan,direkomendasikan_bersyarat,tidak_direkomendasikan',
        ]);

        $wawancara->update([
            'kesimpulan_kaprodi' => $validated['kesimpulan_kaprodi'],
            'rekomendasi'        => $validated['rekomendasi'],
            'kaprodi_id'         => auth('admin')->id(),
            'tanggal_review'     => now(),
            'status'             => 'reviewed',
        ]);

        $queue = $this->completeInterviewStep($request, $wawancara);

        if ($queue) {
            $route = $queue->isOnlineSelection()
                ? 'admin.pmb-queues.officer.wawancara-online'
                : 'admin.pmb-queues.officer.wawancara';
            $message = $queue->isOnlineSelection()
                ? 'Hasil wawancara online berhasil disimpan.'
                : 'Hasil wawancara berhasil disimpan. Tahap peserta selesai dan sistem akan mengambil peserta berikutnya jika tersedia.';

            return redirect()->route($route, $queue->session)->with('success', $message);
        }

        return redirect()
            ->route('admin.wawancara.show', $wawancara)
            ->with('success', 'Hasil wawancara berhasil disimpan.');
    }


    /**
     * =========================
     * LOCK (FINAL)
     * =========================
     */
    public function lock(WawancaraPmb $wawancara)
    {
        $this->abortIfOnlineManagerOnlyReviewer();
        abort_if($this->isInterviewOfficerOnly(), 403, 'Dosen tidak dapat mengunci hasil final.');

        // ⛔ Hanya boleh lock setelah review
        if ($wawancara->status !== 'reviewed') {
            abort(403, 'Wawancara harus direview terlebih dahulu.');
        }

        $wawancara->update([
            'status' => 'locked',
        ]);

        return redirect()
            ->route('admin.wawancara.index')
            ->with('success', 'Hasil wawancara berhasil dikunci (final).');
    }

    private function completeInterviewStep(Request $request, WawancaraPmb $wawancara): ?PmbOfflineQueue
    {
        if (!$request->filled('queue_uuid')) {
            return null;
        }

        $queue = PmbOfflineQueue::with(['session', 'stageSteps.stage'])
            ->where('uuid', $request->queue_uuid)
            ->where('user_id', $wawancara->calon_mahasiswa_id)
            ->first();

        if (!$queue) {
            return null;
        }

        if ($queue->isOnlineSelection()) {
            $this->onlineSelection->markInterviewCompleted($queue, auth('admin')->id());
            return $queue->fresh(['session']);
        }

        $step = $queue->stageSteps
            ->first(fn (PmbQueueStageStep $stageStep): bool => $stageStep->stage?->key === PmbQueueStage::KEY_WAWANCARA_KAPRODI
                && in_array($stageStep->status, [PmbQueueStageStep::STATUS_PROCESSING, PmbQueueStageStep::STATUS_CALLED], true));

        if (!$step) {
            return $queue;
        }

        try {
            $this->workflow->updateStep($step, 'complete', null, auth('admin')->id(), 'Hasil wawancara offline disimpan oleh dosen.');
        } catch (DomainException) {
            return $queue;
        }

        return $queue->fresh(['session']);
    }

    private function authorizeInterviewAssignment(Request $request, WawancaraPmb $wawancara): void
    {
        if (!$request->filled('queue_uuid')) {
            $latestQueue = PmbOfflineQueue::where('user_id', $wawancara->calon_mahasiswa_id)->latest()->latest('id')->first();
            if ($latestQueue?->isOnlineSelection()) {
                abort(403, 'Review peserta online hanya dapat dibuka dari daftar penugasan dosen.');
            }
            return;
        }

        $admin = auth('admin')->user();
        $queue = PmbOfflineQueue::with(['user', 'stageSteps.stage', 'stageSteps.room'])
            ->where('uuid', $request->queue_uuid)
            ->where('user_id', $wawancara->calon_mahasiswa_id)
            ->firstOrFail();

        $allowedStatuses = $queue->isOnlineSelection()
            ? [PmbQueueStageStep::STATUS_WAITING, PmbQueueStageStep::STATUS_PROCESSING, PmbQueueStageStep::STATUS_REENTRY]
            : [PmbQueueStageStep::STATUS_PROCESSING, PmbQueueStageStep::STATUS_CALLED];
        $step = $queue->stageSteps
            ->first(fn (PmbQueueStageStep $stageStep): bool => $stageStep->stage?->key === PmbQueueStage::KEY_WAWANCARA_KAPRODI
                && in_array($stageStep->status, $allowedStatuses, true));

        if (!$step || (int) $step->assigned_officer_id !== (int) $admin?->id) {
            abort(403, 'Peserta ini tidak ditugaskan ke meja wawancara Anda.');
        }

        if (!$queue->isOnlineSelection()
            && $step->room?->assigned_admin_id
            && (int) $step->room->assigned_admin_id !== (int) $admin?->id) {
            abort(403, 'Peserta ini tidak ditugaskan ke meja wawancara Anda.');
        }

        $isInterviewOnly = $admin->can(AdminPermissions::PMB_QUEUE_WAWANCARA)
            && !$admin->can(AdminPermissions::PMB_EDIT);

        $jurusanIds = $admin->loadMissing('jurusan')->jurusan?->interviewPoolIds() ?? [];

        if ($isInterviewOnly
            && (!$jurusanIds || !in_array((int) $queue->user?->jurusan_id, $jurusanIds, true))) {
            abort(403, 'Program studi peserta tidak sesuai dengan akun dosen.');
        }
    }

    private function resolveInterviewQueue(Request $request, WawancaraPmb $wawancara): ?PmbOfflineQueue
    {
        if ($request->filled('queue_uuid')) {
            return PmbOfflineQueue::where('uuid', $request->queue_uuid)
                ->where('user_id', $wawancara->calon_mahasiswa_id)
                ->first();
        }

        return PmbOfflineQueue::where('user_id', $wawancara->calon_mahasiswa_id)
            ->latest()
            ->latest('id')
            ->first();
    }

    private function isOnlineWawancaraOnly(): bool
    {
        $admin = auth('admin')->user();

        return (bool) ($admin?->can(AdminPermissions::PMB_ONLINE_WAWANCARA_MANAGE)
            && !$admin?->can(AdminPermissions::WAWANCARA_REVIEW));
    }

    private function abortIfCannotAccessWawancara(WawancaraPmb $wawancara): void
    {
        if ($this->isOnlineWawancaraOnly()) {
            PmbOnlineSelectionAccess::abortUnlessLatestOnlineUser($wawancara->calon_mahasiswa_id);
        }

        if ($this->isInterviewOfficerOnly()) {
            $this->abortUnlessInterviewOfficerCanSee($wawancara);
        }
    }

    private function abortIfOnlineManagerOnlyReviewer(): void
    {
        if ($this->isOnlineWawancaraOnly()) {
            abort(403, 'PJ Seleksi Online hanya dapat melihat dan mengelola status administrasi wawancara online.');
        }
    }

    private function isInterviewOfficerOnly(): bool
    {
        $admin = auth('admin')->user();

        return (bool) ($admin?->can(AdminPermissions::PMB_QUEUE_WAWANCARA)
            && !$admin?->can(AdminPermissions::PMB_EDIT)
            && !$admin?->can(AdminPermissions::WAWANCARA_REVIEW)
            && !$admin?->can(AdminPermissions::PMB_ONLINE_WAWANCARA_MANAGE));
    }

    private function abortUnlessInterviewOfficerCanSee(WawancaraPmb $wawancara): void
    {
        $admin = auth('admin')->user()->loadMissing('jurusan');
        $jurusanIds = $admin->jurusan?->interviewPoolIds() ?? [];

        abort_if(!$jurusanIds, 403, 'Program studi akun dosen belum ditentukan.');

        $queue = PmbOfflineQueue::with(['user', 'stageSteps.stage'])
            ->where('user_id', $wawancara->calon_mahasiswa_id)
            ->latest()
            ->latest('id')
            ->first();

        abort_unless($queue?->isOnlineSelection(), 403);
        abort_unless(in_array((int) $queue->user?->jurusan_id, $jurusanIds, true), 403, 'Program studi peserta tidak sesuai dengan akun dosen.');

        $step = $queue->stageSteps
            ->first(fn (PmbQueueStageStep $stageStep): bool => $stageStep->stage?->key === PmbQueueStage::KEY_WAWANCARA_KAPRODI);

        abort_if($step?->assigned_officer_id && (int) $step->assigned_officer_id !== (int) $admin->id, 403, 'Peserta ini tidak ditugaskan ke meja wawancara Anda.');
    }
}
