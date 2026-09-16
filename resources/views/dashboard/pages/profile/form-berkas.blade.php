@extends('dashboard.layout.master')
@section('title', 'Berkas PMB')

@section('content')
@php
    $requiredDocs = collect($user::pmbRequiredDocuments())
        ->map(fn ($label, $attribute) => [
            'label' => $label,
            'file' => $user->{$attribute},
            'url' => $user->pmbDocumentUrl($attribute),
        ]);
    $optionalDocs = collect($user::pmbOptionalDocuments())
        ->map(fn ($label, $attribute) => [
            'label' => $label,
            'file' => $user->{$attribute},
            'url' => $user->pmbDocumentUrl($attribute),
        ]);
    $missingDocs = $user->missingPmbRequiredDocuments();
    $completeDocs = empty($missingDocs);
    $documentStatusLabel = $user->missingPmbRequiredDocumentsLabel();
@endphp

<div class="student-page-shell">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <h4 class="fw-bold mb-1">Upload Berkas PMB</h4>
            <p class="text-muted mb-0">Unggah dokumen wajib dengan format JPG/JPEG/PNG maksimal 2 MB per file.</p>
        </div>
        @include('dashboard.components.student-status-badge', [
            'label' => $documentStatusLabel,
            'tone' => $completeDocs ? 'success' : 'warning',
            'icon' => $completeDocs ? 'bx-check-circle' : 'bx-time-five',
        ])
    </div>

    @if ($errors->any())
        <div class="alert alert-danger d-flex gap-2" role="alert">
            <i class="bx bx-error-circle fs-4"></i>
            <div>
                <strong>Upload belum bisa diproses.</strong>
                <div>{{ $documentStatusLabel }}. Periksa juga format dan ukuran file yang ditandai.</div>
            </div>
        </div>
    @endif

    <form action="{{ route('dashboard.profile.berkasUpdate') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-4">
            @foreach($requiredDocs as $name => $doc)
                <div class="col-lg-4">
                    @include('dashboard.components.student-upload-card', [
                        'name' => $name,
                        'label' => $doc['label'],
                        'currentUrl' => $doc['url'],
                        'currentFile' => $doc['file'],
                        'required' => true,
                    ])
                </div>
            @endforeach

            @foreach($optionalDocs as $name => $doc)
                <div class="col-lg-4">
                    @include('dashboard.components.student-upload-card', [
                        'name' => $name,
                        'label' => $doc['label'],
                        'currentUrl' => $doc['url'],
                        'currentFile' => $doc['file'],
                        'required' => false,
                        'description' => 'Opsional. Format JPG, JPEG, atau PNG. Maksimal 2 MB.',
                    ])
                </div>
            @endforeach
        </div>

        <div class="student-cta-card mt-4">
            <div>
                <span class="badge bg-label-info mb-2">Validasi Panitia</span>
                <h5 class="mb-1">Kirim Berkas untuk Diverifikasi</h5>
                <p class="text-muted mb-0">
                    {{ $completeDocs ? 'Semua dokumen wajib sudah tersedia dan siap divalidasi panitia.' : $documentStatusLabel . ' sebelum berkas masuk antrean validasi.' }}
                </p>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bx bx-upload me-1"></i> Simpan Berkas
            </button>
        </div>
    </form>
</div>
@endsection
