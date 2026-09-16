<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\PmbStatus;
use App\Http\Controllers\Controller;
use App\Models\PmbOfflineQueue;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PmbQueueController extends Controller
{
    public function index(Request $request): View
    {
        $queues = PmbOfflineQueue::with(['session.periode', 'session.gelombang', 'room'])
            ->where('user_id', $request->user()->id)
            ->where('selection_mode', PmbOfflineQueue::MODE_OFFLINE)
            ->latest()
            ->paginate(5);

        return view('dashboard.pages.pmb_queue.index', compact('queues'));
    }

    public function show(PmbOfflineQueue $queue): View
    {
        abort_unless($queue->user_id === auth()->id(), 403);
        abort_unless($queue->isOfflineSelection(), 404);

        $queue->load(['session.periode', 'session.gelombang', 'user.jurusan', 'room', 'currentStage', 'stageSteps.stage', 'stageSteps.room']);

        return view('dashboard.pages.pmb_queue.show', compact('queue'));
    }

    public function print(PmbOfflineQueue $queue): Response|RedirectResponse
    {
        abort_unless($queue->user_id === auth()->id(), 403);
        abort_unless($queue->isOfflineSelection(), 404);

        $user = auth()->user();
        $queue->load(['session', 'room', 'user.jurusan', 'stageSteps.stage', 'stageSteps.room']);

        if (!$this->canPrintPmbCard($user, $queue)) {
            return redirect()
                ->route('dashboard.pmb-queue.show', $queue)
                ->with('toastError', 'Kartu tes PMB hanya bisa dicetak setelah biodata, berkas, dan pembayaran/status PMB diverifikasi.');
        }

        [$qrDataUri, $tempPath, $tempFileToClean] = $this->buildQrForQueue($queue);

        $pdf = Pdf::loadView('dashboard.pages.profile.pdf', compact('user', 'queue', 'qrDataUri', 'tempPath'));
        $pdfOutput = $pdf->output();

        if ($tempFileToClean && file_exists($tempFileToClean)) {
            @unlink($tempFileToClean);
        }

        return response($pdfOutput)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="kartu-tes-pmb-' . rawurlencode($queue->queue_code ?? $user->name) . '.pdf"');
    }

    private function canPrintPmbCard($user, PmbOfflineQueue $queue): bool
    {
        return (int) $user->status_biodata === 1
            && $user->hasPmbRequiredDocuments()
            && (int) $user->status_berkas === 1
            && in_array((int) $user->status_pemb, [
                PmbStatus::Verified->value,
                PmbStatus::Lulus->value,
            ], true)
            && $queue->isOfflineSelection()
            && !in_array($queue->status, [
                PmbOfflineQueue::STATUS_CANCELLED,
                PmbOfflineQueue::STATUS_NO_SHOW,
            ], true);
    }

    private function buildQrForQueue(PmbOfflineQueue $queue): array
    {
        $checkInUrl = $queue->signedCheckInUrl();
        $qrDataUri = null;
        $tempPath = null;
        $tempFileToClean = null;

        try {
            if (class_exists(\chillerlan\QRCode\QRCode::class)) {
                $tempFileToClean = storage_path('app/temp_qr_' . uniqid('', true) . '.png');
                $options = new \chillerlan\QRCode\QROptions([
                    'outputInterface' => \chillerlan\QRCode\Output\QRGdImagePNG::class,
                    'outputBase64' => false,
                    'scale' => 5,
                ]);
                $pngData = (new \chillerlan\QRCode\QRCode($options))->render($checkInUrl);

                if (!is_string($pngData) || file_put_contents($tempFileToClean, $pngData) === false) {
                    throw new \RuntimeException('File QR PNG gagal ditulis.');
                }

                if (!is_file($tempFileToClean) || filesize($tempFileToClean) === 0 || @getimagesize($tempFileToClean) === false) {
                    throw new \RuntimeException('File QR PNG tidak valid.');
                }

                $tempPath = str_replace('\\', '/', $tempFileToClean);
            } else {
                $qrDataUri = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($checkInUrl);
            }
        } catch (\Throwable $e) {
            if ($tempFileToClean && file_exists($tempFileToClean)) {
                @unlink($tempFileToClean);
            }

            $tempFileToClean = null;
            $tempPath = null;
            $qrDataUri = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($checkInUrl);
        }

        return [$qrDataUri, $tempPath, $tempFileToClean];
    }
}
