@php
    $currentUrl = $currentUrl ?? null;
    $currentFile = $currentFile ?? null;
    $required = $required ?? false;
    $description = $description ?? 'Format JPG, JPEG, atau PNG. Maksimal 2 MB.';
    $missingLabel = 'Belum ' . $label;
@endphp

<div class="student-upload-card">
    <div class="student-upload-card__head">
        <div>
            <h6 class="mb-1">{{ $label }}</h6>
            <p class="text-muted mb-0">{{ $description }}</p>
        </div>
        @include('dashboard.components.student-status-badge', [
            'label' => $currentFile ? 'Sudah Ada' : ($required ? $missingLabel : 'Opsional'),
            'tone' => $currentFile ? 'success' : ($required ? 'warning' : 'info'),
            'icon' => $currentFile ? 'bx-check-circle' : ($required ? 'bx-time-five' : 'bx-info-circle'),
        ])
    </div>

    @if($currentUrl && $currentFile)
        <div class="student-upload-preview">
            <img src="{{ $currentUrl }}" alt="Preview {{ $label }}">
        </div>
        <div class="small text-muted text-break mb-3">{{ $currentFile }}</div>
    @endif

    <input type="file"
        name="{{ $name }}"
        id="{{ $name }}"
        class="form-control @error($name) is-invalid @enderror"
        accept=".jpg,.jpeg,.png,image/jpeg,image/png"
        @if($required && !$currentFile) required @endif>

    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @else
        <div class="form-text">Pilih file baru hanya jika ingin mengunggah atau mengganti dokumen.</div>
    @enderror
</div>
