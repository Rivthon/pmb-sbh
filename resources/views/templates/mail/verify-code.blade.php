<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Akun - STIKes Bogor Husada</title>
    <style>
        body { background-color: #f7fafc; font-family: 'Helvetica', Arial, sans-serif; color: #2d3748; margin: 0; padding: 0; }
        .container { max-width: 580px; margin: 40px auto; background: #ffffff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); padding: 40px; }
        h1 { color: #1a202c; text-align: center; font-size: 22px; margin-bottom: 24px; }
        p { line-height: 1.6; margin-bottom: 16px; }
        .info-box { background: #f1f5f9; border: 1px dashed #cbd5e0; border-radius: 8px; padding: 12px 16px; margin-bottom: 12px; font-weight: 600; }
        .verify-code { background: #edf2f7; border: 2px dashed #718096; color: #2d3748; font-size: 28px; font-weight: bold; letter-spacing: 4px; text-align: center; padding: 14px; border-radius: 8px; margin: 24px 0; }
        .btn { display: inline-block; text-align: center; background-color: #4f46e5; color: #fff !important; font-weight: bold; padding: 12px 20px; border-radius: 6px; text-decoration: none; margin: 20px 0; }
        .footer { text-align: center; font-size: 12px; color: #718096; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>👋 Halo, {{ $user_name }}</h1>

        <p>
            Terima kasih telah melakukan pendaftaran di <strong>STIKes Bogor Husada</strong>.
            Berikut adalah detail pendaftaran dan kode verifikasi akun Anda:
        </p>

        <div class="info-box">
            👤 <strong>Nama Calon Mahasiswa:</strong> {{ $user_name }}<br>
            📘 <strong>Program Studi Pilihan:</strong> {{ $prodi ?? '-' }}
        </div>

        @if (!empty($password_plaintext))
            <div class="info-box">
                🔑 <strong>Password Akun:</strong> {{ $password_plaintext }}
            </div>
        @endif

        <p>Kode Verifikasi (OTP) Anda:</p>
        <div class="verify-code">
            {{ $verifyCode }}
        </div>

        <p style="text-align: center;">
            <a href="{{ route('auth.verify-account', $user_id) }}" class="btn">
                🔗 Verifikasi Akun Sekarang
            </a>
        </p>

        <p>
            Jika tombol di atas tidak berfungsi, salin dan buka tautan berikut pada browser Anda:<br>
            <a href="{{ route('auth.verify-account', $user_id) }}" style="color:#4f46e5;">
                {{ route('auth.verify-account', $user_id) }}
            </a>
        </p>

        <p>
            📩 Jika belum menemukan email di kotak masuk, silakan periksa folder <strong>Spam/Junk</strong>.
        </p>

        <div class="footer">
            Terima kasih,<br>
            <strong>Panitia PMB – STIKes Bogor Husada</strong><br>
            &copy; {{ date('Y') }} STIKes Bogor Husada
        </div>
    </div>
</body>
</html>
