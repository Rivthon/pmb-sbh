@extends('dashboard.layout.master')
@section('title', 'Dashboard PMB')

@section('content')
@php
    $user = auth()->user();
    $pmbStatus = \App\Enums\PmbStatus::fromValue($user->status_pemb);
    $hasBiodata = (int) $user->status_biodata === 1;
    $missingDocuments = $user->missingPmbRequiredDocuments();
    $hasUploadedDocuments = empty($missingDocuments);
    $documentMissingLabel = $user->missingPmbRequiredDocumentsLabel();
    $hasDocuments = $hasUploadedDocuments && (int) $user->status_berkas === 1;
    $documentDescription = $hasDocuments
        ? 'Berkas wajib sudah dikirim.'
        : ($hasUploadedDocuments ? 'Berkas sudah lengkap dan menunggu validasi panitia.' : $documentMissingLabel . ' agar pendaftaran bisa diproses.');
    $documentCardLabel = $hasDocuments ? 'Terkirim' : ($hasUploadedDocuments ? 'Menunggu' : $documentMissingLabel);
    $hasPaymentProof = filled($user->img_bukti) && (int) $user->status_pemb > 0;
    $paymentVerified = in_array((int) $user->status_pemb, [2, 3], true);
    $hasQueue = isset($latestQueue) && $latestQueue;
    $isOnlineSelection = $hasQueue && $latestQueue->isOnlineSelection();
    $isFinal = in_array((int) $user->status_pemb, [3, 4], true);

    if (!$hasBiodata) {
        $cta = ['title' => 'Lengkapi Biodata PMB', 'description' => 'Isi data identitas, alamat, akademik, dan orang tua agar pendaftaran bisa diproses.', 'href' => route('dashboard.profile.index'), 'button' => 'Lengkapi Biodata'];
    } elseif (!$hasDocuments) {
        $cta = ['title' => $hasUploadedDocuments ? 'Menunggu Validasi Berkas' : 'Upload Berkas Wajib', 'description' => $documentDescription, 'href' => route('dashboard.profile.editBerkas'), 'button' => $hasUploadedDocuments ? 'Lihat Berkas' : 'Upload Berkas'];
    } elseif (!$hasPaymentProof) {
        $cta = ['title' => 'Upload Bukti Pembayaran', 'description' => 'Kirim bukti transfer resmi agar panitia dapat memverifikasi pembayaran Anda.', 'href' => route('dashboard.profile.updatePayment'), 'button' => 'Upload Bukti'];
    } elseif (!$paymentVerified) {
        $cta = ['title' => 'Menunggu Verifikasi Pembayaran', 'description' => 'Bukti pembayaran Anda sudah terkirim. Panitia akan memverifikasi sebelum kartu ujian dan tahap berikutnya dibuka.', 'href' => route('dashboard.profile.cetakKartu'), 'button' => 'Lihat Status PMB'];
    } elseif ($isOnlineSelection) {
        $cta = ['title' => 'Lanjutkan Seleksi Online', 'description' => 'Tes, anamnesa, surat kesehatan, dan form wawancara dipantau tanpa QR atau antrean fisik.', 'href' => route('dashboard.selection.index'), 'button' => 'Buka Seleksi Online'];
    } elseif ($hasQueue) {
        $cta = ['title' => 'Pantau Tes Offline', 'description' => 'Lihat kartu tes, nomor antrian, ruangan, dan status panggilan terbaru.', 'href' => route('dashboard.pmb-queue.show', $latestQueue), 'button' => 'Lihat Kartu Tes'];
    } else {
        $cta = ['title' => 'Pantau Status PMB', 'description' => 'Pendaftaran Anda sedang diproses. Cek status PMB dan kartu ujian secara berkala.', 'href' => route('dashboard.profile.cetakKartu'), 'button' => 'Lihat Status PMB'];
    }

    $steps = [
        ['label' => 'Biodata', 'caption' => $hasBiodata ? 'Sudah terisi' : 'Perlu dilengkapi', 'state' => $hasBiodata ? 'complete' : 'active', 'icon' => 'bx-id-card'],
        ['label' => 'Berkas', 'caption' => $hasDocuments ? 'Dokumen terkirim' : ($hasUploadedDocuments ? 'Menunggu validasi' : $documentMissingLabel), 'state' => $hasDocuments ? 'complete' : ($hasBiodata ? 'active' : 'pending'), 'icon' => 'bx-folder-open'],
        ['label' => 'Pembayaran', 'caption' => $paymentVerified ? 'Pembayaran valid' : ($hasPaymentProof ? 'Menunggu verifikasi' : 'Upload bukti'), 'state' => $paymentVerified ? 'complete' : ($hasDocuments ? 'active' : 'pending'), 'icon' => 'bx-wallet'],
        ['label' => 'Tes', 'caption' => 'Tahap seleksi', 'state' => $paymentVerified ? 'active' : 'pending', 'icon' => 'bx-edit-alt'],
        ['label' => $isOnlineSelection ? 'Online' : 'Antrian', 'caption' => $isOnlineSelection ? 'Tanpa antrean fisik' : ($hasQueue ? $latestQueue->statusLabel() : 'Belum ada sesi'), 'state' => $hasQueue ? 'active' : 'pending', 'icon' => $isOnlineSelection ? 'bx-laptop' : 'bx-qr-scan'],
        ['label' => 'Pengumuman', 'caption' => $isFinal ? $pmbStatus->label() : 'Menunggu hasil', 'state' => $isFinal ? ((int) $user->status_pemb === 4 ? 'danger' : 'complete') : 'pending', 'icon' => 'bx-megaphone'],
    ];

    $cards = [
        ['title' => 'Biodata', 'description' => $hasBiodata ? 'Data utama sudah tersimpan.' : 'Lengkapi data pribadi Anda.', 'tone' => $hasBiodata ? 'success' : 'warning', 'icon' => 'bx-id-card', 'label' => $hasBiodata ? 'Lengkap' : 'Belum Lengkap', 'href' => route('dashboard.profile.index')],
        ['title' => 'Berkas', 'description' => $documentDescription, 'tone' => $hasDocuments ? 'success' : ($hasUploadedDocuments ? 'info' : 'warning'), 'icon' => 'bx-folder-open', 'label' => $documentCardLabel, 'href' => route('dashboard.profile.editBerkas')],
        ['title' => 'Pembayaran', 'description' => $paymentVerified ? 'Pembayaran sudah divalidasi panitia.' : ($hasPaymentProof ? 'Bukti pembayaran menunggu verifikasi.' : 'Upload bukti transfer PMB.'), 'tone' => $paymentVerified ? 'success' : ($hasPaymentProof ? 'info' : 'warning'), 'icon' => 'bx-wallet', 'label' => $paymentVerified ? 'Valid' : ($hasPaymentProof ? 'Menunggu' : 'Belum Upload'), 'href' => route('dashboard.profile.updatePayment')],
        ['title' => 'Status PMB', 'description' => $pmbStatus->label(), 'tone' => $pmbStatus->color(), 'icon' => $pmbStatus->icon(), 'label' => $pmbStatus->label(), 'href' => route('dashboard.profile.cetakKartu')],
    ];
@endphp

<div class="student-page-shell">
    <section class="student-hero">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge bg-white text-primary mb-3">PMB STIKes Bogor Husada</span>
                <h3 class="fw-bold mb-2">Halo, {{ $user->name }}</h3>
                <p class="mb-0">Pantau proses pendaftaran, dokumen, pembayaran, tes, dan antrian Anda dari satu dashboard.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                @include('dashboard.components.student-status-badge', [
                    'label' => $pmbStatus->label(),
                    'tone' => $pmbStatus->color(),
                    'icon' => $pmbStatus->icon(),
                ])
            </div>
        </div>
    </section>

    @include('dashboard.components.student-cta-card', $cta)

    @include('dashboard.components.student-progress-stepper', ['steps' => $steps])

    <div class="row g-4">
        @foreach($cards as $card)
            <div class="col-md-6 col-xl-3">
                <a href="{{ $card['href'] }}" class="student-status-card d-block text-decoration-none">
                    <span class="student-status-card__icon bg-label-{{ $card['tone'] }}">
                        <i class="bx {{ $card['icon'] }}"></i>
                    </span>
                    <div class="d-flex justify-content-between gap-2 align-items-start mb-2">
                        <h5 class="mb-0 text-body">{{ $card['title'] }}</h5>
                        @include('dashboard.components.student-status-badge', [
                            'label' => $card['label'],
                            'tone' => $card['tone'],
                        ])
                    </div>
                    <p class="text-muted mb-0">{{ $card['description'] }}</p>
                </a>
            </div>
        @endforeach
    </div>

    @if($hasQueue && !$isOnlineSelection)
        <div class="card">
            <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <span class="badge bg-label-info mb-2">Antrian Tes Offline</span>
                    <h5 class="mb-1">{{ $latestQueue->session->name }}</h5>
                    <p class="text-muted mb-0">
                        {{ $latestQueue->statusLabel() }}
                        @if($latestQueue->queue_code) - Nomor {{ $latestQueue->queue_code }} @endif
                        @if($latestQueue->room) - {{ $latestQueue->room->name }} @endif
                    </p>
                </div>
                <a href="{{ route('dashboard.pmb-queue.show', $latestQueue) }}" class="btn btn-info">
                    Lihat Kartu Tes
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
