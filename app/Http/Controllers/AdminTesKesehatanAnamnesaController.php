<?php

namespace App\Http\Controllers;

use App\Models\PmbOfflineQueue;
use App\Models\PmbQueueStage;
use App\Models\PmbQueueStageStep;
use App\Services\Pmb\PmbOfflineQueueWorkflowService;
use DomainException;
use Illuminate\Http\Request;
use App\Models\TesKesehatanAnamnesa;
use App\Models\TesKesehatanPemeriksaan;
use App\Support\AdminPermissions;
use App\Support\PmbOnlineSelectionAccess;
use PDF;

class AdminTesKesehatanAnamnesaController extends Controller
{
    private const OFFICIAL_PHYSICAL_EXAM_ITEMS = [
        'konjungtiva',
        'ikhterik',
        'buta_warna',
        'respon_pendengaran',
        'kelengkapan_jari_atas',
        'tremor',
        'bekas_luka_sayatan',
        'kidal',
        'labioschizis',
        'vokal',
        'cat_rambut',
        'tyroid',
        'bunyi_irama_jantung',
        'bunyi_paru',
        'respirasi',
        'simetris_bawah',
        'kelengkapan_jari_bawah',
        'motorik_cara_jalan',
        'bentuk_kaki',
        'tes_urine',
    ];

    private const ANAMNESA_FIELDS = [
        'riwayat_penyakit_keluarga',
        'riwayat_penyakit_pribadi',
        'riwayat_operasi',
        'konsumsi_obat_rutin',
        'penyakit_menular',
        'masalah_kulit',
        'tumor_benjolan',
        'epilepsi',
        'cedera_kepala',
        'batuk_kronis',
        'gangguan_pencernaan',
        'gangguan_keseimbangan',
        'claustrophobia',
        'takut_darah',
        'kacamata',
        'gagap',
        'alat_bantu_tulang',
        'lemah_otot',
        'pikiran_bunuh_diri',
        'kelainan_darah',
        'riwayat_psikolog',
    ];

    public function __construct(private readonly PmbOfflineQueueWorkflowService $workflow)
    {
    }

    /**
     * Tampilkan daftar seluruh hasil anamnesa.
     */
    /**
     * Tampilkan daftar seluruh hasil anamnesa.
     */
    public function index(Request $request)
    {
        $query = TesKesehatanAnamnesa::with(['user.jurusan', 'user.periode', 'pemeriksaan'])
            ->whereHas('pemeriksaan')
            ->latest();
        $recommendationOptions = TesKesehatanPemeriksaan::recommendationLabels();

        if ($this->isOnlineHealthOnly()) {
            PmbOnlineSelectionAccess::constrainToLatestOnlineUsers($query);
        }

        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('rekomendasi') && array_key_exists($request->rekomendasi, $recommendationOptions)) {
            $query->whereHas('pemeriksaan', fn ($q) => $q->where('rekomendasi', $request->rekomendasi));
        }

        $anamnesaList = $query->paginate(15);

        return view('admin.tes_kesehatan.index', compact('anamnesaList', 'recommendationOptions'));
    }


    /**
     * Tampilkan detail anamnesa tertentu.
     */
    public function show($id)
    {
        $anamnesa = TesKesehatanAnamnesa::with('user')->findOrFail($id);
        $this->abortIfCannotAccessAnamnesa($anamnesa);
        $showPhysicalExamForm = !$this->isOnlineHealthOnly();

        return view('admin.tes_kesehatan.show', compact('anamnesa', 'showPhysicalExamForm'));
    }

    /**
     * Update status pemeriksaan.
     */
    public function storePemeriksaan(Request $request, $id)
    {
        $anamnesa = TesKesehatanAnamnesa::findOrFail($id);
        $this->abortIfCannotAccessAnamnesa($anamnesa);
        if ($this->isOnlineHealthOnly()) {
            abort(403, 'Peserta online tidak menggunakan form pemeriksaan fisik.');
        }

        $rules = [
            'nama_pemeriksa' => 'required|string|max:100',
            'tanggal_pemeriksaan' => 'required|date',
            'tinggi_badan' => 'nullable|numeric',
            'berat_badan' => 'nullable|numeric',
            'tekanan_darah' => 'nullable|string|max:50',
            'tekanan_darah_sistolik' => 'nullable|integer|min:0|max:300',
            'tekanan_darah_diastolik' => 'nullable|integer|min:0|max:200',
            'konjungtiva' => 'nullable|string|max:100',
            'ikhterik' => 'nullable|string|max:100',
            'buta_warna' => 'nullable|string|max:100',
            'respon_pendengaran' => 'nullable|string|max:100',
            'kelengkapan_jari_atas' => 'nullable|string|max:100',
            'tremor' => 'nullable|string|max:100',
            'kidal' => 'nullable|string|max:100',
            'bekas_luka_sayatan' => 'nullable|string|max:100',
            'bunyi_jantung' => 'nullable|string|max:100',
            'bunyi_paru' => 'nullable|string|max:100',
            'rekomendasi' => 'nullable|string',
            'queue_uuid' => 'nullable|string',
            'submit_action' => 'nullable|in:save_only,save_and_call_next',
        ];

        foreach (self::OFFICIAL_PHYSICAL_EXAM_ITEMS as $item) {
            $rules["{$item}_kondisi"] = ['nullable', 'in:normal,kelainan'];
            $rules["{$item}_keterangan"] = ['nullable', 'string', 'max:255'];
        }

        foreach (self::ANAMNESA_FIELDS as $field) {
            $rules[$field] = ['required', 'boolean'];
            $rules["{$field}_keterangan"] = ['nullable', 'required_if:' . $field . ',1', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);


        // Set nama dan admin pemeriksa secara otomatis
        $validated['nama_pemeriksa'] = auth('admin')->user()?->name ?? auth()->user()?->name;
        $queueUuid = $validated['queue_uuid'] ?? null;
        $submitAction = $validated['submit_action'] ?? 'save_and_call_next';
        unset($validated['queue_uuid'], $validated['submit_action']);

        $sistolik = $validated['tekanan_darah_sistolik'] ?? null;
        $diastolik = $validated['tekanan_darah_diastolik'] ?? null;
        if ($sistolik !== null && $diastolik !== null) {
            $validated['tekanan_darah'] = "{$sistolik}/{$diastolik}";
        }
        unset($validated['tekanan_darah_sistolik'], $validated['tekanan_darah_diastolik']);

        $anamnesaPayload = [];

        foreach (self::ANAMNESA_FIELDS as $field) {
            $noteField = "{$field}_keterangan";
            $answer = $validated[$field] ?? null;
            $isYes = (string) $answer === '1';

            $anamnesaPayload[$field] = $answer === null || $answer === '' ? null : $isYes;
            $anamnesaPayload[$noteField] = $isYes ? ($validated[$noteField] ?? null) : null;

            unset($validated[$field], $validated[$noteField]);
        }

        if (!$anamnesa->user?->jurusan?->isD3Kebidanan()) {
            $validated['tes_urine_kondisi'] = null;
            $validated['tes_urine_keterangan'] = null;
        }
        // $validated['admin_id'] = auth()->user()->id;

        // Jika ada role checking
        // if (!auth()->user()->hasRole(['Super Admin', 'Petugas Medis'])) {
        //     abort(403, 'Anda tidak memiliki izin untuk melakukan pemeriksaan kesehatan.');
        // }

        TesKesehatanPemeriksaan::updateOrCreate(
            ['tes_kesehatan_anamnesa_id' => $id],
            $validated
        );

        $status = match ($validated['rekomendasi'] ?? null) {
            'layak' => 'lulus',
            'tidak layak' => 'tidak lulus',
            'perlu pemeriksaan lanjutan' => 'perlu tindak lanjut',
            default => $anamnesa->status ?: 'belum diperiksa',
        };

        $anamnesa->forceFill([
            ...$anamnesaPayload,
            'tanggal_pengisian' => $anamnesa->tanggal_pengisian ?: now(),
            'status' => $status,
            'admin_id' => auth('admin')->id(),
        ])->save();

        if ($submitAction === 'save_only') {
            if ($queueUuid) {
                $queue = PmbOfflineQueue::with('session')
                    ->where('uuid', $queueUuid)
                    ->where('user_id', $anamnesa->user_id)
                    ->first();

                if (!$queue) {
                    return redirect()
                        ->route('admin.tes-kesehatan.show', $id)
                        ->with('toastSuccess', 'Pemeriksaan fisik disimpan.');
                }

                return redirect()
                    ->route('admin.pmb-queues.officer.kesehatan', $queue->session)
                    ->with('success', 'Pemeriksaan fisik disimpan. Peserta masih aktif di meja kesehatan.');
            }

            return redirect()
                ->route('admin.tes-kesehatan.show', $id)
                ->with('toastSuccess', 'Pemeriksaan fisik disimpan.');
        }

        $queue = $this->isOnlineHealthOnly()
            ? null
            : $this->completeOfflineHealthStep($queueUuid, $anamnesa);

        if ($queue) {
            return redirect()
                ->route('admin.pmb-queues.officer.kesehatan', $queue->session)
                ->with('success', 'Pemeriksaan fisik disimpan. Peserta berikutnya otomatis dipanggil jika masih ada waiting.');
        }

        return redirect()
            ->route('admin.tes-kesehatan.show', $id)
            ->with('toastSuccess', 'Hasil pemeriksaan berhasil disimpan.');
        // This method can be implemented if needed
    }

    public function autosaveAnamnesa(Request $request, $id)
    {
        $anamnesa = TesKesehatanAnamnesa::findOrFail($id);
        $this->abortIfCannotAccessAnamnesa($anamnesa);
        if ($this->isOnlineHealthOnly()) {
            abort(403, 'Peserta online tidak menggunakan form pemeriksaan fisik.');
        }

        $field = $request->input('field');
        abort_unless(in_array($field, self::ANAMNESA_FIELDS, true), 422, 'Field anamnesa tidak valid.');

        $validated = $request->validate([
            'field' => ['required', 'string'],
            'value' => ['required', 'boolean'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $isYes = (string) $validated['value'] === '1';

        $anamnesa->forceFill([
            $field => $isYes,
            "{$field}_keterangan" => $isYes ? ($validated['keterangan'] ?? null) : null,
            'tanggal_pengisian' => $anamnesa->tanggal_pengisian ?: now(),
            'admin_id' => auth('admin')->id(),
        ])->save();

        return response()->json([
            'ok' => true,
            'message' => 'Tersimpan',
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:belum diperiksa,lulus,tidak lulus,perlu tindak lanjut',
        ]);

        $anamnesa = TesKesehatanAnamnesa::findOrFail($id);
        $this->abortIfCannotAccessAnamnesa($anamnesa);

        // Update status dan admin yang memperbarui
        $anamnesa->status = $request->status;
        $anamnesa->admin_id = auth('admin')->id(); // ✅ tambahkan baris ini

        $anamnesa->save();

        return redirect()
            ->route('admin.tes-kesehatan.show', $id)
            ->with('toastSuccess', 'Status pemeriksaan berhasil diperbarui.');
    }


    /**
     * Hapus data anamnesa.
     */
    public function destroy($id)
    {
        $anamnesa = TesKesehatanAnamnesa::findOrFail($id);
        if ($this->isOnlineHealthOnly()) {
            abort(403, 'PJ Seleksi Online tidak dapat menghapus data anamnesa.');
        }

        $anamnesa->delete();

        return redirect()
            ->route('admin.tes-kesehatan.index')
            ->with('toastSuccess', 'Data anamnesa berhasil dihapus.');
    }

    public function cetakPdf($id)
    {
        $anamnesa = TesKesehatanAnamnesa::with(['user.jurusan', 'user.periode', 'pemeriksaan'])->findOrFail($id);
        $this->abortIfCannotAccessAnamnesa($anamnesa);

        if (!$anamnesa->pemeriksaan) {
            return redirect()->back()->with('error', 'Data pemeriksaan belum tersedia untuk dicetak.');
        }

        $queue = PmbOfflineQueue::with('session.periode')
            ->where('user_id', $anamnesa->user_id)
            ->latest('id')
            ->first();

        $pdf = PDF::loadView('admin.tes_kesehatan.pdf', [
            'anamnesa' => $anamnesa,
            'pemeriksaan' => $anamnesa->pemeriksaan,
            'testSession' => $queue?->session,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('Hasil Pemeriksaan - ' . $anamnesa->user->name . '.pdf');
    }

    public function cetakSemua(Request $request)
    {
        $recommendationOptions = TesKesehatanPemeriksaan::recommendationLabels();
        $query = TesKesehatanAnamnesa::with(['user.jurusan', 'pemeriksaan'])
            ->whereHas('pemeriksaan')
            ->latest();

        if ($this->isOnlineHealthOnly()) {
            PmbOnlineSelectionAccess::constrainToLatestOnlineUsers($query);
        }

        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('rekomendasi') && array_key_exists($request->rekomendasi, $recommendationOptions)) {
            $query->whereHas('pemeriksaan', fn ($q) => $q->where('rekomendasi', $request->rekomendasi));
        }

        $anamnesaList = $query->get();

        $pdf = PDF::loadView('admin.tes_kesehatan.summary_pdf', [
            'anamnesaList' => $anamnesaList,
            'recommendationOptions' => $recommendationOptions,
            'selectedRecommendation' => $request->rekomendasi,
        ])->setPaper('A4', 'landscape');

        return $pdf->stream('Rangkuman Laporan Tes Kesehatan.pdf');
    }

    private function completeOfflineHealthStep(?string $queueUuid, TesKesehatanAnamnesa $anamnesa): ?PmbOfflineQueue
    {
        $queueQuery = PmbOfflineQueue::with(['session', 'stageSteps.stage'])
            ->where('user_id', $anamnesa->user_id)
            ->whereHas('stageSteps.stage', fn ($query) => $query->where('key', PmbQueueStage::KEY_TES_KESEHATAN));

        if ($queueUuid) {
            $queueQuery->where('uuid', $queueUuid);
        } else {
            $queueQuery->latest('id');
        }

        $queue = $queueQuery->first();

        if (!$queue) {
            return null;
        }

        $step = $queue->stageSteps
            ->first(fn (PmbQueueStageStep $stageStep): bool => $stageStep->stage?->key === PmbQueueStage::KEY_TES_KESEHATAN
                && in_array($stageStep->status, [
                    PmbQueueStageStep::STATUS_CALLED,
                    PmbQueueStageStep::STATUS_PROCESSING,
                    PmbQueueStageStep::STATUS_WAITING,
                    PmbQueueStageStep::STATUS_HELD,
                ], true));

        if (!$step) {
            return $queue;
        }

        try {
            if ($step->status === PmbQueueStageStep::STATUS_CALLED) {
                $step = $this->workflow->updateStep($step, 'recall', null, auth('admin')->id(), 'Peserta mulai pemeriksaan kesehatan.');
            }

            $this->workflow->updateStep($step, 'complete_health', null, auth('admin')->id(), 'Pemeriksaan fisik kesehatan disimpan oleh petugas medis.');
        } catch (DomainException) {
            return $queue;
        }

        return $queue->fresh(['session']);
    }

    private function isOnlineHealthOnly(): bool
    {
        $admin = auth('admin')->user();

        return (bool) ($admin?->can(AdminPermissions::PMB_ONLINE_KESEHATAN_MANAGE)
            && !$admin?->can(AdminPermissions::KESEHATAN_REVIEW)
            && !$admin?->can(AdminPermissions::PMB_QUEUE_KESEHATAN));
    }

    private function abortIfCannotAccessAnamnesa(TesKesehatanAnamnesa $anamnesa): void
    {
        if ($this->isOnlineHealthOnly()) {
            PmbOnlineSelectionAccess::abortUnlessLatestOnlineUser($anamnesa->user_id);
        }
    }
}
