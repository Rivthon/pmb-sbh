@extends('dashboard.layout.master')
@section('title', 'Status PMB')

@section('content')
@php
    $pmbStatus = \App\Enums\PmbStatus::fromValue($user->status_pemb);
    $hasBiodata = (int) $user->status_biodata === 1;
    $missingDocuments = $user->missingPmbRequiredDocuments();
    $hasUploadedDocuments = empty($missingDocuments);
    $documentMissingLabel = $user->missingPmbRequiredDocumentsLabel();
    $hasDocuments = $hasUploadedDocuments && (int) $user->status_berkas === 1;
    $hasPaymentProof = filled($user->img_bukti) && (int) $user->status_pemb > 0;
    $paymentVerified = in_array((int) $user->status_pemb, [2, 3], true);
    $timeline = [
        ['title' => 'Biodata', 'description' => $hasBiodata ? 'Biodata sudah dikirim.' : 'Biodata belum lengkap.', 'tone' => $hasBiodata ? 'success' : 'warning', 'icon' => $hasBiodata ? 'bx-check' : 'bx-time-five'],
        ['title' => 'Berkas', 'description' => $hasDocuments ? 'Berkas wajib sudah tersedia.' : ($hasUploadedDocuments ? 'Berkas lengkap, menunggu validasi panitia.' : $documentMissingLabel), 'tone' => $hasDocuments ? 'success' : ($hasUploadedDocuments ? 'info' : 'warning'), 'icon' => $hasDocuments ? 'bx-check' : 'bx-folder-open'],
        ['title' => 'Pembayaran', 'description' => $paymentVerified ? 'Pembayaran sudah divalidasi.' : ($hasPaymentProof ? 'Bukti pembayaran menunggu verifikasi.' : 'Bukti pembayaran belum diunggah.'), 'tone' => $paymentVerified ? 'success' : ($hasPaymentProof ? 'info' : 'warning'), 'icon' => $paymentVerified ? 'bx-check' : ($hasPaymentProof ? 'bx-time-five' : 'bx-wallet')],
        ['title' => 'Status PMB', 'description' => $pmbStatus->label(), 'tone' => $pmbStatus->color(), 'icon' => $pmbStatus->icon()],
    ];
@endphp

<div class="student-page-shell">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <h4 class="fw-bold mb-1">Status PMB</h4>
            <p class="text-muted mb-0">Pantau progres pendaftaran dan validasi panitia Anda.</p>
        </div>
        @include('dashboard.components.student-status-badge', [
            'label' => $pmbStatus->label(),
            'tone' => $pmbStatus->color(),
            'icon' => $pmbStatus->icon(),
        ])
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="mb-4">Timeline PMB</h5>
            <div class="student-timeline">
                @foreach($timeline as $item)
                    <div class="student-timeline-item">
                        <span class="student-timeline-marker bg-label-{{ $item['tone'] }}">
                            <i class="bx {{ $item['icon'] }}"></i>
                        </span>
                        <div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <h6 class="mb-0">{{ $item['title'] }}</h6>
                                @include('dashboard.components.student-status-badge', [
                                    'label' => $item['description'],
                                    'tone' => $item['tone'],
                                ])
                            </div>
                            <p class="text-muted mb-0">{{ $item['description'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
