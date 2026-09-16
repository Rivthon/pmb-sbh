<?php

namespace App\Http\Controllers;

use App\Models\PmbOfflineQueue;
use App\Models\TesKesehatanAnamnesa;
use App\Services\Pmb\PmbOnlineSelectionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TesKesehatanAnamnesaController extends Controller
{
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

    public function create(Request $request)
    {
        $queue = $this->eligibleOnlineQueue($request);
        if (!$queue) {
            return $this->redirectUnavailable();
        }

        $anamnesa = TesKesehatanAnamnesa::where('user_id', $request->user()->id)->first();
        if ($anamnesa?->tanggal_pengisian) {
            return redirect()->route('dashboard.tes-kesehatan.anamnesa.show');
        }

        if (!$queue->session?->isOnlineHealthOpen()) {
            return $this->redirectHealthClosed($queue);
        }

        return view('dashboard.pages.tes_kesehatan.create', compact('queue', 'anamnesa'));
    }

    public function store(Request $request, PmbOnlineSelectionService $onlineSelection): RedirectResponse
    {
        $queue = $this->eligibleOnlineQueue($request);
        if (!$queue) {
            return $this->redirectUnavailable();
        }
        if (!$queue->session?->isOnlineHealthOpen()) {
            return $this->redirectHealthClosed($queue);
        }

        $anamnesa = TesKesehatanAnamnesa::firstOrNew(['user_id' => $request->user()->id]);

        $rules = collect(self::ANAMNESA_FIELDS)
            ->mapWithKeys(fn (string $field): array => [$field => ['required', 'boolean']])
            ->all();
        foreach (self::ANAMNESA_FIELDS as $field) {
            $rules["{$field}_keterangan"] = ['nullable', "required_if:{$field},1", 'string', 'max:255'];
        }
        $rules['keterangan'] = ['nullable', 'string', 'max:2000'];
        $validated = $request->validate($rules);

        $payload = collect($validated)->only(self::ANAMNESA_FIELDS)->all();
        foreach (self::ANAMNESA_FIELDS as $field) {
            $payload["{$field}_keterangan"] = (bool) $payload[$field]
                ? ($validated["{$field}_keterangan"] ?? null)
                : null;
        }
        $payload['keterangan'] = $validated['keterangan'] ?? null;
        $payload['tanggal_pengisian'] = now();
        $payload['status'] = 'belum diperiksa';

        $anamnesa->fill($payload)->save();
        if ($anamnesa->surat_kesehatan_path) {
            $onlineSelection->markHealthLetterUploaded($queue);
        } else {
            $onlineSelection->markAnamnesisSubmitted($queue);
        }

        return redirect()
            ->route('dashboard.tes-kesehatan.anamnesa.show')
            ->with('toastSuccess', 'Anamnesa berhasil dikirim. Surat kesehatan dapat diunggah terpisah sesuai tenggat.');
    }

    public function uploadHealthLetter(Request $request, PmbOnlineSelectionService $onlineSelection): RedirectResponse
    {
        $queue = $this->eligibleOnlineQueue($request);
        if (!$queue) {
            return $this->redirectUnavailable();
        }
        if (!$queue->session?->isOnlineHealthOpen()) {
            return $this->redirectHealthClosed($queue);
        }

        $request->validate([
            'surat_kesehatan' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ], [
            'surat_kesehatan.required' => 'Pilih surat kesehatan yang akan diunggah.',
            'surat_kesehatan.mimes' => 'Surat kesehatan harus berupa PDF, JPG, JPEG, atau PNG.',
            'surat_kesehatan.max' => 'Ukuran surat kesehatan maksimal 4 MB.',
        ]);

        $anamnesa = TesKesehatanAnamnesa::firstOrNew(['user_id' => $request->user()->id]);
        if ($anamnesa->surat_kesehatan_path) {
            Storage::disk('public')->delete($anamnesa->surat_kesehatan_path);
        }

        $anamnesa->surat_kesehatan_path = $request->file('surat_kesehatan')->store('surat-kesehatan', 'public');
        $anamnesa->surat_kesehatan_uploaded_at = now();
        $anamnesa->status ??= 'belum diperiksa';
        $anamnesa->save();
        if ($anamnesa->tanggal_pengisian) {
            $onlineSelection->markHealthLetterUploaded($queue);
        } else {
            $onlineSelection->markAnamnesisSubmitted($queue);
        }

        return back()->with('toastSuccess', $queue->isHealthLetterLate($anamnesa->surat_kesehatan_uploaded_at)
            ? 'Surat kesehatan diterima dan ditandai terlambat.'
            : 'Surat kesehatan berhasil diunggah.');
    }

    public function show(Request $request)
    {
        $queue = $this->eligibleOnlineQueue($request);
        if (!$queue) {
            return $this->redirectUnavailable();
        }

        $anamnesa = TesKesehatanAnamnesa::with(['user.jurusan', 'pemeriksaan'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$anamnesa) {
            return redirect()->route('dashboard.tes-kesehatan.anamnesa.create');
        }

        return view('dashboard.pages.tes_kesehatan.show', compact('anamnesa', 'queue'));
    }

    public function cetak(Request $request)
    {
        $queue = $this->eligibleOnlineQueue($request);
        if (!$queue) {
            return $this->redirectUnavailable();
        }

        $anamnesa = TesKesehatanAnamnesa::with(['user.jurusan', 'pemeriksaan'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $pdf = Pdf::loadView('dashboard.pages.tes_kesehatan.pdf', [
            'anamnesa' => $anamnesa,
            'pemeriksaan' => $anamnesa->pemeriksaan,
        ]);

        return $pdf->stream('Form Kesehatan - ' . $request->user()->name . '.pdf');
    }

    private function eligibleOnlineQueue(Request $request): ?PmbOfflineQueue
    {
        $queue = PmbOfflineQueue::with('session.tesTulis')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->latest('id')
            ->first();

        if (!$queue || !$queue->isOnlineSelection()) {
            return null;
        }

        return $queue;
    }

    private function redirectUnavailable(): RedirectResponse
    {
        return redirect()
            ->route('dashboard.index')
            ->with('toastError', 'Form kesehatan online belum tersedia untuk penugasan terbaru Anda.');
    }

    private function redirectHealthClosed(PmbOfflineQueue $queue): RedirectResponse
    {
        $startLabel = $queue->session?->health_starts_at?->translatedFormat('d F Y H:i') ?? 'sesuai jadwal panitia';

        return redirect()
            ->route('dashboard.profile.cetakKartu')
            ->with('info', 'Tes kesehatan online dibuka ' . $startLabel . '.');
    }
}
