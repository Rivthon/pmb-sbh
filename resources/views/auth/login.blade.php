<!DOCTYPE html>
<html lang="id" class="light-style customizer-hide" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('dashboard_assets/assets/') }}" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Login PMB - STIKes Bogor Husada</title>
    <meta name="description" content="Login Penerimaan Mahasiswa Baru STIKes Bogor Husada.">
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
            /* Color Palette */
            --primary-color: #f1a326;
            /* Hijau Identitas */
            --primary-hover: #ef942b;
            /* Hijau lebih gelap untuk hover */
            --surface-color: #ffffff;
            --bg-color: #f4f6f8;
            /* Abu-abu sangat muda untuk background body */
            --text-heading: #2d3436;
            --text-muted: #636e72;
            --input-bg: #f8f9fa;
            /* Background input saat idle */
            --input-border: #e9ecef;

            /* Spacing & Radius */
            --radius-md: 12px;
            --radius-lg: 16px;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-heading);
            overflow-x: hidden;
        }

        .auth-container {
            min-height: 100vh;
            display: flex;
            width: 100%;
        }

        /* --- Sisi Kiri (Visual) --- */
        .auth-cover-bg {
            flex: 1;
            background-image: url("{{ asset('dashboard_assets/assets/img/front-pages/backgrounds/foto_utama.png') }}");
            background-size: cover;
            background-position: center;
            position: relative;
            display: none;
            /* Hidden on mobile by default */
            overflow: hidden;
        }

        /* Gradient Overlay yang Lebih Modern */
        .auth-cover-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            /* Shorthand for top/right/bottom/left: 0 */
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0.4), rgba(0, 0, 0, 0.8));
            z-index: 1;
        }

        /* Efek Glassmorphism pada Text Container */
        .auth-cover-content {
            position: absolute;
            bottom: 8%;
            left: 8%;
            right: 8%;
            color: white;
            z-index: 2;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: var(--radius-lg);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            max-width: 600px;
        }

        /* --- Sisi Kanan (Form) --- */
        .auth-form-side {
            flex: 0 0 550px;
            /* Sedikit lebih lebar agar lega */
            background: var(--surface-color);
            padding: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 10;
            /* Shadow halus ke arah kiri */
            box-shadow: -10px 0 40px rgba(0, 0, 0, 0.04);
        }

        /* Responsiveness */
        @media (max-width: 991px) {
            .auth-cover-bg {
                display: none;
            }

            .auth-form-side {
                flex: 1;
                padding: 2rem;
                width: 100%;
                box-shadow: none;
            }
        }

        /* --- Input Styling yang Lebih Modern --- */
        .form-floating>.form-control {
            background-color: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: var(--radius-md);
            height: 56px;
            font-size: 0.95rem;
            transition: all 0.25s ease-in-out;
        }

        .form-floating>label {
            padding-left: 1rem;
            color: var(--text-muted);
            transition: all 0.25s ease-in-out;
        }

        /* State: Focus & Not Empty */
        .form-floating>.form-control:focus,
        .form-floating>.form-control:not(:placeholder-shown) {
            background-color: #fff;
            /* Jadi putih saat diketik */
            padding-top: 1.625rem;
            padding-bottom: 0.625rem;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(13, 138, 78, 0.1);
            /* Glow halus, bukan solid */
            outline: none;
        }

        /* State: Error */
        .form-control.is-invalid {
            border-color: #ff3e1d;
            background-image: none;
            /* Menghilangkan icon x bawaan bootstrap yang kadang mengganggu */
        }

        .form-control.is-invalid:focus {
            box-shadow: 0 0 0 4px rgba(255, 62, 29, 0.1);
        }

        /* --- Button Styling --- */
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 14px;
            font-weight: 600;
            font-size: 1rem;
            border-radius: var(--radius-md);
            letter-spacing: 0.3px;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(13, 138, 78, 0.25);
        }

        .btn-primary:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(13, 138, 78, 0.2);
        }

        /* --- Password Toggle --- */
        .password-toggle {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            z-index: 10;
            padding: 5px;
            border-radius: 50%;
            transition: color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle:hover {
            color: var(--primary-color);
            background-color: rgba(13, 138, 78, 0.05);
        }

        /* --- Animations --- */
        .animate-up {
            animation: fadeUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
            opacity: 0;
            /* Start invisible */
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Utility Tambahan */
        a {
            transition: color 0.2s;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .text-primary:hover {
            color: var(--primary-hover) !important;
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="auth-container">

        <div class="auth-cover-bg d-lg-block">
            <div class="auth-cover-content animate-up">
                <span class="badge bg-warning text-dark mb-3 px-3 py-2 rounded-pill">PMB Online 2025</span>
                <h1 class="fw-bold text-white text-bold display-5 mb-3">Selamat Datang Kembali</h1>
                <p class="fs-5 opacity-75 mb-0" style="line-height: 1.6;">
                    Masuk untuk melanjutkan pendaftaran, cek status seleksi, atau lengkapi berkas administrasi Anda di
                    STIKes Bogor Husada.
                </p>
            </div>
        </div>

        <div class="auth-form-side">
            <div class="w-100 animate-up" style="max-width: 400px;">

                <div class="text-center mb-5">
                    <a href="{{ url('/') }}">
                        <img src="{{ asset('dashboard_assets/assets/img/logo/logo_sbh_panjang.png') }}" alt="Logo SBH"
                            style="width: 180px;" class="mb-4">
                    </a>
                    <h4 class="fw-bold mb-1 text-dark">Masuk Akun</h4>
                    <p class="text-muted small">Silakan masukkan email dan password Anda.</p>
                </div>

                <form id="formAuthentication" action="{{ route('auth.store.login') }}" method="POST"
                    x-data="{ showPass: false }">
                    @csrf

                    <div class="form-floating mb-3">
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                            name="email" placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
                        <label for="email">Email Terdaftar</label>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-floating mb-3 position-relative">
                        <input :type="showPass ? 'text' : 'password'"
                            class="form-control @error('password') is-invalid @enderror" id="password" name="password"
                            placeholder="Password" required>
                        <label for="password">Password</label>

                        <button type="button" class="password-toggle" @click="showPass = !showPass" tabindex="-1">
                            <i :class="showPass ? 'bx bx-hide fs-4' : 'bx bx-show fs-4'"></i>
                        </button>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember-me" name="remember">
                            <label class="form-check-label text-muted small" for="remember-me">
                                Ingat Saya
                            </label>
                        </div>
                        {{-- <a href="#" class="text-primary fw-bold small text-decoration-none">Lupa Password?</a> --}}
                    </div>

                    <button type="submit" class="btn btn-primary w-100 shadow-sm mb-4" id="submitBtn">
                        <span class="submit-text">Masuk Sekarang</span>
                        <div class="spinner-border spinner-border-sm text-white d-none" role="status"></div>
                    </button>

                    <div class="text-center">
                        <p class="text-muted small mb-0">Belum punya akun?
                            <a href="{{ route('auth.register') }}"
                                class="text-primary fw-bold text-decoration-none">Daftar disini</a>
                        </p>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/js/bootstrap.js') }}"></script>

    <script src="{{ asset('dashboard_assets/assets/vendor/libs/iziToast/js/iziToast.min.js') }}"></script>

    @include('dashboard.components.notification')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('formAuthentication');
            const submitButton = document.getElementById('submitBtn');
            const submitText = submitButton.querySelector('.submit-text');
            const spinner = submitButton.querySelector('.spinner-border');

            form.addEventListener('submit', function (e) {
                // Biarkan browser handle validasi HTML5
                if (!form.checkValidity()) {
                    return;
                }

                // Prevent double submit
                if (submitButton.disabled) {
                    e.preventDefault();
                    return;
                }

                // UX: Loading State
                submitButton.disabled = true;
                submitText.innerHTML = "Memverifikasi...";
                spinner.classList.remove('d-none');

                // Tampilkan Notifikasi Modern (Nuansa Orange)
                iziToast.show({
                    theme: 'light',
                    color: '#fff',
                    layout: 2,
                    icon: 'bx bx-loader-alt bx-spin',
                    iconColor: '#ff7f00',          // <--- WARNA ORANGE UTAMA

                    title: 'Sedang Memproses',
                    titleColor: '#ff7f00',         // <--- WARNA JUDUL ORANGE
                    titleSize: '16px',
                    titleLineHeight: '1.5',

                    message: 'Memverifikasi kredensial Anda, mohon tunggu...',
                    messageColor: '#7a7a7a',
                    messageSize: '14px',

                    position: 'center',
                    transitionIn: 'fadeInUp',
                    transitionOut: 'fadeOutDown',

                    progressBar: true,
                    progressBarColor: '#ff7f00',   // <--- PROGRESS BAR ORANGE
                    progressBarEasing: 'linear',

                    overlay: true,
                    overlayClose: false,
                    overlayColor: 'rgba(0, 0, 0, 0.5)',

                    timeout: false,
                    maxWidth: 400,
                    zindex: 9999,

                    onOpening: function(instance, toast){
                        toast.style.borderRadius = '12px';
                        toast.style.boxShadow = '0 10px 30px rgba(255, 127, 0, 0.15)'; // <--- Shadow halus nuansa orange
                        toast.style.borderLeft = '4px solid #ff7f00'; // <--- Aksen border kiri orange
                    }
                });
            });
        });
    </script>
</body>

</html>