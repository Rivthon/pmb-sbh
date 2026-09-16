@php
$status = \App\Enums\PmbStatus::fromValue($value ?? null);
$colorMap = [
    'success' => ['class' => 'status-lulus', 'icon' => 'bx-check-double'],
    'danger' => ['class' => 'status-ditolak', 'icon' => 'bx-block'],
    'warning' => ['class' => 'status-pending', 'icon' => 'bx-time-five'],
    'info' => ['class' => 'status-review', 'icon' => 'bx-search'],
    'primary' => ['class' => 'status-verified', 'icon' => 'bx-badge-check'],
    'secondary' => ['class' => 'status-draft', 'icon' => 'bx-edit'],
];
$color = $status ? $status->color() : 'secondary';
$info = $colorMap[$color] ?? ['class' => 'status-draft', 'icon' => 'bx-info-circle'];
@endphp

@if($status)
<span class="status-badge {{ $info['class'] }}">
    <i class="bx {{ $info['icon'] }}"></i>
    {{ $status->label() }}
</span>
@else
<span class="status-badge status-draft">
    <i class="bx bx-info-circle"></i>
    -
</span>
@endif
