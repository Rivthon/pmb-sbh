@extends('dashboard.layout.master')

{{-- 1. BAGIAN CSS (Elastic Shockwave Style) --}}
<style>
    /* Container untuk merapikan posisi */
    .success-anim-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 2rem;
        position: relative;
        height: 150px;
        /* Memberi ruang agar gelombang tidak terpotong */
    }

    /* Lingkaran Utama */
    .success-circle {
        width: 100px;
        height: 100px;
        /* Gradient Hijau Modern (Campuran Success & Teal) */
        background: linear-gradient(135deg, #198754, #20c997);
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        z-index: 10;
        box-shadow: 0 10px 30px rgba(25, 135, 84, 0.4);
        /* Animasi masuk lingkaran utama */
        animation: circle-entry 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        transform: scale(0);
    }

    /* Gelombang 1 & 2 (Ripple Effect) */
    .success-circle::before,
    .success-circle::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: 50%;
        border: 2px solid #198754;
        opacity: 0;
        z-index: -1;
    }

    /* Gelombang Pertama */
    .success-circle::before {
        animation: shockwave 2.5s infinite ease-out;
        animation-delay: 0.6s;
    }

    /* Gelombang Kedua */
    .success-circle::after {
        animation: shockwave 2.5s infinite ease-out;
        animation-delay: 1.1s;
    }

    /* Icon Checklist */
    .success-circle i {
        color: #fff;
        font-size: 4.5rem;
        /* Animasi icon membal */
        animation: check-bounce 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
        animation-delay: 0.4s;
        opacity: 0;
        transform: scale(0.5) rotate(-20deg);
    }

    /* --- Keyframes --- */
    @keyframes circle-entry {
        0% {
            transform: scale(0);
            opacity: 0;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    @keyframes shockwave {
        0% {
            transform: scale(1);
            opacity: 0.8;
            border-width: 4px;
        }

        100% {
            transform: scale(2.2);
            opacity: 0;
            border-width: 0px;
        }
    }

    @keyframes check-bounce {
        0% {
            transform: scale(0) rotate(-45deg);
            opacity: 0;
        }

        70% {
            transform: scale(1.2) rotate(5deg);
            opacity: 1;
        }

        100% {
            transform: scale(1) rotate(0deg);
            opacity: 1;
        }
    }
</style>


@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body text-center py-5 px-4">

                    {{-- 2. BAGIAN ICON ANIMASI (UPDATED) --}}
                    {{-- Menggunakan class wrapper baru 'success-anim-wrapper' --}}
                    <div class="success-anim-wrapper">
                        <div class="success-circle">
                            {{-- Icon Boxicons --}}
                            <i class='bx bx-check'></i>
                        </div>
                    </div>
                    {{-- END ANIMATION --}}

                    {{-- Judul Utama --}}
                    <h2 class="fw-bold mb-3">Tes Selesai Dikerjakan!</h2>

                    {{-- Pesan Terima Kasih & Konfirmasi --}}
                    <p class="text-muted fs-5 mb-4">
                        Terima kasih telah meluangkan waktu untuk menyelesaikan tes
                        <strong class="text-dark">{{ $hasil->tesTulis->nama_tes }}</strong>.
                        <br>
                        Jawaban Anda telah berhasil kami rekam ke dalam sistem.
                    </p>

                    {{-- Informasi Next Step --}}
                    <div class="alert alert-light border shadow-sm text-start d-inline-block mx-auto mb-4"
                        style="max-width: 600px;">
                        <div class="d-flex">
                            <i class="bi bi-info-circle-fill text-primary mt-1 me-3 fs-5"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Informasi Selanjutnya</h6>
                                <p class="mb-0 small text-muted">
                                    Tim kami akan meninjau hasil tes Anda. Silakan cek email atau pantau dashboard ini
                                    secara berkala untuk informasi mengenai tahapan seleksi berikutnya.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Detail Waktu Submit --}}
                    <div class="text-muted small mb-5">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">
                            <i class="bi bi-clock-history me-1"></i>
                            Disubmit pada: {{ \Carbon\Carbon::parse($hasil->waktu_selesai)->translatedFormat('d F Y,
                            H:i') }} WIB
                        </span>
                    </div>

                    {{-- Tombol Kembali --}}
                    <div>
                        <a href="{{ route('dashboard.user.tes-tulis.index') }}"
                            class="btn btn-primary px-4 py-2 rounded-3">
                            <i class="bi bi-arrow-left me-2"></i> Kembali ke Dashboard
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection