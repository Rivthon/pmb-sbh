<!DOCTYPE html>
<html
  lang="id"
  class="light-style layout-wide customizer-hide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="{{ asset('dashboard_assets/assets/') }}"
  data-template="vertical-menu-template-free">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Masuk Admin - STIKES BOGOR HUSADA</title>

    <!-- Meta Tags -->
    <meta itemprop="name" content="STIKES BOGOR HUSADA PENERIMAAN MAHASISWA BARU">
    <meta itemprop="description" content="Penerimaan Mahasiswa Baru STIKES BOGOR HUSADA - Portal Administrasi Terpadu">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('dashboard_assets/assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/libs/iziToast/css/iziToast.min.css') }}">

    <!-- Premium Custom Split CSS -->
    <style>
      html, body {
        height: 100%;
        margin: 0;
        font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        background-color: #f8fafc;
        overflow-x: hidden;
      }

      .login-container {
        display: flex;
        min-height: 100vh;
        width: 100%;
      }

      /* Left Panel - Branding Sidebar */
      .login-sidebar {
        flex: 1.2;
        background: radial-gradient(circle at 10% 20%, rgb(15, 23, 42) 0%, rgb(30, 27, 75) 100%);
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 4rem;
        color: #ffffff;
      }

      .login-sidebar::before {
        content: '';
        position: absolute;
        top: -10%;
        left: -10%;
        width: 60%;
        height: 60%;
        background: radial-gradient(circle, rgba(105, 108, 255, 0.18) 0%, rgba(105, 108, 255, 0) 70%);
        pointer-events: none;
      }

      .login-sidebar::after {
        content: '';
        position: absolute;
        bottom: -10%;
        right: -10%;
        width: 50%;
        height: 50%;
        background: radial-gradient(circle, rgba(3, 195, 236, 0.15) 0%, rgba(3, 195, 236, 0) 70%);
        pointer-events: none;
      }

      .sidebar-grid {
        position: absolute;
        inset: 0;
        opacity: 0.04;
        background-image: radial-gradient(circle, #ffffff 1px, transparent 1px);
        background-size: 24px 24px;
        pointer-events: none;
      }

      .sidebar-header {
        z-index: 10;
      }

      .logo-wrapper {
        display: inline-flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.98);
        padding: 0.75rem 1.75rem;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
      }

      .logo-img {
        height: 50px;
        width: auto;
        object-fit: contain;
      }

      .sidebar-body {
        margin-top: auto;
        margin-bottom: auto;
        z-index: 10;
      }

      .system-badge {
        background: rgba(105, 108, 255, 0.15);
        border: 1px solid rgba(105, 108, 255, 0.3);
        color: #8385ff;
        padding: 0.4rem 1.2rem;
        border-radius: 100px;
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 1px;
        display: inline-block;
        margin-bottom: 1.5rem;
        text-transform: uppercase;
      }

      .system-title {
        font-size: 2.75rem;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
      }

      .institution-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #94a3b8;
        margin-bottom: 1.5rem;
      }

      .system-desc {
        font-size: 0.95rem;
        color: #cbd5e1;
        line-height: 1.6;
        margin-bottom: 3rem;
        max-width: 520px;
      }

      .feature-list {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
        max-width: 600px;
      }

      .feature-item {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(8px);
        padding: 1.25rem;
        border-radius: 16px;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
      }

      .feature-item:hover {
        background: rgba(255, 255, 255, 0.07);
        border-color: rgba(105, 108, 255, 0.3);
        transform: translateY(-3px);
      }

      .feature-icon-box {
        width: 38px;
        height: 38px;
        background: rgba(105, 108, 255, 0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #8385ff;
        font-size: 1.2rem;
        border: 1px solid rgba(105, 108, 255, 0.3);
      }

      .feature-label {
        font-size: 0.9rem;
        font-weight: 600;
        color: #f8fafc;
      }

      .sidebar-footer {
        z-index: 10;
        font-size: 0.8rem;
        color: #64748b;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        padding-top: 1.5rem;
        width: 100%;
      }

      /* Right Panel - Form login Area */
      .login-form-area {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4rem;
        background: #f8fafc;
        position: relative;
      }

      .login-form-card {
        width: 100%;
        max-width: 440px;
        background: #ffffff;
        border-radius: 20px;
        padding: 3rem 2.5rem;
        box-shadow: 0 10px 30px rgba(67, 89, 113, 0.06);
        border: 1px solid #e2e8f0;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
      }

      .login-header {
        text-align: left;
        margin-bottom: 2rem;
      }

      .login-title {
        font-size: 1.85rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 0.5rem;
        letter-spacing: -0.5px;
      }

      .login-subtitle {
        font-size: 0.9rem;
        color: #64748b;
        line-height: 1.5;
      }

      /* Form inputs */
      .form-group-custom {
        position: relative;
        margin-bottom: 1.5rem;
      }

      .form-label-custom {
        font-size: 0.78rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: 0.5rem;
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }

      .input-group-custom {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
      }

      .input-icon {
        position: absolute;
        left: 1rem;
        color: #94a3b8;
        font-size: 1.2rem;
        pointer-events: none;
        transition: color 0.2s ease;
        z-index: 5;
      }

      .input-control-custom {
        width: 100%;
        padding: 0.85rem 1rem 0.85rem 2.75rem;
        font-size: 0.92rem;
        color: #1e293b;
        background-color: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        outline: none;
        transition: all 0.2s ease;
      }

      .input-control-custom:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 4px rgba(105, 108, 255, 0.1);
      }

      .input-control-custom:focus + .input-icon {
        color: #696cff;
      }

      .input-control-custom.is-invalid {
        border-color: #ff3e1d;
      }

      .input-control-custom.is-invalid:focus {
        box-shadow: 0 0 0 4px rgba(255, 62, 29, 0.1);
      }

      .password-input-wrapper {
        position: relative;
        width: 100%;
      }

      .password-toggle-btn {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        padding: 0;
        z-index: 10;
        transition: color 0.2s ease;
      }

      .password-toggle-btn:hover {
        color: #696cff;
      }

      .input-control-password {
        padding-right: 2.75rem !important;
      }

      .remember-forgot-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.75rem;
      }

      .checkbox-custom-label {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        color: #475569;
        cursor: pointer;
        user-select: none;
      }

      .checkbox-custom {
        width: 17px;
        height: 17px;
        border-radius: 4px;
        border: 1.5px solid #cbd5e1;
        cursor: pointer;
      }

      .btn-login-custom {
        width: 100%;
        padding: 0.85rem 1.5rem;
        font-size: 0.92rem;
        font-weight: 700;
        color: #ffffff;
        background-color: #696cff;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(105, 108, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
      }

      .btn-login-custom:hover {
        background-color: #5558ff;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(105, 108, 255, 0.3);
      }

      .btn-login-custom:active {
        transform: translateY(0);
      }

      .btn-login-custom:disabled {
        background-color: #a1acb8;
        box-shadow: none;
        cursor: not-allowed;
        transform: none;
      }

      .security-notice {
        margin-top: 2rem;
        padding: 0.85rem 1rem;
        background-color: #f8fafc;
        border-radius: 10px;
        border-left: 3px solid #64748b;
        display: flex;
        align-items: center;
        gap: 0.5rem;
      }

      .security-notice i {
        color: #64748b;
        font-size: 1.1rem;
      }

      .security-notice span {
        font-size: 0.74rem;
        font-weight: 600;
        color: #64748b;
      }

      /* Responsive styling */
      @media (max-width: 991px) {
        .login-container {
          flex-direction: column;
        }
        .login-sidebar {
          flex: none;
          padding: 2.5rem 1.5rem;
          text-align: center;
          align-items: center;
        }
        .logo-wrapper {
          margin-bottom: 1.5rem;
        }
        .sidebar-body {
          margin: 1rem 0;
        }
        .system-title {
          font-size: 2rem;
        }
        .institution-title {
          font-size: 1.2rem;
          margin-bottom: 1rem;
        }
        .system-desc {
          max-width: 100%;
          margin-bottom: 2rem;
          font-size: 0.9rem;
        }
        .feature-list {
          grid-template-columns: 1fr;
          gap: 0.75rem;
          width: 100%;
          max-width: 420px;
        }
        .login-form-area {
          padding: 2.5rem 1.25rem;
        }
        .login-form-card {
          padding: 2.25rem 1.5rem;
        }
      }
    </style>

    <!-- Helpers -->
    <script src="{{ asset('dashboard_assets/assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/js/config.js') }}"></script>
  </head>

  <body>
    <!-- Content Wrapper -->
    <div class="login-container">
      
      <!-- Left Panel: Branding / Information Info -->
      <div class="login-sidebar">
        <div class="sidebar-grid"></div>

        <!-- Campus Logo -->
        <div class="sidebar-header">
          <div class="logo-wrapper">
            <img src="{{ asset('logo/logo_sbh.png') }}" alt="Logo STIKES BOGOR HUSADA" class="logo-img" />
          </div>
        </div>

        <!-- Information & Highlights -->
        <div class="sidebar-body">
          <span class="system-badge">Portal Administrasi</span>
          <h1 class="system-title">Sistem PMB Admin</h1>
          <h2 class="institution-title">STIKES BOGOR HUSADA</h2>
          <p class="system-desc">
            Selamat datang kembali di Portal Penerimaan Mahasiswa Baru. Silakan masuk menggunakan kredensial resmi Anda untuk mengelola seluruh rangkaian pendaftaran mahasiswa secara terpadu.
          </p>

          <!-- Core Features -->
          <div class="feature-list">
            <div class="feature-item">
              <div class="feature-icon-box">
                <i class="bx bx-user-check"></i>
              </div>
              <span class="feature-label">Kelola Data Pendaftar</span>
            </div>
            <div class="feature-item">
              <div class="feature-icon-box">
                <i class="bx bx-shield-quarter"></i>
              </div>
              <span class="feature-label">Verifikasi Berkas</span>
            </div>
            <div class="feature-item">
              <div class="feature-icon-box">
                <i class="bx bx-credit-card"></i>
              </div>
              <span class="feature-label">Monitoring Pembayaran</span>
            </div>
            <div class="feature-item">
              <div class="feature-icon-box">
                <i class="bx bx-bar-chart-alt-2"></i>
              </div>
              <span class="feature-label">Laporan PMB Realtime</span>
            </div>
          </div>
        </div>

        <!-- Footnote info -->
        <div class="sidebar-footer">
          <span>&copy; {{ date('Y') }} STIKES BOGOR HUSADA. Hak Cipta Dilindungi Undang-Undang.</span>
        </div>
      </div>

      <!-- Right Panel: Form Login -->
      <div class="login-form-area">
        <div class="login-form-card animate-fade-in">
          
          <!-- Title & Greeting -->
          <div class="login-header">
            <h3 class="login-title">Masuk Admin</h3>
            <p class="login-subtitle">Gunakan akun admin resmi Anda untuk masuk ke sistem dashboard PMB.</p>
          </div>

          <!-- Alert error login di atas form -->
          @if(session('toastError') || $errors->any())
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-start gap-2 mb-4" role="alert">
              <i class="bx bx-error-circle fs-5 mt-0.5" style="min-width: 20px;"></i>
              <div style="font-size: 0.85rem; line-height: 1.4;">
                @if(session('toastError'))
                  <span class="fw-semibold">{{ session('toastError') }}</span>
                @else
                  <span class="fw-semibold">Gagal masuk.</span> Silakan periksa kembali email dan kata sandi Anda.
                @endif
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          <!-- Alert session success di atas form -->
          @if(session('toastSuccess') || session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-start gap-2 mb-4" role="alert">
              <i class="bx bx-check-circle fs-5 mt-0.5" style="min-width: 20px;"></i>
              <div style="font-size: 0.85rem; line-height: 1.4;">
                <span class="fw-semibold">{{ session('toastSuccess') ?? session('success') }}</span>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          <!-- Authentication Form -->
          <form id="formAuthentication" action="{{ route('admin.login.store') }}" method="POST">
            @csrf

            <!-- Email Input Group -->
            <div class="form-group-custom">
              <label for="email" class="form-label-custom">Alamat Email</label>
              <div class="input-group-custom">
                <input
                  type="email"
                  class="input-control-custom @error('email') is-invalid @enderror"
                  id="email"
                  name="email"
                  placeholder="admin@sbh.ac.id"
                  value="{{ old('email') }}"
                  autocomplete="username"
                  autofocus
                  required />
                <i class="bx bx-envelope input-icon"></i>
              </div>
              @error('email')
                <div class="text-danger fw-semibold mt-1" style="font-size: 0.78rem;">
                  {{ $message }}
                </div>
              @enderror
            </div>

            <!-- Password Input Group -->
            <div class="form-group-custom">
              <label class="form-label-custom" for="password">Kata Sandi</label>
              <div class="input-group-custom">
                <div class="password-input-wrapper">
                  <input
                    type="password"
                    id="password"
                    class="input-control-custom input-control-password @error('password') is-invalid @enderror"
                    name="password"
                    placeholder="Masukkan kata sandi"
                    autocomplete="current-password"
                    required />
                  <button type="button" class="password-toggle-btn" id="passwordToggle" aria-label="Tampilkan kata sandi">
                    <i class="bx bx-hide" id="passwordToggleIcon"></i>
                  </button>
                </div>
                <i class="bx bx-lock-alt input-icon"></i>
              </div>
              @error('password')
                <div class="text-danger fw-semibold mt-1" style="font-size: 0.78rem;">
                  {{ $message }}
                </div>
              @enderror
            </div>

            <!-- Remember Me Block -->
            <div class="remember-forgot-row">
              <label class="checkbox-custom-label">
                <input type="checkbox" name="remember" id="remember" class="checkbox-custom" {{ old('remember') ? 'checked' : '' }}>
                <span>Ingat Sesi Saya</span>
              </label>
            </div>

            <!-- Action Button -->
            <button class="btn-login-custom" type="submit" id="btnSubmit">
              <i class="bx bx-log-in-circle fs-5"></i>
              <span>Masuk Ke Dashboard</span>
            </button>
          </form>

          <!-- Security notice block -->
          <div class="security-notice">
            <i class="bx bx-shield-quarter"></i>
            <span>Akses hanya untuk administrator yang berwenang. Seluruh aktivitas dalam sesi ini dicatat dalam log sistem.</span>
          </div>

        </div>
      </div>

    </div>

    <!-- Core Scripts -->
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/iziToast/js/iziToast.min.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/js/custom.js') }}"></script>

    <!-- Page Notifications -->
    @include('dashboard.components.notification')

    <!-- Interactive script show/hide password and loading submit spinner -->
    <script>
      $(document).ready(function() {
        // --- Show/Hide Password Toggle ---
        const $passwordInput = $('#password');
        const $toggleBtn = $('#passwordToggle');
        const $toggleIcon = $('#passwordToggleIcon');

        $toggleBtn.on('click', function() {
          const type = $passwordInput.attr('type') === 'password' ? 'text' : 'password';
          $passwordInput.attr('type', type);
          
          if (type === 'text') {
            $toggleIcon.removeClass('bx-hide').addClass('bx-show');
          } else {
            $toggleIcon.removeClass('bx-show').addClass('bx-hide');
          }
        });

        // --- Button Loading State on Form Submit ---
        $('#formAuthentication').on('submit', function(e) {
          const $btn = $('#btnSubmit');
          $btn.prop('disabled', true);
          $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Membuka Sesi...');
        });
      });
    </script>
  </body>
</html>
