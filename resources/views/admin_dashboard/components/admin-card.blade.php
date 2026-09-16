<div class="card admin-card">
    @if($title ?? false)
    <div class="card-header">
        <div>
            <h5 class="card-title">
                @if($icon ?? false)<i class="{{ $icon }}"></i>@endif
                {{ $title }}
            </h5>
            @if($subtitle ?? false)
            <small class="card-subtitle">{{ $subtitle }}</small>
            @endif
        </div>
        @if(isset($headerActions))
        <div class="d-flex align-items-center gap-2">
            {!! $headerActions !!}
        </div>
        @endif
    </div>
    @endif
    <div class="card-body {{ ($noPadding ?? false) ? 'no-padding' : '' }}">
        {{ $slot }}
    </div>
    @if(isset($footer))
    <div class="card-footer">
        {!! $footer !!}
    </div>
    @endif
</div>
