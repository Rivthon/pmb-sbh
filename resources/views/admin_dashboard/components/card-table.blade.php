<div class="card">
    @isset($title)
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0">{{ $title }}</h5>
            @isset($actions)
                <div class="d-flex flex-wrap gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endisset

    @isset($filters)
        <div class="card-body border-bottom">{{ $filters }}</div>
    @endisset

    <div class="table-responsive">
        {{ $slot }}
    </div>

    @isset($pagination)
        <div class="card-footer">{{ $pagination }}</div>
    @endisset
</div>
