<!DOCTYPE html>
<html lang="id" class="light-style customizer-hide" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('dashboard_assets/assets/') }}" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Daftar PMB - STIKes Bogor Husada</title>
    <meta name="description" content="Pendaftaran Mahasiswa Baru STIKes Bogor Husada.">
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
            --surface-color: #ffffff;
            --bg-color: #f4f6f8;
            --text-heading: #2d3436;
            --text-muted: #636e72;
            --input-bg: #f8f9fa;
            --input-border: #e9ecef;
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
            /* Pastikan path gambar benar */
            background-image: url("{{ \App\Models\LandingMedia::where('key', 'auth_image')->first()?->url ?? asset('dashboard_assets/assets/img/front-pages/backgrounds/foto_utama.png') }}");
            background-size: cover;
            background-position: center;
            position: relative;
            display: none;
            overflow: hidden;
        }

        .auth-cover-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0.3), rgba(0, 0, 0, 0.8));
            z-index: 1;
        }

        /* Glassmorphism Box */
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
            flex: 0 0 600px;
            background: var(--surface-color);
            padding: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 10;
            box-shadow: -10px 0 40px rgba(0, 0, 0, 0.04);
            overflow-y: auto;
            max-height: 100vh;
        }

        @media (max-width: 991px) {
            .auth-cover-bg {
                display: none;
            }

            .auth-form-side {
                flex: 1;
                padding: 2rem;
                width: 100%;
                box-shadow: none;
                max-height: none;
            }
        }

        /* --- Input Styling --- */
        .form-floating>.form-control,
        .form-floating>.form-select {
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
        }

        .form-floating>.form-control:focus,
        .form-floating>.form-control:not(:placeholder-shown),
        .form-floating>.form-select:focus {
            background-color: #fff;
            padding-top: 1.625rem;
            padding-bottom: 0.625rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(13, 138, 78, 0.1);
            outline: none;
        }

        .form-control.is-invalid {
            border-color: #ff3e1d;
            background-image: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 14px;
            font-weight: 600;
            border-radius: var(--radius-md);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(121, 121, 121, 0.25);
        }

        /* Password Toggle */
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
        }

        /* Animasi */
        .animate-up {
            animation: fadeUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
            opacity: 0;
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

        /* Badges untuk Password Strength */
        .badge-soft-success {
            background-color: rgba(13, 138, 78, 0.1);
            color: var(--primary-color);
        }

        .badge-soft-secondary {
            background-color: #e9ecef;
            color: #6c757d;
        }
    </style>
</head>

<body>

    <div class="auth-container">
        <div class="auth-cover-bg d-lg-block">
            <div class="auth-cover-content animate-up">
                <span class="badge bg-warning text-dark mb-3 px-3 py-2 rounded-pill">Pendaftaran Dibuka</span>
                <h1 class="fw-bold text-white text-bold display-5 mb-3">Mulai Karier Masa Depan</h1>
                <p class="fs-5 opacity-75 mb-0" style="line-height: 1.6;">
                    Bergabunglah dengan ribuan tenaga kesehatan profesional lulusan STIKes Bogor Husada.
                </p>
            </div>
        </div>

        <div class="auth-form-side">
            <div class="w-100 animate-up" style="max-width: 480px;">

                <div class="text-center mb-5">
                    <a href="{{ url('/') }}">
                        <img src="{{ asset('dashboard_assets/assets/img/logo/logo_sbh_panjang.png') }}" alt="Logo SBH"
                            style="width: 180px;" class="mb-4">
                    </a>
                    <h4 class="fw-bold mb-1 text-dark">Registrasi Akun Baru</h4>
                    <p class="text-muted small">Isi data diri Anda untuk memulai pendaftaran.</p>
                </div>

                <form id="formAuthentication" method="POST" action="{{ route('auth.store.register') }}"
                    autocomplete="off">
                    @csrf

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="name" name="name" placeholder="John Doe"
                            value="{{ old('name') }}" required autofocus>
                        <label for="name">Nama Lengkap Siswa</label>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="name@example.com" value="{{ old('email') }}" required>
                                <label for="email">Alamat Email</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="phone" name="phone" placeholder="0812..."
                                    value="{{ old('phone') }}" required>
                                <label for="phone">No. WhatsApp</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" id="jurusan_id" name="jurusan_id" required>
                                    <option value="" disabled selected>Pilih Prodi</option>
                                    @foreach ($jurusans as $jurusan)
                                    <option value="{{ $jurusan->id }}">{{ $jurusan->nama_jurusan }}</option>
                                    @endforeach
                                </select>
                                <label for="jurusan_id">Minat Prodi</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" id="kuesioner_id" name="kuesioner_id" required>
                                    <option value="" disabled selected>Sumber Info</option>
                                    @foreach ($kuesioners as $kuesioner)
                                    <option value="{{ $kuesioner->id }}">{{ $kuesioner->nama_kusioner }}</option>
                                    @endforeach
                                </select>
                                <label for="kuesioner_id">Tahu Dari Mana?</label>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info small mb-3">
                        Password akun akan dibuat otomatis dan dikirimkan ke email setelah pendaftaran berhasil.
                    </div>

                    <div class="mb-4 form-check">
                        <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                        <label class="form-check-label text-muted small" for="terms">
                            Saya menyetujui <a href="#" class="text-primary fw-bold text-decoration-none">Syarat &
                                Ketentuan</a> Pendaftaran.
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 shadow-sm mb-4" id="submitBtn">
                        <span class="submit-text">Daftar Sekarang</span>
                        <div class="spinner-border spinner-border-sm text-white d-none" role="status"></div>
                    </button>

                    <div class="text-center">
                        <p class="text-muted small mb-0">Sudah punya akun?
                            <a href="{{ route('auth.login') }}" class="text-primary fw-bold text-decoration-none">Masuk
                                disini</a>
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {

        const form = document.getElementById('formAuthentication');
        const submitButton = document.getElementById('submitBtn');
        const submitText = submitButton.querySelector('.submit-text');
        const spinner = submitButton.querySelector('.spinner-border');

        let redirectUrl = null; // <-- ambil dari backend

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            if (submitButton.disabled) return;

            submitButton.disabled = true;
            submitText.innerHTML = "Memproses...";
            spinner.classList.remove('d-none');

            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

            iziToast.show({
                title: 'Sedang Memproses',
                message: 'Mohon tunggu sebentar...',
                position: 'center',
                timeout: false,
                overlay: true,
                close: false
            });

            try {
                const formData = new FormData(form);

                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const text = await response.text();
                let data;

                try {
                    data = JSON.parse(text);
                } catch {
                    console.error("SERVER RETURNED HTML:", text);
                    throw new Error("Server error");
                }

                iziToast.destroy();

                if (!response.ok) {
                    throw data;
                }

                // ================================
                // 🔥 INI KUNCI UTAMA
                // ================================
                redirectUrl = data.redirect;

                if (!redirectUrl) {
                    throw new Error("Server tidak mengirim redirect URL");
                }

                iziToast.success({
                    title: 'Registrasi Berhasil!',
                    message: 'Mengalihkan ke halaman verifikasi...',
                    position: 'center',
                    timeout: 2000,
                    overlay: true,
                    onClosed: function () {
                        window.location.href = redirectUrl;
                    }
                });

            } catch (error) {

                iziToast.destroy();

                submitButton.disabled = false;
                submitText.innerHTML = "Daftar Sekarang";
                spinner.classList.add('d-none');

                if (error.errors) {
                    Object.keys(error.errors).forEach(field => {
                        const input = document.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            let feedback = input.parentNode.querySelector('.invalid-feedback');
                            if (!feedback) {
                                feedback = document.createElement('div');
                                feedback.className = 'invalid-feedback';
                                input.parentNode.appendChild(feedback);
                            }
                            feedback.innerText = error.errors[field][0];
                        }

                        iziToast.error({
                            title: 'Validasi Gagal',
                            message: error.errors[field][0],
                            position: 'topRight'
                        });
                    });
                } else {
                    iziToast.error({
                        title: 'Terjadi Kesalahan',
                        message: error.message || 'Server error',
                        position: 'center'
                    });
                }
            }
        });

    });
    </script>
</body>

</html>
