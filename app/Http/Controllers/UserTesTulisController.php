<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\TesTulis;
use App\Models\KategoriSoal;
use Illuminate\Http\Request;
use App\Models\HasilTesTulis;
use App\Models\TesTulisHasilKategori;
use App\Models\TesTulisJawaban;
use App\Models\PmbOfflineQueue;
use App\Models\PmbTestSession;
use Illuminate\Support\Facades\Auth;
use App\Services\MasterData\UserService;
use App\Services\Pmb\PmbOnlineSelectionService;

class UserTesTulisController extends Controller
{

    /**
     * Menampilkan daftar tes tulis aktif.
     */
    public function index()
    {
        $queue = $this->latestOnlineTesTulisQueue();

        $tesTulis = $queue?->session?->tes_tulis_id
            ? TesTulis::whereKey($queue->session->tes_tulis_id)
                ->where('status_aktif', true)
                ->with('hasilTesUser')
                ->get()
            : collect();

        return view('dashboard.pages.tes-tulis.index', compact('tesTulis', 'queue'));
    }

    public function show($id)
    {
        $userId = Auth::id();
        $queue = $this->latestOnlineTesTulisQueue();

        if (!$queue || (int) $queue->session?->tes_tulis_id !== (int) $id) {
            return redirect()->route('dashboard.index')
                ->with('toastError', 'Tes tulis online belum tersedia untuk akun Anda.');
        }

        if (!$queue->session?->isOnlineSelectionOpen()) {
            return redirect()->route('dashboard.user.tes-tulis.index')
                ->with('info', 'Tes tulis online dibuka ' . ($queue->session?->online_test_starts_at?->translatedFormat('d F Y H:i') ?? 'sesuai jadwal panitia') . '.');
        }

        $tesTulis = TesTulis::with([
            'soal' => fn ($query) => $query->orderBy('id'),
            'soal.pilihanJawaban',
            'soal.kategori',
        ])->findOrFail($id);

        if (!$tesTulis->status_aktif) {
            return redirect()->route('dashboard.user.tes-tulis.index')
                ->with('error', 'Tes ini tidak aktif.');
        }

        $hasil = HasilTesTulis::firstOrNew([
            'user_id' => $userId,
            'tes_tulis_id' => $tesTulis->id,
        ]);

        // Jika belum mulai
        if (!$hasil->exists || !$hasil->waktu_mulai) {
            $hasil->fill([
                'waktu_mulai' => now(),
                'status' => 'berlangsung',
            ])->save();
        }

        // Jika sudah selesai, arahkan ke hasil
        if ($hasil->status === 'selesai') {
            return redirect()->route('dashboard.user.tes-tulis.index')
                ->with('info', 'Anda sudah menyelesaikan tes ini.');
        }

        // Hitung waktu tersisa
        $mulai = Carbon::parse($hasil->waktu_mulai);
        $batas = $mulai->copy()->addMinutes($tesTulis->durasi_menit);
        $now = now();

        if ($now->gte($batas)) {
            // Waktu habis, tandai selesai
            $hasil->update([
                'status' => 'selesai',
                'waktu_selesai' => $now,
            ]);

            return redirect()->route('dashboard.user.tes-tulis.index')
                ->with('error', 'Waktu tes telah habis.');
        }

        $sisaWaktu = max(0, (int)$now->diffInSeconds($batas, false));

        $kategoriIds = $tesTulis->soal
            ->pluck('kategori_id')
            ->filter()
            ->unique()
            ->values();

        $kategoriList = KategoriSoal::query()
            ->whereIn('id', $kategoriIds)
            ->orderBy('id')
            ->get(['id', 'nama_kategori', 'cerita_bacaan']);

        // 🔹 Kelompokkan soal berdasarkan kategori (optional untuk sidebar)
        $soalPerKategori = $tesTulis->soal->groupBy('kategori_id');

        $hasQuestionPassages = $tesTulis->soal->contains(fn ($item) => filled($item->cerita_bacaan));

        // Acak soal jika diaktifkan. Soal dengan bacaan per-grup dijaga urut agar bacaan tidak terpisah dari soal berikutnya.
        $soal = $tesTulis->acak_soal && !$hasQuestionPassages
            ? $tesTulis->soal->shuffle()
            : $tesTulis->soal;

        return view('dashboard.pages.tes-tulis.show', compact(
            'tesTulis',
            'hasil',
            'soal',
            'sisaWaktu',
            'kategoriList',
            'soalPerKategori'
        ));
    }

    public function submit(Request $request, $id, PmbOnlineSelectionService $onlineSelection)
    {
        $queue = $this->latestOnlineTesTulisQueue();

        if (!$queue || (int) $queue->session?->tes_tulis_id !== (int) $id) {
            $message = 'Tes tulis online belum tersedia untuk akun Anda.';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 403)
                : redirect()->route('dashboard.index')->with('toastError', $message);
        }

        if (!$queue->session?->isOnlineSelectionOpen()) {
            $message = 'Tes tulis online belum dibuka.';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 403)
                : redirect()->route('dashboard.user.tes-tulis.index')->with('info', $message);
        }

        $tes = TesTulis::with(['soal.kategori', 'soal.pilihanJawaban'])->findOrFail($id);
        $user = auth()->user();

        // Ambil input jawaban baik dari JSON maupun form
        $jawaban = $request->isJson()
            ? $request->json('jawaban', [])
            : $request->input('jawaban', []);

        $isAutoSubmit = $request->boolean('is_auto');
        $questionIds = $tes->soal->pluck('id')->map(fn ($id) => (int) $id)->values();
        $answeredQuestionIds = collect($jawaban)
            ->filter(fn ($value) => filled($value))
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if (!$isAutoSubmit && empty($jawaban)) {
            $message = 'Tidak ada jawaban yang dikirim.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        if (!$isAutoSubmit && $questionIds->diff($answeredQuestionIds)->isNotEmpty()) {
            $message = 'Lengkapi semua jawaban sebelum mengirim tes tulis.';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        // Ambil atau buat hasil utama
        $hasil = HasilTesTulis::firstOrCreate([
            'user_id' => $user->id,
            'tes_tulis_id' => $tes->id,
        ]);

        $rekapKategori = [];

        foreach ($tes->soal as $soal) {
            $soalId = (int) $soal->id;
            $kategoriId = $soal->kategori_id;

            // Ambil pilihan user (baik integer atau string key)
            $pilihanId = isset($jawaban[$soalId])
                ? (int) $jawaban[$soalId]
                : (isset($jawaban[(string)$soalId]) ? (int) $jawaban[(string)$soalId] : null);

            $pilihan = $soal->pilihanJawaban->firstWhere('id', $pilihanId);

            // Deteksi jawaban benar
            $isBenar = 0;
            if ($pilihan) {
                $isBenar = (int) (
                    ($pilihan->benar ?? $pilihan->is_benar ?? $pilihan->correct ?? false)
                );
            }

            // Rekap per kategori
            if (!isset($rekapKategori[$kategoriId])) {
                $rekapKategori[$kategoriId] = [
                    'jumlah_soal' => 0,
                    'jawaban_benar' => 0,
                    'jawaban_salah' => 0,
                ];
            }

            $rekapKategori[$kategoriId]['jumlah_soal']++;
            $rekapKategori[$kategoriId]['jawaban_benar'] += $isBenar;
            $rekapKategori[$kategoriId]['jawaban_salah'] += !$isBenar;

            // Simpan detail per soal
            TesTulisJawaban::updateOrCreate(
                [
                    'hasil_tes_id' => $hasil->id,
                    'soal_id' => $soalId,
                ],
                [
                    'pilihan_jawaban_id' => $pilihanId,
                    'benar' => $isBenar,
                ]
            );
        }

        // Hitung skor total
        $totalBenar = collect($rekapKategori)->sum('jawaban_benar');
        $totalSoal  = collect($rekapKategori)->sum('jumlah_soal');
        $skorAkhir  = $totalSoal > 0 ? round(($totalBenar / $totalSoal) * 100, 2) : 0;

        // Update hasil utama
        $hasil->update([
            'skor' => $skorAkhir,
            'jawaban_benar' => $totalBenar,
            'jawaban_salah' => $totalSoal - $totalBenar,
            'status' => 'selesai',
            'waktu_selesai' => now(),
        ]);

        // Simpan hasil kategori
        foreach ($rekapKategori as $kategoriId => $data) {
            $skorKategori = $data['jumlah_soal'] > 0
                ? round(($data['jawaban_benar'] / $data['jumlah_soal']) * 100, 2)
                : 0;

            TesTulisHasilKategori::updateOrCreate(
                [
                    'hasil_tes_id' => $hasil->id,
                    'kategori_id' => $kategoriId,
                ],
                [
                    'jumlah_soal' => $data['jumlah_soal'],
                    'jawaban_benar' => $data['jawaban_benar'],
                    'jawaban_salah' => $data['jawaban_salah'],
                    'skor' => $skorKategori,
                ]
            );
        }

        $onlineSelection->markWrittenCompleted($queue);

        $redirectUrl = route('dashboard.tes-tulis.result', $tes->id);

        // === Respons akhir ===
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $isAutoSubmit
                    ? 'Jawaban tersimpan otomatis saat waktu habis.'
                    : 'Jawaban berhasil disimpan dan hasil sudah direkap.',
                'redirect' => $redirectUrl,
                'skor' => $skorAkhir,
            ]);
        }

        return redirect($redirectUrl)->with('success', 'Jawaban berhasil disimpan dan hasil sudah direkap.');
    }



    public function result($id)
    {
        $userId = Auth::id();
        $queue = $this->latestOnlineTesTulisQueue();

        if (!$queue || (int) $queue->session?->tes_tulis_id !== (int) $id) {
            return redirect()->route('dashboard.index')
                ->with('toastError', 'Tes tulis online belum tersedia untuk akun Anda.');
        }

        // Ambil hasil utama (total skor, benar, salah, dll)
        $hasil = HasilTesTulis::where('user_id', $userId)
            ->where('tes_tulis_id', $id)
            ->firstOrFail();

        // Ambil rekap hasil per kategori
        $hasilKategori = TesTulisHasilKategori::where('hasil_tes_id', $hasil->id)
            ->with('kategori') // jika relasi kategori ada
            ->get();

        return view('dashboard.pages.tes-tulis.result', compact('hasil', 'hasilKategori'));
    }

    private function latestOnlineTesTulisQueue(): ?PmbOfflineQueue
    {
        $queue = PmbOfflineQueue::with('session.tesTulis')
            ->where('user_id', auth()->id())
            ->latest()
            ->latest('id')
            ->first();

        return $queue
            && $queue->requiresTesTulis()
            && $queue->isOnlineSelection()
            && $queue->session?->tes_tulis_id
            && (bool) $queue->session?->tesTulis?->status_aktif
                ? $queue
                : null;
    }
}
