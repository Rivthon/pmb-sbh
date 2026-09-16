@php
    $missingDocuments = method_exists($mahasiswa, 'missingPmbRequiredDocuments')
        ? array_values($mahasiswa->missingPmbRequiredDocuments())
        : [];
    $documentsComplete = empty($missingDocuments);
    $uploadLabel = $documentsComplete ? 'Berkas Wajib Lengkap' : 'Belum ' . implode(', ', $missingDocuments);
    $uploadTone = $documentsComplete ? 'success' : 'warning';

    $verificationStatus = match ((int) ($mahasiswa->status_berkas ?? 0)) {
        1 => ['label' => 'Terverifikasi', 'tone' => 'success', 'icon' => 'bx-check-shield'],
        2 => ['label' => 'Ditolak', 'tone' => 'danger', 'icon' => 'bx-x-circle'],
        default => ['label' => 'Belum Diverifikasi', 'tone' => 'secondary', 'icon' => 'bx-time-five'],
    };
@endphp

<div class="d-flex flex-column align-items-start gap-1">
    <span class="badge bg-label-{{ $uploadTone }} fw-semibold">
        <i class="bx {{ $documentsComplete ? 'bx-check-circle' : 'bx-error-circle' }} me-1"></i>{{ $uploadLabel }}
    </span>
    <small class="text-muted">
        Admin:
        <span class="text-{{ $verificationStatus['tone'] }} fw-semibold">
            <i class="bx {{ $verificationStatus['icon'] }} me-1"></i>{{ $verificationStatus['label'] }}
        </span>
    </small>
    <small class="text-muted">Ijazah/SKL opsional</small>
</div>
