<!DOCTYPE html>

<html lang="en" class="light-style layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('dashboard_assets/assets/') }}" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} - @yield('title')</title>

    <meta name="description" content="PMB SBH- Penerimaan Mahasiswa Baru">

    <!-- Google / Search Engine Tags -->
    <meta itemprop="name" content="PMB SBH">
    <meta itemprop="description" content="PMB SBH - Penerimaan Mahasiswa Baru">

    <!-- Facebook Meta Tags -->
    <meta property="og:url" content="https://pmb.sbh.ac.id">
    <meta property="og:type" content="website">
    <meta property="og:title" content="PMB SBH">
    <meta property="og:description" content="PMB SBH - Penerimaan Mahasiswa Baru">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="PMB SBH">
    <meta name="twitter:description" content="PMB SBH - Penerimaan Mahasiswa Baru">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('dashboard_assets/assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    @php
        $adminUiCss = 'dashboard_assets/assets/css/admin-ui.css';
        $adminUiVersion = file_exists(public_path($adminUiCss)) ? filemtime(public_path($adminUiCss)) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/css/core.css') }}"
        class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/css/theme-default.css') }}"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/css/custom.css') }}" />
    <link rel="stylesheet" href="{{ asset($adminUiCss) }}?v={{ $adminUiVersion }}" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/css/pages/page-profile.css') }}">

    <!-- Vendors CSS -->
    <link rel="stylesheet"
        href="{{ asset('dashboard_assets/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/libs/apex-charts/apex-charts.css') }}" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/libs/iziToast/css/iziToast.min.css') }}">

    {{-- Datatables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

    {{-- Select2 CSS --}}
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/libs/select2/css/select2.min.css') }}">

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="{{ asset('dashboard_assets/assets/vendor/js/helpers.js') }}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('dashboard_assets/assets/js/config.js') }}"></script>

    @stack('head')
</head>

<body>
    <div class="pre-loader is-load">
        <div class="circle-loader"></div>
    </div>

    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            @include('admin_dashboard.components.sidebar')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">

                <!-- Navbar -->
                @include('admin_dashboard.components.navbar')
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->

                    <div class="container-xxl flex-grow-1 container-p-y">
                        @yield('content')
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    @include('admin_dashboard.components.footer')
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->

    <script src="{{ asset('dashboard_assets/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/js/menu.js') }}"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/iziToast/js/iziToast.min.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/chartjs/chartjs.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/chartjs/charts-chartjs.js') }}"></script>

    {{-- Select2 JS --}}
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/select2/js/select2.full.min.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('dashboard_assets/assets/js/main.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/js/custom.js') }}"></script>

    <!-- Page JS -->
    <script src="{{ asset('dashboard_assets/assets/js/dashboards-analytics.js') }}"></script>

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>

    @include('admin_dashboard.components.notification')

    <script>
        window.addEventListener('load', function () {
            const loader = document.querySelector('.pre-loader');
            if (!loader) return;

            loader.classList.remove('is-load');
            loader.style.display = 'none';
        });

        setTimeout(function () {
            const loader = document.querySelector('.pre-loader.is-load');
            if (!loader) return;

            loader.classList.remove('is-load');
            loader.style.display = 'none';
        }, 2500);
    </script>

    @stack('script')

</body>

</html>
