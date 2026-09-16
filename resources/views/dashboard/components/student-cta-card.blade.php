@php
    $description = $description ?? null;
    $href = $href ?? '#';
    $button = $button ?? 'Lanjutkan';
    $icon = $icon ?? 'bx-right-arrow-alt';
    $tone = $tone ?? 'primary';
@endphp

<div class="student-cta-card">
    <div>
        <span class="badge bg-label-{{ $tone }} mb-2">Langkah Berikutnya</span>
        <h5 class="mb-1">{{ $title }}</h5>
        @if($description)
            <p class="text-muted mb-0">{{ $description }}</p>
        @endif
    </div>
    <a href="{{ $href }}" class="btn btn-{{ $tone }}">
        {{ $button }}
        <i class="bx {{ $icon }} ms-1"></i>
    </a>
</div>
