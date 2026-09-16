<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            $request = app()->bound('request') ? app('request') : null;

            Log::channel('audit')->error('Application exception', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'url' => $request?->fullUrl(),
                'ip' => $request?->ip(),
            ]);
        });

        $this->renderable(function (HttpExceptionInterface $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => match ($e->getStatusCode()) {
                        403 => 'Anda tidak memiliki akses.',
                        404 => 'Data atau halaman tidak ditemukan.',
                        default => 'Terjadi kesalahan. Silakan coba lagi.',
                    },
                ], $e->getStatusCode());
            }
        });
    }
}
