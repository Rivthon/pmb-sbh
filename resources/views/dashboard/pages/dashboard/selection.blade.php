@extends('dashboard.layout.master')
@section('title', 'Seleksi Tes PMB')

@push('head')
<style>
    .selection-shell {
        display: grid;
        gap: 1.5rem;
    }

    .selection-page-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .selection-page-title {
        display: flex;
        align-items: center;
        gap: .85rem;
        min-width: 0;
    }

    .selection-page-icon,
    .selection-card-icon,
    .selection-mini-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .selection-page-icon {
        width: 46px;
        height: 46px;
        border-radius: 8px;
        color: #696cff;
        background: #eef0ff;
        font-size: 1.45rem;
    }

    .selection-card {
        overflow: hidden;
        border: 0;
    }

    .selection-card-hero {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1.25rem;
        padding: 1.4rem;
        border: 1px solid #e5e7f0;
        border-radius: 8px;
        background: linear-gradient(135deg, #f8fbff 0%, #eef9ff 100%);
    }

    .selection-card-title {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        min-width: 0;
    }

    .selection-card-icon {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        color: #00b8d9;
        background: #d7f8ff;
        font-size: 1.5rem;
    }

    .selection-info-grid,
    .selection-progress-grid {
        display: grid;
        gap: .85rem;
    }

    .selection-info-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .selection-progress-grid {
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }

    .selection-info-card,
    .selection-progress-card {
        min-width: 0;
        border: 1px solid #e5e7f0;
        border-radius: 8px;
        background: #fff;
    }

    .selection-info-card {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: .8rem;
        padding: 1rem;
    }

    .selection-progress-card {
        padding: .95rem;
    }

    .selection-mini-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        font-size: 1.1rem;
    }

    .selection-action-row {
        display: flex;
        flex-wrap: wrap;
        gap: .65rem;
    }

    .selection-mobile-actions {
        display: none;
    }

    .selection-action-row .btn,
    .selection-mobile-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        min-height: 40px;
    }

    @media (max-width: 1199.98px) {
        .selection-progress-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .selection-shell {
            padding-bottom: 9.5rem;
        }

        .selection-page-head,
        .selection-card-hero {
            flex-direction: column;
            align-items: stretch;
        }

        .selection-page-icon {
            width: 42px;
            height: 42px;
        }

        .selection-info-grid,
        .selection-progress-grid {
            grid-template-columns: 1fr;
        }

        .selection-card-hero {
            padding: 1rem;
        }

        .selection-action-row {
            display: grid;
            grid-template-columns: 1fr;
        }

        .selection-action-row .btn {
            width: 100%;
            white-space: normal;
        }

        .selection-mobile-actions {
            position: fixed;
            right: .85rem;
            bottom: .85rem;
            left: .85rem;
            z-index: 1080;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .55rem;
            padding: .75rem;
            border: 1px solid rgba(105, 108, 255, .16);
            border-radius: 8px;
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 12px 34px rgba(67, 89, 113, .22);
            backdrop-filter: blur(8px);
        }

        .selection-mobile-actions .btn {
            width: 100%;
            min-height: 42px;
            padding-right: .6rem;
            padding-left: .6rem;
            white-space: nowrap;
        }

        .selection-mobile-actions .btn i {
            font-size: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="student-page-shell selection-shell">
    <div class="selection-page-head">
        <div class="selection-page-title">
            <span class="selection-page-icon">
                <i class="bx bx-check-shield"></i>
            </span>
            <div>
                <h4 class="fw-bold mb-1">Seleksi Tes PMB</h4>
                <p class="text-muted mb-0">Pantau dan lanjutkan tahap seleksi online Anda.</p>
            </div>
        </div>
    </div>

    @if($isOnlineSelection)
        @include('dashboard.pages.dashboard.partials.online-selection-card')
    @else
        <div class="card">
            <div class="card-body text-center p-5">
                <span class="student-status-card__icon bg-label-secondary mx-auto mb-3">
                    <i class="bx bx-check-shield"></i>
                </span>
                <h5 class="fw-bold mb-2">Belum ada sesi seleksi aktif</h5>
                <p class="text-muted mb-0">Tahap seleksi akan muncul setelah panitia memasukkan Anda ke sesi PMB.</p>
            </div>
        </div>
    @endif
</div>
@endsection
