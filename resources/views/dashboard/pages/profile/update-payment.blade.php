@extends('dashboard.layout.master')
@section('title', 'Pembayaran PMB')

@section('content')
@php
    $hasProof = filled($user->img_bukti);
    $paymentWaiting = (int) $user->status_pemb === 1;
    $paymentVerified = in_array((int) $user->status_pemb, [
        \App\Enums\PmbStatus::Verified->value,
        \App\Enums\PmbStatus::Lulus->value,
    ], true);
    $paymentRejected = (int) $user->status_pemb === \App\Enums\PmbStatus::TidakLulus->value;
@endphp

<div class="student-page-shell">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <h4 class="fw-bold mb-1">Pembayaran Pendaftaran PMB</h4>
            <p class="text-muted mb-0">Gunakan rekening resmi SBH dan unggah bukti pembayaran dengan format yang benar.</p>
        </div>
        @include('dashboard.components.student-status-badge', [
            'label' => $paymentVerified ? 'Pembayaran Valid' : ($paymentRejected ? 'Tidak Lulus' : ($paymentWaiting ? 'Menunggu Verifikasi' : 'Belum Upload Bukti')),
            'tone' => $paymentVerified ? 'success' : ($paymentRejected ? 'danger' : ($paymentWaiting ? 'info' : 'warning')),
            'icon' => $paymentVerified ? 'bx-check-circle' : ($paymentRejected ? 'bx-x-circle' : ($paymentWaiting ? 'bx-time-five' : 'bx-wallet')),
        ])
    </div>

    <div class="student-payment-status bg-label-{{ $paymentVerified ? 'success' : ($paymentRejected ? 'danger' : ($paymentWaiting ? 'info' : 'warning')) }}">
        <i class="bx {{ $paymentVerified ? 'bx-check-circle' : ($paymentRejected ? 'bx-x-circle' : ($paymentWaiting ? 'bx-time-five' : 'bx-info-circle')) }}"></i>
        <div>
            <strong>
                @if($paymentVerified)
                    Pembayaran sudah divalidasi panitia.
                @elseif($paymentRejected)
                    Status PMB Anda tidak lulus.
                @elseif($paymentWaiting)
                    Bukti pembayaran sudah terkirim dan sedang diverifikasi.
                @else
                    Silakan unggah bukti pembayaran.
                @endif
            </strong>
            <p class="mb-0">
                @if($paymentVerified)
                    Simpan bukti pembayaran dan lanjutkan pemantauan status PMB dari dashboard.
                @elseif($paymentRejected)
                    Hubungi admin PMB jika membutuhkan konfirmasi lanjutan.
                @elseif($paymentWaiting)
                    Anda tetap dapat mengganti bukti pembayaran jika file sebelumnya kurang jelas.
                @else
                    Pastikan nominal, nama pengirim, tanggal, dan nomor rekening tujuan terlihat jelas.
                @endif
            </p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger d-flex gap-2" role="alert">
            <i class="bx bx-error-circle fs-4"></i>
            <div>
                <strong>Bukti pembayaran belum bisa dikirim.</strong>
                <div>Pastikan file berupa JPG/JPEG/PNG dan ukuran maksimal 2 MB.</div>
            </div>
        </div>
    @endif

    <div class="row g-4 align-items-stretch">
        <div class="col-xl-4 col-lg-5">
            <div class="student-payment-card h-100">
                <div class="card-body">
                    <div class="student-payment-bank">
                        <span class="badge bg-label-primary">Rekening Resmi</span>
                        <h5 class="mb-1">BSI Syariah</h5>
                        <p class="text-muted mb-0">Gunakan hanya rekening resmi berikut.</p>
                    </div>

                    <div class="student-payment-account">
                        <span>Nomor Rekening</span>
                        <strong>9999444331</strong>
                    </div>

                    <div class="student-payment-info-grid">
                        <div class="student-payment-info-item">
                            <span>Atas Nama</span>
                            <strong>YAYASAN HUSADA BOGOR</strong>
                        </div>
                        <div class="student-payment-info-item">
                            <span>Admin PMB</span>
                            <strong>SBH Nisa</strong>
                        </div>
                        <div class="student-payment-info-item">
                            <span>WhatsApp</span>
                            <strong>+62 811-1011-1560</strong>
                        </div>
                    </div>

                    <div class="student-payment-note">
                        <i class="bx bx-shield-quarter"></i>
                        <span>Tidak ada nomor rekening lain selain rekening resmi yang ditampilkan di halaman ini.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <form action="{{ route('dashboard.profile.updatedataPembayaran') }}" method="POST" enctype="multipart/form-data" class="student-payment-form h-100">
                @csrf
                <div class="student-payment-upload">
                    <div class="student-payment-upload__intro">
                        <div>
                            <span class="badge bg-label-info mb-2">{{ $hasProof ? 'Bukti Tersimpan' : 'Upload Bukti' }}</span>
                            <h5 class="mb-1">Bukti Pembayaran</h5>
                            <p class="text-muted mb-0">Unggah foto atau screenshot bukti transfer. Format JPG/JPEG/PNG maksimal 2 MB.</p>
                        </div>
                    </div>

                    @include('dashboard.components.student-upload-card', [
                        'name' => 'img_bukti',
                        'label' => 'File Bukti Pembayaran',
                        'currentUrl' => $user->pmbDocumentUrl('img_bukti'),
                        'currentFile' => $user->img_bukti,
                        'required' => true,
                        'description' => 'Pastikan tulisan pada bukti transfer terbaca jelas.',
                    ])

                    <div class="student-payment-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-upload me-1"></i> Kirim Bukti Pembayaran
                        </button>
                        <a href="https://wa.me/6281110111560?text=Halo%20Admin%20SBH%20Nisa,%20saya%20ingin%20konfirmasi%20bukti%20pembayaran."
                            target="_blank"
                            class="btn btn-outline-success">
                            <i class="bx bxl-whatsapp me-1"></i> Konfirmasi WhatsApp
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
