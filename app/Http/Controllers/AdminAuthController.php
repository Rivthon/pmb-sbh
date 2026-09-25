<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\PmbTestSession;
use Illuminate\Http\Request;
use App\Mail\VerificationCodeMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Auth\AdminRequest;
use App\Helpers\Responses\ObjectResponse;
use App\Helpers\Utilities\RandomGenerator;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\MasterData\UserService;
use Illuminate\Support\Facades\DB;
use App\Services\Audit\ActivityLogger;
use App\Support\AdminPermissions;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AdminAuthController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger
    ) {}

    public function showLoginForm()
    {
        return view('admin.auth_login'); // Buat view login untuk admin
    }

    public function storeLogin(AdminRequest $request)
{
    // Validasi input dari request
    $requestDTO = $request->validated();
    $throttleKey = Str::lower($requestDTO['email']) . '|' . $request->ip();

    if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
        $seconds = RateLimiter::availableIn($throttleKey);

        return redirect()->route('admin.login')
            ->with('toastError', "Terlalu banyak percobaan login. Coba kembali dalam {$seconds} detik.")
            ->withInput($request->only('email'));
    }

    try {
        // Cari admin berdasarkan email
        $admin = Admin::where('email', $requestDTO['email'])->first();

        // Jika admin tidak ditemukan, arahkan kembali ke halaman login
        if (is_null($admin)) {
            RateLimiter::hit($throttleKey, 900);
            return redirect()->route('admin.login')->with('toastError', 'Email atau password tidak sesuai.');
        }

        // Cek status aktif admin
        if (isset($admin->is_active) && !$admin->is_active) {
            return redirect()->route('admin.login')->with('toastError', 'Akun Anda dinonaktifkan. Silakan hubungi Super Admin.');
        }

        // Proses login menggunakan guard 'admin'
        if (Auth::guard('admin')->attempt([
            'email' => $requestDTO['email'],
            'password' => $requestDTO['password'],
        ])) {
            // Regenerasi session untuk keamanan
            request()->session()->regenerate();
            RateLimiter::clear($throttleKey);

            $this->activityLogger->log(
                'auth',
                'admin.login',
                'Admin berhasil login',
                $admin,
                [],
                ['email' => $admin->email]
            );

            if ($admin->can(AdminPermissions::PMB_QUEUE_SCAN)
                && !$admin->can(AdminPermissions::PMB_EDIT)) {
                return redirect()->route('admin.pmb-queues.scan.index')->with('toastSuccess', __('auth.success_login'));
            }

            if ($admin->can(AdminPermissions::PMB_QUEUE_ONLINE_MANAGE)
                && !$admin->can(AdminPermissions::PMB_EDIT)) {
                return redirect()
                    ->route('admin.pmb-queues.index')
                    ->with('toastSuccess', __('auth.success_login'));
            }

            if ($admin->can(AdminPermissions::PMB_QUEUE_TES_TULIS)
                && !$admin->can(AdminPermissions::PMB_EDIT)
                && !$admin->can(AdminPermissions::PMB_QUEUE_KESEHATAN)
                && !$admin->can(AdminPermissions::PMB_QUEUE_WAWANCARA)) {
                return redirect()
                    ->route('admin.pmb-queues.written-test.sessions')
                    ->with('toastSuccess', __('auth.success_login'));
            }

            if ($admin->can(AdminPermissions::PMB_QUEUE_WAWANCARA)
                && !$admin->can(AdminPermissions::PMB_EDIT)
                && !$admin->can(AdminPermissions::PMB_QUEUE_TES_TULIS)
                && !$admin->can(AdminPermissions::PMB_QUEUE_KESEHATAN)) {
                return redirect()
                    ->route('admin.pmb-queues.interview.sessions')
                    ->with('toastSuccess', __('auth.success_login'));
            }

            if ($admin->can(AdminPermissions::PMB_QUEUE_KESEHATAN)
                && !$admin->can(AdminPermissions::PMB_EDIT)
                && !$admin->can(AdminPermissions::PMB_QUEUE_TES_TULIS)
                && !$admin->can(AdminPermissions::PMB_QUEUE_WAWANCARA)) {
                $session = PmbTestSession::query()
                    ->whereIn('status', [PmbTestSession::STATUS_OPEN, PmbTestSession::STATUS_SCHEDULED])
                    ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [PmbTestSession::STATUS_OPEN])
                    ->orderByDesc('starts_at')
                    ->first();

                return $session
                    ? redirect()->route('admin.pmb-queues.officer.kesehatan', $session)->with('toastSuccess', __('auth.success_login'))
                    : redirect()->route('admin.tes-kesehatan.index')->with('toastSuccess', __('auth.success_login'));
            }

            // Arahkan ke dashboard admin setelah login sukses
            return redirect()->route('admin.dashboard')->with('toastSuccess', __('auth.success_login'));
        }

        // Jika password salah, arahkan kembali ke halaman login dengan pesan error
        RateLimiter::hit($throttleKey, 900);

        return redirect()->route('admin.login')->with('toastError', 'Email atau password tidak sesuai.')->withInput();
    } catch (\Throwable $th) {
        // Tangani error dan arahkan kembali dengan pesan error
        return redirect()->route('admin.login')->with('toastError', 'Gagal masuk. Terjadi kesalahan pada server.')->withInput();
    }
}

    public function logout()
    {
        $this->activityLogger->log('auth', 'admin.logout', 'Admin logout');
        Auth::guard('admin')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('admin.login')->with('toastSuccess', 'Anda berhasil logout');
    }
}
