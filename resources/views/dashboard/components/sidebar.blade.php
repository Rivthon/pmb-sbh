<!-- Menu -->
@php
    $sidebarUser = auth()->user();
    $missingDocuments = $sidebarUser ? array_values($sidebarUser->missingPmbRequiredDocuments()) : [];
    $documentBadgeLabel = empty($missingDocuments)
        ? 'Lengkap'
        : 'Belum ' . $missingDocuments[0] . (count($missingDocuments) > 1 ? ' +' . (count($missingDocuments) - 1) : '');
    $showTesTulisMenu = false;
    $showOnlineHealthMenu = false;
    $showOnlineInterviewMenu = false;
    $showOnlineSelectionSummary = false;
    $latestSelectionQueue = null;

    if ($sidebarUser) {
        $latestSelectionQueue = \App\Models\PmbOfflineQueue::with('session.tesTulis')
            ->where('user_id', $sidebarUser->id)->latest()->latest('id')->first();
        $onlineQueue = $latestSelectionQueue?->isOnlineSelection() ? $latestSelectionQueue : null;
        $showOnlineSelectionSummary = (bool) $onlineQueue;
        $showTesTulisMenu = $onlineQueue
            && $onlineQueue->requiresTesTulis()
            && $onlineQueue->session?->tes_tulis_id
            && (bool) $onlineQueue->session?->tesTulis?->status_aktif;

        $showOnlineHealthMenu = (bool) $onlineQueue;
        $showOnlineInterviewMenu = $showOnlineHealthMenu;
    }
@endphp
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('home') }}" class="app-brand-link">
            <img src="{{ asset('logo/logo_sbh.png') }}" alt="Logo" style="width: 200px; height: auto;" />
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">

        {{-- DASHBOARD --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Dashboard</span>
        </li>

        <li class="menu-item @if(Route::is('dashboard.index')) active @endif">
            <a href="{{ route('dashboard.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-alt-2"></i>
                <div>Dashboard</div>
            </a>
        </li>

        <li class="menu-item @if(Route::is('dashboard.profile.cetakKartu*')) active @endif">
            <a href="{{ route('dashboard.profile.cetakKartu') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-badge-check"></i>
                <div>Status PMB</div>
            </a>
        </li>

        {{-- ALUR PMB --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Alur PMB</span>
        </li>

        <li class="menu-item @if(Route::is('dashboard.profile.index*') || Route::is('dashboard.profile.editBerkas*') || Route::is('dashboard.profile.updatePayment*')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-list-check"></i>
                <div>Pendaftaran</div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item @if(Route::is('dashboard.profile.index*')) active @endif">
                    <a href="{{ route('dashboard.profile.index') }}" class="menu-link">
                        <i class="bx bx-id-card me-2"></i>
                        <div>Biodata PMB</div>
                    </a>
                </li>

                <li class="menu-item @if(Route::is('dashboard.profile.editBerkas*')) active @endif">
                    <a href="{{ route('dashboard.profile.editBerkas') }}" class="menu-link">
                        <i class="bx bx-folder-open me-2"></i>
                        <div>Berkas PMB</div>
                        <span class="badge bg-label-{{ empty($missingDocuments) ? 'success' : 'warning' }} ms-auto px-2 py-1" style="font-size: 0.65rem;">
                            {{ $documentBadgeLabel }}
                        </span>
                    </a>
                </li>

                <li class="menu-item @if(Route::is('dashboard.profile.updatePayment*')) active @endif">
                    <a href="{{ route('dashboard.profile.updatePayment') }}" class="menu-link">
                        <i class="bx bx-wallet me-2"></i>
                        <div>Pembayaran PMB</div>
                    </a>
                </li>
            </ul>
        </li>

        {{-- SELEKSI TES PMB --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Seleksi Tes PMB</span>
        </li>

        <li class="menu-item @if(Route::is('dashboard.selection.*') || Route::is('dashboard.user.*') || Route::is('dashboard.tes-tulis.*') || Route::is('dashboard.tes-kesehatan.*') || Route::is('dashboard.wawancara.*') || Route::is('dashboard.pmb-queue.*')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-check-shield"></i>
                <div>Seleksi Tes PMB</div>
            </a>

            <ul class="menu-sub">
                @if($showOnlineSelectionSummary)
                <li class="menu-item @if(Route::is('dashboard.selection.*')) active @endif">
                    <a href="{{ route('dashboard.selection.index') }}" class="menu-link">
                        <i class="bx bx-dashboard me-2"></i>
                        <div>Ringkasan Seleksi</div>
                    </a>
                </li>
                @endif

                @if($showTesTulisMenu)
                <li class="menu-item @if(Route::is('dashboard.user.tes-tulis.index*')) active @endif">
                    <a href="{{ route('dashboard.user.tes-tulis.index') }}" class="menu-link">
                        <i class="bx bx-edit-alt me-2"></i>
                        <div>Tes Tulis</div>
                    </a>
                </li>
                @endif

                @if($showOnlineHealthMenu)
                <li class="menu-item @if(Route::is('dashboard.tes-kesehatan.*')) active @endif">
                    <a href="{{ route('dashboard.tes-kesehatan.anamnesa.create') }}" class="menu-link">
                        <i class="bx bx-plus-medical me-2"></i>
                        <div>Kesehatan Online</div>
                    </a>
                </li>
                @endif

                @if($showOnlineInterviewMenu)
                <li class="menu-item @if(Route::is('dashboard.wawancara.*')) active @endif">
                    <a href="{{ route('dashboard.wawancara.index') }}" class="menu-link">
                        <i class="bx bx-conversation me-2"></i>
                        <div>Form Wawancara</div>
                    </a>
                </li>
                @endif

                @if(!$latestSelectionQueue?->isOnlineSelection())
                <li class="menu-item @if(Route::is('dashboard.pmb-queue.*')) active @endif">
                    <a href="{{ route('dashboard.pmb-queue.index') }}" class="menu-link">
                        <i class="bx bx-qr-scan me-2"></i>
                        <div>Antrian Tes</div>
                    </a>
                </li>
                @endif

            </ul>
        </li>

        {{-- PENGATURAN --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Pengaturan</span>
        </li>

        <li class="menu-item">
            <form method="POST" action="{{ route('auth.logout') }}">
                @csrf
                <button type="submit" class="menu-link border-0 bg-transparent w-100 text-start">
                    <i class="menu-icon tf-icons bx bx-log-out-circle"></i>
                    <div>Log Out</div>
                </button>
            </form>
        </li>

    </ul>
</aside>
<!-- / Menu -->
