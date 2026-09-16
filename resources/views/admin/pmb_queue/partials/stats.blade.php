<div class="row g-2 mb-3 pmb-queue-stats-compact">
    @php
        $items = [
            ['label' => 'Peserta', 'value' => $stats['total'], 'icon' => 'bx-group', 'tone' => 'primary'],
            ['label' => 'Hadir', 'value' => $stats['checked_in'], 'icon' => 'bx-qr-scan', 'tone' => 'info'],
            ['label' => 'Menunggu', 'value' => $stats['waiting'], 'icon' => 'bx-time-five', 'tone' => 'warning'],
            ['label' => 'Dipanggil', 'value' => $stats['called'], 'icon' => 'bx-volume-full', 'tone' => 'dark'],
            ['label' => 'Di Ruangan', 'value' => $stats['in_room'], 'icon' => 'bx-door-open', 'tone' => 'secondary'],
            ['label' => 'Selesai', 'value' => $stats['completed'], 'icon' => 'bx-check-circle', 'tone' => 'success'],
        ];
    @endphp
    @foreach($items as $item)
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card pmb-queue-stat h-100">
                <div class="card-body d-flex align-items-center gap-2">
                    <span class="badge bg-label-{{ $item['tone'] }} p-2"><i class="bx {{ $item['icon'] }}"></i></span>
                    <div>
                        <div class="fw-bold lh-1">{{ $item['value'] }}</div>
                        <div class="text-muted small">{{ $item['label'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@if(isset($stageBoard) && $stageBoard->isNotEmpty())
    <div class="row g-2 mb-3 pmb-stage-stats-compact">
        @foreach($stageBoard as $stage)
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="fw-semibold small mb-2">{{ $stage['name'] }}</div>
                        <div class="d-flex flex-wrap gap-1 small">
                            <span class="badge bg-label-warning">{{ $stage['counts']['waiting'] ?? 0 }} menunggu</span>
                            <span class="badge bg-label-dark">{{ ($stage['counts']['called'] ?? 0) + ($stage['counts']['processing'] ?? 0) }} proses</span>
                            <span class="badge bg-label-success">{{ $stage['counts']['completed'] ?? 0 }} selesai</span>
                            @if(($stage['counts']['held'] ?? 0) > 0 || ($stage['counts']['problem'] ?? 0) > 0)
                                <span class="badge bg-label-danger">{{ ($stage['counts']['held'] ?? 0) + ($stage['counts']['problem'] ?? 0) }} bermasalah</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
