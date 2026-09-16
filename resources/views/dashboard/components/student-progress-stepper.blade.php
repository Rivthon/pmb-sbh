@php
    $steps = $steps ?? [];
@endphp

<div class="student-progress-stepper">
    @foreach($steps as $step)
        @php
            $state = $step['state'] ?? 'pending';
            $icon = match ($state) {
                'complete' => 'bx-check',
                'active' => $step['icon'] ?? 'bx-time-five',
                'danger' => 'bx-x',
                default => $step['icon'] ?? 'bx-circle',
            };
        @endphp
        <div class="student-progress-step is-{{ $state }}">
            <div class="student-progress-icon">
                <i class="bx {{ $icon }}"></i>
            </div>
            <div>
                <div class="student-progress-label">{{ $step['label'] }}</div>
                <div class="student-progress-caption">{{ $step['caption'] ?? '' }}</div>
            </div>
        </div>
    @endforeach
</div>
