<div class="page-hero animate-fade-in">
    <div class="page-hero-inner">
        <div class="page-hero-content">
            <!-- Breadcrumb -->
            @if(!empty($breadcrumbs))
            <nav class="page-hero-breadcrumb">
                <a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a>
                @foreach($breadcrumbs as $crumb)
                    <span class="separator"><i class="bx bx-chevron-right"></i></span>
                    @if(isset($crumb['url']))
                        <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                    @else
                        <span class="current">{{ $crumb['label'] }}</span>
                    @endif
                @endforeach
            </nav>
            @endif
            <!-- Title -->
            <div class="page-hero-title">
                <span class="hero-icon"><i class="bx bx-{{ $icon ?? 'layer' }}"></i></span>
                <h4>{{ $title }}</h4>
            </div>
            @if(!empty($subtitle))
            <p class="page-hero-subtitle">{{ $subtitle }}</p>
            @endif
        </div>
        @if(isset($actions))
        <div class="page-hero-actions">
            {!! $actions !!}
        </div>
        @endif
    </div>
</div>
