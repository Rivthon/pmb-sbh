@php
    $label = $label ?? 'Belum Diproses';
    $tone = $tone ?? 'secondary';
    $icon = $icon ?? null;
@endphp

<span class="student-status-badge badge bg-label-{{ $tone }}">
    @if($icon)
        <i class="bx {{ $icon }} me-1"></i>
    @endif
    {{ $label }}
</span>
