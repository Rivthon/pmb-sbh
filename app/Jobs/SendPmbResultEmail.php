<?php

namespace App\Jobs;

use App\Enums\PmbStatus;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPmbResultEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly int $mahasiswaId,
        public readonly PmbStatus $status
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $mahasiswa = User::with(['jurusan'])->findOrFail($this->mahasiswaId);
        $periodeAktif = Periode::where('status_periode', 'aktif')->first();

        if (!$periodeAktif || empty($mahasiswa->email)) {
            Log::channel('audit')->warning('PMB result email skipped', [
                'mahasiswa_id' => $this->mahasiswaId,
                'status' => $this->status->value,
                'has_active_periode' => (bool) $periodeAktif,
                'has_email' => filled($mahasiswa->email),
            ]);

            return;
        }

        $tanggalTes = $periodeAktif->tanggal_tes
            ? \Carbon\Carbon::parse($periodeAktif->tanggal_tes)->translatedFormat('l, d F Y')
            : '-';

        $view = $this->status === PmbStatus::Lulus
            ? 'templates/mail.lulus_pmb'
            : 'templates/mail.tidak_lulus_pmb';

        $subject = $this->status === PmbStatus::Lulus
            ? 'Pemberitahuan Kelulusan Tes PMB STIKes Bogor Husada'
            : 'Pemberitahuan Hasil Seleksi PMB STIKes Bogor Husada';

        Mail::send($view, [
            'nama' => $mahasiswa->name,
            'jurusan' => $mahasiswa->jurusan->nama_jurusan ?? '-',
            'tahunAkademik' => $periodeAktif->deskripsi,
            'tanggalTes' => $tanggalTes,
            'linkPPSMB' => $periodeAktif->linked,
        ], function ($message) use ($mahasiswa, $subject) {
            $message->to($mahasiswa->email)->subject($subject);
        });
    }
}
