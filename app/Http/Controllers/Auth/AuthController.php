<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Agama;
use App\Models\Jurusan;
use App\Models\Periode;
use App\Models\Provinsi;
use App\Models\Gelombang;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Kuesioner;
use Illuminate\Support\Str;
use App\Models\PekerjaanIbu;
use Illuminate\Http\Request;
use App\Models\PekerjaanAyah;
use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\DB;
use App\Models\PenghasilanOrangTua;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\MasterData\UserService;
use App\Helpers\Responses\ObjectResponse;
use App\Helpers\Utilities\RandomGenerator;
use App\Exceptions\FailedRegisterException;
use App\Http\Requests\Auth\RegisterRequest;
use App\Exceptions\SendVerificationCodeException;

class AuthController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {
    }

    public function viewLogin()
    {
        if (auth()->check())
            return redirect()->route('dashboard.index');
        return view('auth.login');
    }

    public function storeLogin(LoginRequest $request)
    {
        $credentials = $request->validated();

        try {
            // 1. Coba untuk login terlebih dahulu
            if (!Auth::attempt($credentials)) {
                return redirect()->route('auth.login')->with('toastError', __('auth.wrong_password'))->withInput();
            }

            // Jika login berhasil, ambil data user yang sudah terautentikasi
            $user = Auth::user();

            // 2. Cek verifikasi email setelah login berhasil
            if (is_null($user->email_verified_at)) {
                // Logout user jika email belum terverifikasi
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect(route('auth.login'))->with('toastError', __('auth.not_active'));
            }

            // Regenerasi session untuk keamanan
            $request->session()->regenerate();

            // Eager load relasi yang dibutuhkan
            $user->load(['jurusan', 'periode', 'gelombang']);

            // 3. Cek status dan arahkan ke view yang sesuai
            if ($user->status_pemb == 3) { // Asumsi 3 = Lulus
                // 4. Kirim hanya objek user yang sudah lengkap ke view
                return view('auth.lulus', [
                    'user' => $user,
                    'nama' => $user->name,
                    'jurusan' => $user->jurusan->nama_jurusan ?? '-',
                    'tahunAkademik' => $user->periode->deskripsi ?? '-',
                    'tanggalTes' => $user->periode->tanggal_tes ?? null, // Kirim objek Carbon atau null
                    'linkPPSMB' => $user->periode->linked ?? '#',
                ]);
            }
            if ($user->status_pemb == 4) { // Asumsi 4 = Tidak Lulus
                return view('auth.tidak', [
                    'user' => $user,
                    'nama' => $user->name,
                    'jurusan' => $user->jurusan->nama_jurusan ?? '-',
                    'tahunAkademik' => $user->periode->deskripsi ?? '-',
                    'tanggalTes' => $user->periode->tanggal_tes ?? null, // Kirim objek Carbon atau null
                ]);
            }

            // Jika tidak lulus, arahkan ke dashboard
            return redirect()->route('dashboard.index')->with('toastSuccess', __('auth.success_login'));
        } catch (\Throwable $th) {
            Log::error('Login error: ' . $th->getMessage());
            return redirect()->route('auth.login')->with('toastError', __('auth.failed_login'))->withInput();
        }
    }

    public function viewRegister()
    {
        $periodes = Periode::all();
        $gelombangs = Gelombang::all();
        $jurusans = Jurusan::all();
        $provinsi = Provinsi::all();
        $kabupaten = Kabupaten::all();
        $kecamatan = Kecamatan::all();
        $kelurahan = Kelurahan::all();
        $kuesioners = Kuesioner::all();
        $agama = Agama::all(); // Mendapatkan semua data agama
        $pekerjaanAyah = PekerjaanAyah::all(); // Mendapatkan semua data pekerjaan ayah
        $pekerjaanIbu = PekerjaanIbu::all(); // Mendapatkan semua data pekerjaan ibu
        $penghasilan = PenghasilanOrangTua::all(); // Mendapatkan semua data penghasilan
        if (auth()->check())
            return redirect()->route('dashboard.index');
        return view('auth.register', [
            'periodes' => $periodes,
            'gelombangs' => $gelombangs,
            'jurusans' => $jurusans,
            'provinsi' => $provinsi,
            'kabupaten' => $kabupaten,
            'kecamatan' => $kecamatan,
            'kelurahan' => $kelurahan,
            'agama' => $agama,
            'pekerjaanAyah' => $pekerjaanAyah,
            'pekerjaanIbu' => $pekerjaanIbu,
            'kuesioners' => $kuesioners,
            'penghasilan' => $penghasilan,
        ]);
    }

    public function storeRegister(RegisterRequest $request)
    {
        $validated = $request->validated();
        $validated['role'] = User::USER_ROLE;
        unset($validated['terms'], $validated['password_confirmation']);

        DB::beginTransaction();

        try {
            $createUserResponse = $this->userService->createUser($validated);

            if (!$createUserResponse->success) {
                throw new FailedRegisterException($createUserResponse->message);
            }

            $user = $createUserResponse->data;

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('REGISTER FAILED', [
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Registrasi gagal'
                ], 500);
            }

            return redirect()
                ->route('auth.register')
                ->with('toastError', __('auth.not_registered'))
                ->withInput();
        }

        /**
         * ===========================
         * KIRIM OTP VIA EMAIL (SETELAH COMMIT)
         * ===========================
         */
        $sendVerificationCodeResponse = $this->sendVerificationCode(
            $user,
            true,
            $user->password_plaintext ?? null
        );

        if (!$sendVerificationCodeResponse->success) {
            Log::error('REGISTERED_USER_VERIFICATION_EMAIL_FAILED', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pendaftaran berhasil, tetapi kode verifikasi gagal dikirim.',
                    'redirect' => route('auth.verify-account', $user->uuid),
                ], 503);
            }

            return redirect()
                ->route('auth.verify-account', $user->uuid)
                ->with('toastError', 'Pendaftaran berhasil, tetapi kode verifikasi gagal dikirim. Silakan kirim ulang kode verifikasi.');
        }

        /**
         * ===========================
         * RESPONSE KE FRONTEND
         * ===========================
         */
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => route('auth.verify-account', $user->uuid)
            ]);
        }

        return redirect()
            ->route('auth.verify-account', $user->uuid)
            ->with('toastSuccess', __('auth.registered'));
    }



    public function viewVerifyAccount(string $user)
    {
        $getUserResponse = $this->userService->findUserByUUID($user);

        if (!$getUserResponse->success) {
            return redirect(route('auth.register'))
                ->with('toastError', $getUserResponse->message);
        }

        if (!is_null($getUserResponse->data->email_verified_at)) {
            return redirect(route('auth.login'))
                ->with('toastSuccess', 'Akun anda sudah terverifikasi, Silahkan Login');
        }

        $userData = $getUserResponse->data;
        return view('auth.verify-account', compact('user', 'userData'));
    }

    // public function updatePhoneNumber(Request $request, string $user)
    // {
    //     $request->validate([
    //         'phone' => 'required|numeric|unique:users,phone'
    //     ], [], [
    //         'phone' => 'Nomor Telepon'
    //     ]);

    //     $getUserResponse = $this->userService->findUserByUUID($user);
    //     if (!$getUserResponse->success) {
    //         return response()->json(['success' => false, 'message' => $getUserResponse->message]);
    //     }

    //     try {
    //         $getUserResponse->data->update([
    //             'phone' => $request->phone
    //         ]);

    //         // Kirim ulang kode verifikasi ke nomor baru
    //         $this->sendVerificationCode($getUserResponse->data, true);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Nomor telepon berhasil diupdate dan kode verifikasi telah dikirim ulang'
    //         ]);
    //     } catch (\Throwable $th) {
    //         \Log::error('Error updating phone number: ' . $th->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal update nomor telepon'
    //         ], 500);
    //     }
    // }

    public function storeVerifyAccount(Request $request, string $user)
    {
        $requestDTO = $request->validate([
            'verify_code' => 'required|numeric'
        ], [], [
            'verify_code' => 'Kode Verifikasi'
        ]);

        $getUserResponse = $this->userService->findUserByUUID($user);

        if (!$getUserResponse->success) {
            return redirect(route('auth.register'))
                ->with('toastError', $getUserResponse->message);
        }

        $userData = $getUserResponse->data;

        if ($userData->email_verified_at) {
            return redirect(route('auth.login'))
                ->with('toastSuccess', 'Akun anda sudah terverifikasi, Silahkan Login');
        }

        if ((string) $userData->verification_code !== (string) $requestDTO['verify_code']) {
            return redirect(route('auth.verify-account', $user))
                ->with('toastError', __('auth.wrong_code'));
        }

        try {
            $userData->email_verified_at = now();
            $userData->verification_code = null;
            $userData->save();

            return redirect()
                ->route('auth.login')
                ->with('toastSuccess', 'Akun terverifikasi. Silakan login menggunakan password yang Anda buat saat pendaftaran.');

        } catch (\Throwable $th) {
            \Log::error('Error during account verification: ' . $th->getMessage());

            return redirect()
                ->route('auth.verify-account', $user)
                ->with('toastError', __('auth.error_activate'));
        }
    }


    public function resendVerifyCode(string $user)
    {
        $getUserResponse = $this->userService->findUserByUUID($user);

        if (!$getUserResponse->success) {
            return redirect(route('auth.register'))
                ->with('toastError', $getUserResponse->message);
        }

        // 🔐 Sudah diverifikasi?
        if ($getUserResponse->data->email_verified_at) {
            return redirect(route('auth.login'))
                ->with('toastSuccess', 'Akun anda sudah terverifikasi, Silahkan Login');
        }

        $sendVerificationCodeResponse = $this->sendVerificationCode($getUserResponse->data, true);

        return redirect()
            ->route('auth.verify-account', $user)
            ->with(
                $sendVerificationCodeResponse->success ? 'toastSuccess' : 'toastError',
                $sendVerificationCodeResponse->message
            );
    }



    private function sendVerificationCode(User $user, bool $withRefreshCode = false, ?string $passwordPlaintext = null)
    {
        try {
            $verificationCode = $withRefreshCode
                ? RandomGenerator::generateRandomNumber(6, true)
                : $user->verification_code;

            if ($withRefreshCode) {
                $user->update(['verification_code' => $verificationCode]);
            }

            // Load relasi jurusan agar nama prodi bisa dikirim di email
            $user->loadMissing('jurusan');
            $prodiName = $user->jurusan->nama_jurusan ?? '-';

            // Kirim via Email saja
            try {
                \Mail::to($user->email)->send(
                    new \App\Mail\VerificationCodeMail(
                        $verificationCode,
                        $user->name,
                        $user->uuid,
                        $prodiName,
                        $passwordPlaintext
                    )
                );
            } catch (\Throwable $mailException) {
                \Log::error('Email verify failed: ' . $mailException->getMessage());
                return ObjectResponse::error('Kode verifikasi gagal dikirim.', 500);
            }

            return ObjectResponse::success(__('auth.verification_code'), 201);
        } catch (\Throwable $th) {
            \Log::error('Error sending verification code: ' . $th->getMessage());
            return ObjectResponse::error(__('auth.error_verification_code'), 500);
        }
    }

    private function sendVerificationCodeWhatsApp(User $user, string $verificationCode): bool
    {
        try {
            // =========================
            // 1. Normalisasi nomor
            // =========================
            if (empty($user->phone)) {
                throw new \Exception('Nomor WA kosong');
            }

            $phone = preg_replace('/\D+/', '', $user->phone);

            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            }

            if (!str_starts_with($phone, '62')) {
                throw new \Exception('Format nomor WA tidak valid: ' . $phone);
            }

            // =========================
            // 2. Ambil konfigurasi
            // =========================
            $url = config('services.wablas.url') ?: env('WABLAS_URL');
            $token = config('services.wablas.token') ?: env('WABLAS_TOKEN');
            $secret = config('services.wablas.secret') ?: env('WABLAS_SECRET');

            if (!$url || !$token || !$secret) {
                throw new \Exception('Config Wablas tidak lengkap');
            }

            // =========================
            // 3. Buat pesan
            // =========================
            $prodi = optional($user->jurusan)->nama_jurusan ?? '-';
            $link = route('auth.verify-account', $user->uuid);

            $message = <<<MSG
🎓 *PENDAFTARAN PMB*
*STIKes Bogor Husada*

Halo *{$user->name}* 👋
Terima kasih telah mendaftar di *STIKes Bogor Husada*.

━━━━━━━━━━━━━━━━
📌 *DATA PENDAFTAR*
━━━━━━━━━━━━━━━━
👤 Nama   : {$user->name}
📧 Email  : {$user->email}
📘 Prodi  : {$prodi}

━━━━━━━━━━━━━━━━
🔐 *AKSES AKUN*
━━━━━━━━━━━━━━━━
🔢 Kode Verifikasi : *{$verificationCode}*

━━━━━━━━━━━━━━━━
🔗 *LINK VERIFIKASI*
━━━━━━━━━━━━━━━━
{$link}

Silakan masukkan *Kode Verifikasi* di halaman tersebut untuk mengaktifkan akun Anda.

Terima kasih telah memilih
*STIKes Bogor Husada* 🙏
MSG;


            // =========================
            // 4. Payload
            // =========================
            $payload = [
                "data" => [
                    [
                        "phone" => $phone,
                        "message" => $message
                    ]
                ]
            ];

            Log::info('WABLAS SEND', [
                'user_id' => $user->id,
                'phone' => $phone
            ]);

            // =========================
            // 5. Kirim ke Wablas
            // =========================
            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' => $token . '.' . $secret,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $payload);

            if (!$response->ok()) {
                throw new \Exception('HTTP Error ' . $response->status());
            }

            $result = $response->json();

            // =========================
            // 6. Validasi hasil Wablas
            // =========================
            if (!isset($result['status']) || $result['status'] !== true) {
                throw new \Exception('Wablas API error: ' . json_encode($result));
            }

            $wablasStatus = strtolower($result['data']['messages'][0]['status'] ?? '');

            // STATUS YANG DIANGGAP SUKSES
            if (!in_array($wablasStatus, ['pending', 'queued', 'sent', 'success'])) {
                throw new \Exception('Wablas rejected message: ' . json_encode($result));
            }

            Log::info('WABLAS OK', [
                'user_id' => $user->id,
                'phone' => $phone,
                'status' => $wablasStatus
            ]);

            return true;
        } catch (\Throwable $e) {

            // ❗ WA ERROR TIDAK BOLEH MEMBATALKAN REGISTER
            Log::error('WABLAS FAILED', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }



    public function logout()
    {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('auth.login')->with('toastSuccess', __('auth.logout'));
    }
}
