<?php

namespace App\Http\Controllers;

use App\Models\PmbOfflineQueue;
use App\Models\WawancaraPmb;
use App\Services\Pmb\PmbOnlineSelectionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WawancaraMahasiswaController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $queue = $this->eligibleQueue($request);
        if (!$queue) return $this->redirectUnavailable();

        $wawancara = WawancaraPmb::firstOrCreate(
            ['calon_mahasiswa_id' => $request->user()->id],
            ['status' => 'draft']
        );
        $interviewOpen = (bool) $queue->session?->isOnlineInterviewFormOpen();

        return view('dashboard.pages.wawancara.index', compact('wawancara', 'queue', 'interviewOpen'));
    }

    public function submit(Request $request, PmbOnlineSelectionService $onlineSelection): RedirectResponse
    {
        $queue = $this->eligibleQueue($request);
        if (!$queue) return $this->redirectUnavailable();
        if (!$queue->session?->isOnlineInterviewFormOpen()) {
            return $this->redirectInterviewClosed($queue);
        }

        $wawancara = WawancaraPmb::firstOrCreate(['calon_mahasiswa_id' => $request->user()->id], ['status' => 'draft']);
        abort_unless($wawancara->status === 'draft', 403, 'Form wawancara sudah dikirim.');

        $validated = $request->validate([
            'jawaban_1' => ['required', 'string', 'min:10'],
            'jawaban_2' => ['required', 'string', 'min:10'],
            'kelebihan' => ['required', 'string', 'min:3'],
            'kekurangan' => ['required', 'string', 'min:3'],
            'jawaban_4' => ['required', 'string', 'min:10'],
            'jawaban_5' => ['required', 'string', 'min:10'],
            'jawaban_6' => ['required', 'string', 'min:10'],
            'sumber_informasi' => ['nullable', 'string', 'max:255'],
        ]);

        $wawancara->fill($validated + ['status' => 'submitted'])->save();
        $onlineSelection->markInterviewFormSubmitted($queue);

        return redirect()->route('dashboard.wawancara.show')->with('success', 'Form wawancara berhasil dikirim.');
    }

    public function show(Request $request): View|RedirectResponse
    {
        $queue = $this->eligibleQueue($request);
        if (!$queue) return $this->redirectUnavailable();

        $wawancara = WawancaraPmb::with(['user.jurusan', 'pewawancara'])
            ->where('calon_mahasiswa_id', $request->user()->id)->first();
        if (!$wawancara || $wawancara->status === 'draft') return redirect()->route('dashboard.wawancara.index');

        return view('dashboard.pages.wawancara.show', compact('wawancara', 'queue'));
    }

    public function cetak(Request $request)
    {
        $queue = $this->eligibleQueue($request);
        if (!$queue) return $this->redirectUnavailable();
        $wawancara = WawancaraPmb::with(['user.jurusan', 'pewawancara'])
            ->where('calon_mahasiswa_id', $request->user()->id)
            ->where('status', '<>', 'draft')->firstOrFail();

        return Pdf::loadView('dashboard.pages.wawancara.pdf', compact('wawancara'))
            ->stream('Form Wawancara - ' . $request->user()->name . '.pdf');
    }

    private function eligibleQueue(Request $request): ?PmbOfflineQueue
    {
        $queue = PmbOfflineQueue::with(['session', 'stageSteps.stage', 'stageSteps.room', 'stageSteps.officer'])
            ->where('user_id', $request->user()->id)->latest()->latest('id')->first();

        return $queue?->isOnlineSelection() ? $queue : null;
    }

    private function redirectUnavailable(): RedirectResponse
    {
        return redirect()
            ->route('dashboard.index')
            ->with('toastError', 'Form wawancara online belum tersedia untuk penugasan terbaru Anda.');
    }

    private function redirectInterviewClosed(PmbOfflineQueue $queue): RedirectResponse
    {
        $startLabel = $queue->session?->interview_starts_at?->translatedFormat('d F Y H:i') ?? 'sesuai jadwal panitia';

        return redirect()
            ->route('dashboard.profile.cetakKartu')
            ->with('info', 'Form wawancara online dibuka ' . $startLabel . '.');
    }
}
