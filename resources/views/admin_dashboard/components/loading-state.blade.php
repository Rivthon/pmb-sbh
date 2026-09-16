<div id="{{ $id ?? 'page-loader' }}" class="text-center py-4" style="{{ !empty($hidden) ? 'display:none;' : '' }}">
    <span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></span>
    <span class="ms-2 text-muted">{{ $label ?? 'Memuat data...' }}</span>
</div>
