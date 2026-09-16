<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\PmbStatus;
use App\Models\Agama;
use App\Models\Jurusan;
use App\Models\Periode;
use App\Models\Provinsi;
use App\Models\Gelombang;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\PmbOfflineQueue;
use App\Models\PekerjaanIbu;
use Illuminate\Http\Request;
use App\Models\PekerjaanAyah;
use App\Models\PenghasilanOrangTua;
use App\Http\Controllers\Controller;
use App\Services\MasterData\UserService;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Barryvdh\DomPDF\Facade\Pdf;

class ProfileController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function index()
    {
        $user = auth()->user();
        $periodes = Periode::all();
        $gelombangs = Gelombang::all();
        $jurusan = Jurusan::all();
        $provinsi = Provinsi::all();
        $kabupaten = $user?->provinsi_id
            ? Kabupaten::where('id_prov', $user->provinsi_id)->orderBy('nama_kab')->get()
            : collect();
        $kecamatan = $user?->kabupaten_id
            ? Kecamatan::where('id_kab', $user->kabupaten_id)->orderBy('nama_kec')->get()
            : collect();
        $kelurahan = $user?->kecamatan_id
            ? Kelurahan::where('id_kec', $user->kecamatan_id)->orderBy('nama_kel')->get()
            : collect();
        $agama = Agama::all(); // Mendapatkan semua data agama
        $pekerjaanAyah = PekerjaanAyah::all(); // Mendapatkan semua data pekerjaan ayah
        $pekerjaanIbu = PekerjaanIbu::all(); // Mendapatkan semua data pekerjaan ibu
        $penghasilan = PenghasilanOrangTua::all(); // Mendapatkan semua data penghasilan

        // Mengirim data ke view create mahasiswa baru
        return view('dashboard.pages.profile.index', [
            'periodes' => $periodes,
            'gelombangs' => $gelombangs,
            'jurusan' => $jurusan,
            'provinsi' => $provinsi,
            'kabupaten' => $kabupaten,
            'kecamatan' => $kecamatan,
            'kelurahan' => $kelurahan,
            'agama' => $agama,
            'pekerjaanAyah' => $pekerjaanAyah,
            'pekerjaanIbu' => $pekerjaanIbu,
            'penghasilan' => $penghasilan,
        ]);
    }

    public function editBerkas()
    {
        $user = auth()->user(); // Get the authenticated user

        if (!$this->hasCompletedBiodata($user)) {
            return redirect()
                ->route('dashboard.profile.index')
                ->with('toastError', 'Lengkapi biodata terlebih dahulu sebelum mengunggah berkas PMB.');
        }

        // If needed, pass any related data to the view (e.g., existing documents)
        return view('dashboard.pages.profile.form-berkas', compact('user'));
    }
    public function update(UpdateProfileRequest $request)
    {
        // Validasi data yang masuk
        $requestDTO = $request->validated();

        // Panggil service untuk meng-update profil user
        $updateProfileResponse = $this->userService->updateUser($requestDTO, auth()->user()->uuid);
        $requestDTO = $request->only(['image']);
        // Cek apakah update berhasil dan arahkan sesuai dengan hasilnya
        return $updateProfileResponse->success
            ? redirect()->route('dashboard.profile.index')->with('toastSuccess', 'Profil Berhasil Diubah!')
            : redirect()->route('dashboard.profile.index')->with('toastError', 'Profil Gagal Diubah');
    }

    public function updatedata(Request $request)
    {
        $user = auth()->user();

        if (!$this->hasCompletedBiodata($user)) {
            return redirect()
                ->route('dashboard.profile.index')
                ->with('toastError', 'Lengkapi biodata terlebih dahulu sebelum mengunggah berkas PMB.');
        }

        $documentLabels = array_merge($user::pmbRequiredDocuments(), $user::pmbOptionalDocuments());

        $request->validate([
            'img_kk' => [blank($user->img_kk) ? 'required' : 'nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'img_ktp' => [blank($user->img_ktp) ? 'required' : 'nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'img_ijazah' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ], [
            'required' => ':Attribute wajib diunggah sebelum berkas dapat dikirim.',
            'image' => ':Attribute harus berupa file gambar.',
            'mimes' => 'Format :attribute harus JPG, JPEG, atau PNG.',
            'max' => 'Ukuran :attribute maksimal 2 MB.',
        ], $documentLabels);

        $requestDTO = $request->only(['img_kk', 'img_ktp', 'img_ijazah']);
        $updateProfileResponse = $this->userService->updateBerkas($requestDTO, auth()->user()->uuid);

        $user = auth()->user()->refresh();

        if ($updateProfileResponse->success && $this->hasRequiredDocuments($user)) {
            $user->update([
                'status_berkas' => 1
            ]);

            return redirect()->route('dashboard.profile.editBerkas')->with('toastSuccess', 'Berkas berhasil dikirim. Silakan tunggu validasi panitia.');
        }

        return redirect()
            ->route('dashboard.profile.editBerkas')
            ->with('toastError', $user->missingPmbRequiredDocumentsLabel('Berkas gagal diubah.'));
    }


    public function updatePayment()
    {
        $user = auth()->user(); // Get the authenticated user

        if (!$this->canSubmitPayment($user)) {
            return redirect()
                ->route($this->hasCompletedBiodata($user) ? 'dashboard.profile.editBerkas' : 'dashboard.profile.index')
                ->with('toastError', 'Lengkapi biodata dan berkas wajib terlebih dahulu sebelum mengunggah bukti pembayaran.');
        }

        // If needed, pass any related data to the view (e.g., existing documents)
        return view('dashboard.pages.profile.update-payment', compact('user'));
    }

    public function updatedataPembayaran(Request $request)
    {
        $user = auth()->user();

        if (!$this->canSubmitPayment($user)) {
            return redirect()
                ->route($this->hasCompletedBiodata($user) ? 'dashboard.profile.editBerkas' : 'dashboard.profile.index')
                ->with('toastError', 'Lengkapi biodata dan berkas wajib terlebih dahulu sebelum mengunggah bukti pembayaran.');
        }

        $request->validate([
            'img_bukti' => [empty($user->img_bukti) ? 'required' : 'nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ], [
            'img_bukti.required' => 'Bukti pembayaran wajib diunggah sebelum dikirim ke panitia.',
            'img_bukti.image' => 'Bukti pembayaran harus berupa file gambar.',
            'img_bukti.mimes' => 'Format bukti pembayaran harus JPG, JPEG, atau PNG.',
            'img_bukti.max' => 'Ukuran bukti pembayaran maksimal 2 MB.',
        ]);

        $requestDTO = $request->only(['img_bukti']);
        $updateProfileResponse = $this->userService->updateBukti($requestDTO, auth()->user()->uuid);

        if ($updateProfileResponse->success) {
            auth()->user()->refresh()->update([
                'status_pemb' => 1
            ]);

            return redirect()->route('dashboard.profile.updatePayment')->with('toastSuccess', 'Bukti pembayaran berhasil dikirim. Silakan tunggu verifikasi panitia.');
        }

        return redirect()->route('dashboard.profile.updatePayment')->with('toastError', 'Berkas Gagal Diubah');
    }


    public function cetakKartu()
    {
        $user = auth()->user(); // Get the authenticated user
        $latestQueue = $this->latestQueueForUser($user);

        // If needed, pass any related data to the view (e.g., existing documents)
        return view('dashboard.pages.profile.cetak-kartu', compact('user', 'latestQueue'));
    }

    public function cetak()
    {
        $user = auth()->user();

        $queue = $this->latestQueueForUser($user);

        if (!$this->canPrintPmbCard($user, $queue)) {
            return redirect()
                ->route('dashboard.profile.cetakKartu')
                ->with('toastError', 'Kartu ujian PMB hanya bisa dicetak setelah pembayaran/status PMB diverifikasi dan peserta sudah dimasukkan ke sesi tes.');
        }

        $qrDataUri = null;
        $tempPath = null;
        $tempFileToClean = null;

        if ($queue) {
            $checkInUrl = $queue->signedCheckInUrl();
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

                    // DomPDF on Windows resolves local absolute paths more reliably than file:/// URIs.
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
        }

        $pdf = PDF::loadView('dashboard.pages.profile.pdf', compact('user', 'queue', 'qrDataUri', 'tempPath'));

        $pdfOutput = $pdf->output();

        // Delete temporary file if it was created
        if ($tempFileToClean && file_exists($tempFileToClean)) {
            @unlink($tempFileToClean);
        }

        return response($pdfOutput)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="kartu-ujian-pmb-' . rawurlencode($user->name) . '.pdf"');
    }

    // Method to fetch Kabupaten based on selected Provinsi
    public function getKabupaten($provinsi_id)
    {
        $kabupatens = Kabupaten::where('id_prov', $provinsi_id)->orderBy('nama_kab')->get();
        return response()->json($kabupatens);
    }

    // Method to fetch Kecamatan based on selected Kabupaten
    public function getKecamatan($kabupaten_id)
    {
        $kecamatans = Kecamatan::where('id_kab', $kabupaten_id)->orderBy('nama_kec')->get();
        return response()->json($kecamatans);
    }

    // Method to fetch Kelurahan based on selected Kecamatan
    public function getKelurahan($kecamatan_id)
    {
        $kelurahans = Kelurahan::where('id_kec', $kecamatan_id)->orderBy('nama_kel')->get();
        return response()->json($kelurahans);
    }

    private function hasCompletedBiodata($user): bool
    {
        return (int) $user->status_biodata === 1;
    }

    private function hasRequiredDocuments($user): bool
    {
        return $user->hasPmbRequiredDocuments();
    }

    private function canSubmitPayment($user): bool
    {
        return $this->hasCompletedBiodata($user)
            && $this->hasRequiredDocuments($user)
            && (int) $user->status_berkas === 1;
    }

    private function canPrintPmbCard($user, ?PmbOfflineQueue $queue = null): bool
    {
        return $this->canSubmitPayment($user)
            && $queue !== null
            && $queue->isOfflineSelection()
            && in_array((int) $user->status_pemb, [
                PmbStatus::Verified->value,
                PmbStatus::Lulus->value,
            ], true);
    }

    private function latestQueueForUser($user): ?PmbOfflineQueue
    {
        return PmbOfflineQueue::with(['session', 'room', 'currentStage', 'stageSteps.stage', 'stageSteps.room', 'stageSteps.officer'])
            ->where('user_id', $user->id)
            ->latest()
            ->latest('id')
            ->first();
    }
}
