@php
    use App\Support\AdminPermissions;

    $routeActive = fn (array $patterns): bool => collect($patterns)->contains(fn ($pattern) => Route::is($pattern));
    $itemClass = fn (array $patterns): string => $routeActive($patterns) ? ' active' : '';
    $groupClass = fn (array $patterns): string => $routeActive($patterns) ? ' active open' : '';

    $permissions = [
        'dashboard' => ['view dashboard', AdminPermissions::DASHBOARD_VIEW],
        'pmbView' => ['view mahasiswa baru', AdminPermissions::PMB_VIEW],
        'pmbCreate' => ['create mahasiswa baru', AdminPermissions::PMB_CREATE],
        'pmbQueue' => ['view mahasiswa baru', AdminPermissions::PMB_VIEW],
        'tesTulis' => ['view tes tulis', AdminPermissions::MASTER_VIEW],
        'hasilTes' => ['view hasil tes', AdminPermissions::MASTER_VIEW],
        'wawancara' => ['wawancara view', AdminPermissions::WAWANCARA_REVIEW],
        'tesKesehatan' => ['view tes kesehatan', AdminPermissions::KESEHATAN_REVIEW],
        'periode' => ['view periode', AdminPermissions::PERIODE_VIEW],
        'gelombang' => ['view gelombang', AdminPermissions::GELOMBANG_VIEW],
        'jurusan' => ['view jurusan', AdminPermissions::JURUSAN_VIEW],
        'kuesioner' => ['view kuesioner', AdminPermissions::MASTER_VIEW],
        'agama' => ['view agama', AdminPermissions::MASTER_VIEW],
        'pekerjaanAyah' => ['view pekerjaan ayah', AdminPermissions::MASTER_VIEW],
        'pekerjaanIbu' => ['view pekerjaan ibu', AdminPermissions::MASTER_VIEW],
        'penghasilan' => ['view penghasilan', AdminPermissions::MASTER_VIEW],
        'landing' => ['edit landing page', AdminPermissions::LANDING_PAGE_MANAGE],
        'user' => ['view user', AdminPermissions::USER_VIEW],
        'role' => ['view role', AdminPermissions::ROLE_VIEW],
        'permission' => ['view permission', AdminPermissions::ROLE_VIEW],
    ];

    $operasionalRoutes = ['admin.mahasiswa-baru.*', 'admin.pmb-queues.*'];
    $seleksiRoutes = ['admin.tes-tulis.*', 'admin.soal.*', 'admin.hasil-tes.*', 'admin.wawancara.*', 'admin.tes-kesehatan.*'];
    $konfigurasiRoutes = ['admin.periode.*', 'admin.gelombang.*', 'admin.jurusan.*', 'admin.kuesioner.*'];
    $referensiRoutes = ['admin.agama.*', 'admin.pekerjaan-ayah.*', 'admin.pekerjaan-ibu.*', 'admin.penghasilan-orang-tua.*'];
    $kontenRoutes = ['admin.biaya-kuliah.*', 'admin.landing-videos.*', 'admin.hero-features.*', 'admin.landing-info-cards.*', 'admin.landing-media.*'];
    $sistemRoutes = ['admin.users.*', 'admin.roles.*', 'admin.permissions.*'];
    $admin = auth('admin')->user();
    $healthOnlyOfficer = $admin
        && $admin->can(AdminPermissions::PMB_QUEUE_KESEHATAN)
        && !$admin->can(AdminPermissions::PMB_EDIT)
        && !$admin->can(AdminPermissions::PMB_QUEUE_TES_TULIS)
        && !$admin->can(AdminPermissions::PMB_QUEUE_WAWANCARA);
    $healthOfficerSession = $healthOnlyOfficer
        ? \App\Models\PmbTestSession::query()
            ->whereIn('status', [\App\Models\PmbTestSession::STATUS_OPEN, \App\Models\PmbTestSession::STATUS_SCHEDULED])
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [\App\Models\PmbTestSession::STATUS_OPEN])
            ->orderByDesc('starts_at')
            ->first()
        : null;
    $filingOnlyOfficer = $admin?->hasRole(AdminPermissions::ROLE_PETUGAS_PEMBERKASAN);
    $onlineSelectionManager = $admin
        && $admin->can(AdminPermissions::PMB_QUEUE_ONLINE_MANAGE)
        && !$admin->can(AdminPermissions::PMB_EDIT);
    $interviewOnlyOfficer = $admin
        && $admin->can(AdminPermissions::PMB_QUEUE_WAWANCARA)
        && !$admin->can(AdminPermissions::PMB_EDIT)
        && !$admin->can(AdminPermissions::PMB_QUEUE_TES_TULIS)
        && !$admin->can(AdminPermissions::PMB_QUEUE_KESEHATAN);
    $writtenTestOnlyOfficer = $admin
        && $admin->can(AdminPermissions::PMB_QUEUE_TES_TULIS)
        && !$admin->can(AdminPermissions::PMB_EDIT)
        && !$admin->can(AdminPermissions::PMB_QUEUE_KESEHATAN)
        && !$admin->can(AdminPermissions::PMB_QUEUE_WAWANCARA);
    $brandUrl = match (true) {
        $filingOnlyOfficer => route('admin.pmb-queues.scan.index'),
        $onlineSelectionManager => route('admin.pmb-queues.index'),
        $writtenTestOnlyOfficer => route('admin.pmb-queues.written-test.sessions'),
        $interviewOnlyOfficer => route('admin.pmb-queues.interview.offline.sessions'),
        $healthOnlyOfficer && $healthOfficerSession => route('admin.pmb-queues.officer.kesehatan', $healthOfficerSession),
        $healthOnlyOfficer => route('admin.tes-kesehatan.index'),
        default => route('admin.dashboard'),
    };
@endphp

<!-- Sidebar -->
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo admin-sidebar-brand">
        <a href="{{ $brandUrl }}" class="app-brand-link">
            <img src="{{ asset('logo/logo_sbh.png') }}" alt="Logo SBH" />
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1 admin-sidebar-menu">
        @if($filingOnlyOfficer)
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Pemberkasan PMB</span>
        </li>
        <li class="menu-item{{ $itemClass(['admin.mahasiswa-baru.*']) }}">
                <a href="{{ route('admin.pmb-queues.scan.index') }}" class="menu-link">
                <i class="menu-icon bx bx-qr-scan"></i>
                <div>Scan Barcode Kedatangan</div>
                </a>
        </li>
        @elseif($onlineSelectionManager)
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Seleksi Online PMB</span>
        </li>
        <li class="menu-item{{ $itemClass(['admin.pmb-queues.index', 'admin.pmb-queues.show', 'admin.pmb-queues.edit']) }}">
            <a href="{{ route('admin.pmb-queues.index') }}" class="menu-link">
                <i class="menu-icon bx bx-video"></i>
                <div>Sesi Seleksi Online</div>
            </a>
        </li>
        <li class="menu-item{{ $itemClass(['admin.tes-tulis.*', 'admin.soal.*']) }}">
            <a href="{{ route('admin.tes-tulis.index') }}" class="menu-link">
                <i class="menu-icon bx bx-pencil"></i>
                <div>Tes Tulis & Soal Online</div>
            </a>
        </li>
        <li class="menu-item{{ $itemClass(['admin.hasil-tes.*']) }}">
            <a href="{{ route('admin.hasil-tes.index') }}" class="menu-link">
                <i class="menu-icon bx bx-bar-chart-alt-2"></i>
                <div>Hasil Tes Tulis Online</div>
            </a>
        </li>
        <li class="menu-item{{ $itemClass(['admin.tes-kesehatan.*']) }}">
            <a href="{{ route('admin.tes-kesehatan.index') }}" class="menu-link">
                <i class="menu-icon bx bx-health"></i>
                <div>Kesehatan Online</div>
            </a>
        </li>
        <li class="menu-item{{ $itemClass(['admin.wawancara.*']) }}">
            <a href="{{ route('admin.wawancara.index') }}" class="menu-link">
                <i class="menu-icon bx bx-conversation"></i>
                <div>Wawancara Online</div>
            </a>
        </li>
        @elseif($writtenTestOnlyOfficer)
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Tes Tulis PMB</span>
        </li>
        <li class="menu-item{{ $itemClass(['admin.pmb-queues.written-test.sessions', 'admin.pmb-queues.officer.tes-tulis']) }}">
            <a href="{{ route('admin.pmb-queues.written-test.sessions') }}" class="menu-link">
                <i class="menu-icon bx bx-pencil"></i>
                <div>Sesi Tes Tulis</div>
            </a>
        </li>
        @elseif($healthOnlyOfficer)
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Tes Kesehatan</span>
        </li>
        <li class="menu-item{{ $itemClass(['admin.pmb-queues.officer.kesehatan']) }}">
            <a href="{{ $healthOfficerSession ? route('admin.pmb-queues.officer.kesehatan', $healthOfficerSession) : route('admin.tes-kesehatan.index') }}" class="menu-link">
                <i class="menu-icon bx bx-health"></i>
                <div>Tes Kesehatan Offline</div>
            </a>
        </li>
        <li class="menu-item{{ $itemClass(['admin.tes-kesehatan.*']) }}">
            <a href="{{ route('admin.tes-kesehatan.index') }}" class="menu-link">
                <i class="menu-icon bx bx-file"></i>
                <div>Laporan Kesehatan</div>
            </a>
        </li>
        @elseif($interviewOnlyOfficer)
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Wawancara PMB</span>
        </li>
        <li class="menu-item{{ $itemClass(['admin.pmb-queues.interview.offline.sessions', 'admin.pmb-queues.officer.wawancara']) }}">
            <a href="{{ route('admin.pmb-queues.interview.offline.sessions') }}" class="menu-link">
                <i class="menu-icon bx bx-conversation"></i>
                <div>Wawancara Offline</div>
            </a>
        </li>
        <li class="menu-item{{ $itemClass(['admin.pmb-queues.interview.online.sessions', 'admin.pmb-queues.officer.wawancara-online']) }}">
            <a href="{{ route('admin.pmb-queues.interview.online.sessions') }}" class="menu-link">
                <i class="menu-icon bx bx-video"></i>
                <div>Wawancara Online</div>
            </a>
        </li>
        @else
        @canany($permissions['dashboard'])
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Beranda</span>
        </li>
        <li class="menu-item{{ $itemClass(['admin.dashboard']) }}">
            <a href="{{ route('admin.dashboard') }}" class="menu-link">
                <i class="menu-icon bx bx-home-alt"></i>
                <div>Dashboard</div>
            </a>
        </li>
        @endcanany

        @canany(array_merge($permissions['pmbView'], $permissions['pmbCreate']))
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Operasional PMB</span>
        </li>
        <li class="menu-item{{ $groupClass($operasionalRoutes) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bx-user-plus"></i>
                <div>Pendaftar PMB</div>
            </a>
            <ul class="menu-sub">
                @canany($permissions['pmbView'])
                <li class="menu-item{{ $itemClass(['admin.mahasiswa-baru.index', 'admin.mahasiswa-baru.show', 'admin.mahasiswa-baru.detail', 'admin.mahasiswa-baru.edit']) }}">
                    <a href="{{ route('admin.mahasiswa-baru.index') }}" class="menu-link">
                        <i class="bx bx-list-ul me-2"></i>
                        <div>Daftar Calon Mahasiswa</div>
                    </a>
                </li>
                @endcanany

                @canany($permissions['pmbQueue'])
                <li class="menu-item{{ $itemClass(['admin.pmb-queues.index', 'admin.pmb-queues.show', 'admin.pmb-queues.board', 'admin.pmb-queues.create', 'admin.pmb-queues.edit']) }}">
                    <a href="{{ route('admin.pmb-queues.index') }}" class="menu-link">
                        <i class="bx bx-qr-scan me-2"></i>
                        <div>Sesi Seleksi PMB</div>
                    </a>
                </li>
                <li class="menu-item{{ $itemClass(['admin.pmb-queues.participants.add']) }}">
                    <a href="{{ route('admin.pmb-queues.participants.add') }}" class="menu-link">
                        <i class="bx bx-table me-2"></i>
                        <div>Tambah Peserta Antrian</div>
                    </a>
                </li>
                @endcanany

                @canany($permissions['pmbCreate'])
                <li class="menu-item{{ $itemClass(['admin.mahasiswa-baru.create']) }}">
                    <a href="{{ route('admin.mahasiswa-baru.create') }}" class="menu-link">
                        <i class="bx bx-user-plus me-2"></i>
                        <div>Tambah Calon Mahasiswa</div>
                    </a>
                </li>
                @endcanany
            </ul>
        </li>
        @endcanany

        @canany(array_merge($permissions['tesTulis'], $permissions['hasilTes'], $permissions['wawancara'], $permissions['tesKesehatan']))
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Seleksi & Verifikasi</span>
        </li>
        <li class="menu-item{{ $groupClass($seleksiRoutes) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bx-check-shield"></i>
                <div>Alur Seleksi</div>
            </a>
            <ul class="menu-sub">
                @canany($permissions['tesTulis'])
                <li class="menu-item{{ $itemClass(['admin.tes-tulis.*', 'admin.soal.*']) }}">
                    <a href="{{ route('admin.tes-tulis.index') }}" class="menu-link">
                        <i class="bx bx-pencil me-2"></i>
                        <div>Tes Tulis</div>
                    </a>
                </li>
                @endcanany

                @canany($permissions['hasilTes'])
                <li class="menu-item{{ $itemClass(['admin.hasil-tes.*']) }}">
                    <a href="{{ route('admin.hasil-tes.index') }}" class="menu-link">
                        <i class="bx bx-bar-chart-alt-2 me-2"></i>
                        <div>Hasil Tes</div>
                    </a>
                </li>
                @endcanany

                @canany($permissions['wawancara'])
                <li class="menu-item{{ $itemClass(['admin.wawancara.*']) }}">
                    <a href="{{ route('admin.wawancara.index') }}" class="menu-link">
                        <i class="bx bx-conversation me-2"></i>
                        <div>Wawancara PMB</div>
                    </a>
                </li>
                @endcanany

                @canany($permissions['tesKesehatan'])
                <li class="menu-item{{ $itemClass(['admin.tes-kesehatan.*']) }}">
                    <a href="{{ route('admin.tes-kesehatan.index') }}" class="menu-link">
                        <i class="bx bx-file me-2"></i>
                        <div>Laporan Kesehatan</div>
                    </a>
                </li>
                @endcanany
            </ul>
        </li>
        @endcanany

        @canany(array_merge($permissions['periode'], $permissions['gelombang'], $permissions['jurusan'], $permissions['kuesioner']))
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Konfigurasi PMB</span>
        </li>
        <li class="menu-item{{ $groupClass($konfigurasiRoutes) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bx-slider-alt"></i>
                <div>Setup PMB</div>
            </a>
            <ul class="menu-sub">
                @canany($permissions['periode'])
                <li class="menu-item{{ $itemClass(['admin.periode.*']) }}">
                    <a href="{{ route('admin.periode.index') }}" class="menu-link">
                        <i class="bx bx-calendar-event me-2"></i>
                        <div>Periode</div>
                    </a>
                </li>
                @endcanany

                @canany($permissions['gelombang'])
                <li class="menu-item{{ $itemClass(['admin.gelombang.*']) }}">
                    <a href="{{ route('admin.gelombang.index') }}" class="menu-link">
                        <i class="bx bx-layer me-2"></i>
                        <div>Gelombang</div>
                    </a>
                </li>
                @endcanany

                @canany($permissions['jurusan'])
                <li class="menu-item{{ $itemClass(['admin.jurusan.*']) }}">
                    <a href="{{ route('admin.jurusan.index') }}" class="menu-link">
                        <i class="bx bx-bookmark me-2"></i>
                        <div>Program Studi</div>
                    </a>
                </li>
                @endcanany

                @canany($permissions['kuesioner'])
                <li class="menu-item{{ $itemClass(['admin.kuesioner.*']) }}">
                    <a href="{{ route('admin.kuesioner.index') }}" class="menu-link">
                        <i class="bx bx-edit-alt me-2"></i>
                        <div>Kuesioner</div>
                    </a>
                </li>
                @endcanany
            </ul>
        </li>
        @endcanany

        @canany(array_merge($permissions['agama'], $permissions['pekerjaanAyah'], $permissions['pekerjaanIbu'], $permissions['penghasilan']))
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Referensi Biodata</span>
        </li>
        <li class="menu-item{{ $groupClass($referensiRoutes) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bx-id-card"></i>
                <div>Data Referensi</div>
            </a>
            <ul class="menu-sub">
                @canany($permissions['agama'])
                <li class="menu-item{{ $itemClass(['admin.agama.*']) }}">
                    <a href="{{ route('admin.agama.index') }}" class="menu-link">
                        <i class="bx bx-church me-2"></i>
                        <div>Agama</div>
                    </a>
                </li>
                @endcanany

                @canany($permissions['pekerjaanAyah'])
                <li class="menu-item{{ $itemClass(['admin.pekerjaan-ayah.*']) }}">
                    <a href="{{ route('admin.pekerjaan-ayah.index') }}" class="menu-link">
                        <i class="bx bx-male me-2"></i>
                        <div>Pekerjaan Ayah</div>
                    </a>
                </li>
                @endcanany

                @canany($permissions['pekerjaanIbu'])
                <li class="menu-item{{ $itemClass(['admin.pekerjaan-ibu.*']) }}">
                    <a href="{{ route('admin.pekerjaan-ibu.index') }}" class="menu-link">
                        <i class="bx bx-female me-2"></i>
                        <div>Pekerjaan Ibu</div>
                    </a>
                </li>
                @endcanany

                @canany($permissions['penghasilan'])
                <li class="menu-item{{ $itemClass(['admin.penghasilan-orang-tua.*']) }}">
                    <a href="{{ route('admin.penghasilan-orang-tua.index') }}" class="menu-link">
                        <i class="bx bx-wallet me-2"></i>
                        <div>Penghasilan Orang Tua</div>
                    </a>
                </li>
                @endcanany
            </ul>
        </li>
        @endcanany

        @canany($permissions['landing'])
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Konten Publik</span>
        </li>
        <li class="menu-item{{ $groupClass($kontenRoutes) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bx-news"></i>
                <div>Landing Page</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item{{ $itemClass(['admin.hero-features.*']) }}">
                    <a href="{{ route('admin.hero-features.index') }}" class="menu-link">
                        <i class="bx bx-grid-alt me-2"></i>
                        <div>Keunggulan Hero</div>
                    </a>
                </li>
                <li class="menu-item{{ $itemClass(['admin.landing-info-cards.*']) }}">
                    <a href="{{ route('admin.landing-info-cards.index') }}" class="menu-link">
                        <i class="bx bx-detail me-2"></i>
                        <div>Konten Informasi</div>
                    </a>
                </li>
                <li class="menu-item{{ $itemClass(['admin.landing-media.*']) }}">
                    <a href="{{ route('admin.landing-media.index') }}" class="menu-link">
                        <i class="bx bx-image me-2"></i>
                        <div>Gambar Utama</div>
                    </a>
                </li>
                <li class="menu-item{{ $itemClass(['admin.biaya-kuliah.*']) }}">
                    <a href="{{ route('admin.biaya-kuliah.index') }}" class="menu-link">
                        <i class="bx bx-money me-2"></i>
                        <div>Biaya Kuliah</div>
                    </a>
                </li>
                <li class="menu-item{{ $itemClass(['admin.landing-videos.*']) }}">
                    <a href="{{ route('admin.landing-videos.index') }}" class="menu-link">
                        <i class="bx bxl-youtube me-2"></i>
                        <div>Video Shorts</div>
                    </a>
                </li>
            </ul>
        </li>
        @endcanany

        @canany(array_merge($permissions['user'], $permissions['role'], $permissions['permission']))
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Administrasi Sistem</span>
        </li>
        <li class="menu-item{{ $groupClass($sistemRoutes) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bxs-user-account"></i>
                <div>Akses Admin</div>
            </a>
            <ul class="menu-sub">
                @canany($permissions['user'])
                <li class="menu-item{{ $itemClass(['admin.users.*']) }}">
                    <a href="{{ route('admin.users.index') }}" class="menu-link">
                        <i class="bx bx-user me-2"></i>
                        <div>Admin / User</div>
                    </a>
                </li>
                @endcanany

                @canany($permissions['role'])
                <li class="menu-item{{ $itemClass(['admin.roles.*']) }}">
                    <a href="{{ route('admin.roles.index') }}" class="menu-link">
                        <i class="bx bx-shield-quarter me-2"></i>
                        <div>Role</div>
                    </a>
                </li>
                @endcanany

                @canany($permissions['permission'])
                <li class="menu-item{{ $itemClass(['admin.permissions.*']) }}">
                    <a href="{{ route('admin.permissions.index') }}" class="menu-link">
                        <i class="bx bx-key me-2"></i>
                        <div>Permission</div>
                    </a>
                </li>
                @endcanany
            </ul>
        </li>
        @endcanany
        @endif

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Akun</span>
        </li>
        <li class="menu-item">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="menu-link border-0 bg-transparent w-100 text-start">
                    <i class="menu-icon bx bx-power-off text-danger"></i>
                    <div>Logout</div>
                </button>
            </form>
        </li>
    </ul>
</aside>
<!-- /Sidebar -->
