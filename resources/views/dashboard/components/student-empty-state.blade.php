@php
    $description = $description ?? null;
    $icon = $icon ?? 'bx-info-circle';
@endphp

<div class="student-empty-state">
    <i class="bx {{ $icon }}"></i>
    <h5>{{ $title }}</h5>
    @if($description)
        <p>{{ $description }}</p>
    @endif
</div>
