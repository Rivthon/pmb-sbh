<div class="text-center text-muted py-5">
    <i class="bx {{ $icon ?? 'bx-info-circle' }} display-6 d-block mb-2"></i>
    <div class="fw-semibold">{{ $title ?? 'Data belum tersedia' }}</div>
    @isset($description)
        <div class="small mt-1">{{ $description }}</div>
    @endisset
</div>
