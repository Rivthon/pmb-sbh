<!DOCTYPE html>
<html lang="id" class="light-style layout-wide customizer-hide" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('dashboard_assets/assets/') }}" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Verifikasi Akun - STIKes Bogor Husada</title>

    <meta name="description" content="Verifikasi Akun Penerimaan Mahasiswa Baru STIKes Bogor Husada.">
    <link rel="icon" type="image/x-icon" href="{{ asset('dashboard_assets/assets/img/favicon/favicon.ico') }}" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/css/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/libs/iziToast/css/iziToast.min.css') }}" />

    <style>
        :root {
            --primary-color: #674fff;
            --primary-hover: #729cfe;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-image: url("{{ asset('dashboard_assets/assets/img/front-pages/backgrounds/hero-bg.png') }}");
            /* Pastikan path gambar benar */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Overlay Background */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.7);
            /* Overlay putih transparan agar card menonjol */
            backdrop-filter: blur(5px);
            z-index: -1;
        }

        .auth-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            background: #fff;
            max-width: 450px;
            width: 100%;
            overflow: hidden;
            animation: slideUp 0.6s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .user-info-box {
            background-color: #f8f9fa;
            border: 1px dashed #dee2e6;
            border-radius: 12px;
            padding: 15px;
        }

        /* Styling Input OTP */
        .form-control-otp {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.5rem;
            text-align: center;
            height: 60px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .form-control-otp:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(13, 138, 78, 0.1);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 12px;
            font-weight: 600;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(13, 138, 78, 0.2);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <div class="container p-3">
        <div class="auth-card mx-auto p-4 p-md-5">

            <div class="text-center mb-4">
                <a href="#">
                    <img src="{{ asset('logo/logo_sbh.png') }}" alt="Logo STIKes" style="height: 60px; width: auto;"
                        class="mb-2">
                </a>
            </div>

            <div class="text-center mb-4">
                <h4 class="fw-bold mb-2">Verifikasi Akun 🔐</h4>
                <p class="text-muted small">
                    Demi keamanan, kami telah mengirimkan kode OTP ke Email Anda.
                </p>
            </div>

            <div class="user-info-box mb-4">
                <div class="d-flex align-items-center mb-2">
                    <div class="badge bg-label-success rounded p-2 me-3">
                        <i class="bx bx-user fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Nama Lengkap</small>
                        <span class="fw-semibold text-dark">{{ $userData->name }}</span>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="badge bg-label-success rounded p-2 me-3">
                        <i class="bx bxl-whatsapp fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Email</small>
                        <span class="fw-semibold text-dark">{{ $userData->Email }}</span>
                    </div>
                </div>
            </div>

            <form id="formAuthentication" method="POST" action="{{ route('auth.store.verify-account', $user) }}">
                @csrf

                <div class="mb-4">
                    <label for="verify_code" class="form-label d-block text-center fw-medium mb-2">Masukkan Kode 6
                        Digit</label>
                    <input type="text" class="form-control form-control-otp @error('verify_code') is-invalid @enderror"
                        id="verify_code" name="verify_code" placeholder="123456" maxlength="10"
                        value="{{ old('verify_code') }}" required autofocus autocomplete="off">
                    @error('verify_code')
                        <div class="invalid-feedback text-center">{{ $message }}</div>
                    @enderror
                </div>

                <button class="btn btn-primary d-grid w-100 mb-3" type="submit">
                    Verifikasi Sekarang
                </button>
            </form>

            <div class="text-center"
                x-data="resendTimer('{{ route('auth.resend-code', $user) }}', '{{ csrf_token() }}')">
                <p class="small text-muted mb-0">
                    Belum menerima kode?

                    <template x-if="canResend && !isLoading">
                        <a href="#" @click.prevent="resend" class="fw-bold text-primary text-decoration-none">Kirim
                            Ulang</a>
                    </template>

                    <template x-if="isLoading">
                        <span class="text-primary fw-medium">
                            <span class="spinner-border spinner-border-sm me-1"></span> Mengirim...
                        </span>
                    </template>

                    <template x-if="!canResend">
                        <span class="text-muted">
                            Tunggu <strong class="text-dark" x-text="countdown"></strong> detik
                        </span>
                    </template>
                </p>
            </div>

        </div>
    </div>

    <script src="{{ asset('dashboard_assets/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/iziToast/js/iziToast.min.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    @include('dashboard.components.notification')

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('resendTimer', (url, csrfToken) => ({
                countdown: 60,
                canResend: false,
                isLoading: false,
                timer: null,

                init() {
                    this.startCountdown();
                },

                startCountdown() {
                    this.timer = setInterval(() => {
                        if (this.countdown > 0) {
                            this.countdown--;
                        } else {
                            this.canResend = true;
                            clearInterval(this.timer);
                        }
                    }, 1000);
                },

                async resend() {
                    if (this.isLoading || !this.canResend) return;

                    this.isLoading = true;
                    this.canResend = false;

                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({ resend: true })
                        });

                        const data = await response.json().catch(() => null);

                        if (response.ok) {
                            iziToast.success({
                                title: 'Berhasil',
                                message: data?.message || 'Kode verifikasi baru telah dikirim.',
                                position: 'topRight'
                            });
                        } else {
                            iziToast.error({
                                title: 'Gagal',
                                message: 'Gagal mengirim ulang kode.',
                                position: 'topRight'
                            });
                        }

                    } catch (error) {
                        iziToast.error({
                            title: 'Error',
                            message: 'Terjadi kesalahan jaringan.',
                            position: 'topRight'
                        });
                    } finally {
                        this.isLoading = false;
                        this.countdown = 60; // Reset timer ke 60 detik
                        this.startCountdown();
                    }
                }
            }));
        });
    </script>
</body>

</html>