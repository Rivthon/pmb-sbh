<!DOCTYPE html>
<html lang="en" class="light-style layout-navbar-fixed layout-wide " dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('dashboard_assets/assets/') }}" data-template="front-pages">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>PMB SBH | Penerimaan Mahasiswa Baru STIKes Bogor Husada</title>
    <meta name="description"
        content="Daftar kuliah di kampus kesehatan terbaik di Kota Bogor, STIKes Bogor Husada, yang bernaung langsung di bawah Rumah Sakit Azra. Dapatkan Laptop Gratis untuk semua pendaftar!">

    <!-- Canonical SEO -->
    <link rel="canonical" href="https://pmb.sbh.ac.id" />

    <!-- Google / Search Engine Tags -->
    <meta itemprop="name" content="PMB SBH | Penerimaan Mahasiswa Baru STIKes Bogor Husada">
    <meta itemprop="description"
        content="Daftar kuliah di kampus kesehatan terbaik di Kota Bogor, STIKes Bogor Husada, yang bernaung langsung di bawah Rumah Sakit Azra. Dapatkan Laptop Gratis untuk semua pendaftar!">
    <meta itemprop="image"
        content="https://pmb.sbh.ac.id/dashboard_assets/assets/img/front-pages/landing-page/faq-foto.png">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://pmb.sbh.ac.id">
    <meta name="twitter:title" content="PMB SBH | Penerimaan Mahasiswa Baru STIKes Bogor Husada">
    <meta name="twitter:description"
        content="Daftar kuliah di kampus kesehatan terbaik di Kota Bogor, STIKes Bogor Husada, yang bernaung langsung di bawah Rumah Sakit Azra. Dapatkan Laptop Gratis untuk semua pendaftar!">
    <meta name="twitter:image"
        content="https://pmb.sbh.ac.id/dashboard_assets/assets/img/front-pages/landing-page/faq-foto.png">

    <!-- Open Graph Meta Tags -->
    <meta property="og:locale" content="id_ID" />
    <meta property="og:site_name" content="PMB SBH - STIKes Bogor Husada" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="PMB SBH | Penerimaan Mahasiswa Baru STIKes Bogor Husada" />
    <meta property="og:description"
        content="Daftar kuliah di kampus kesehatan terbaik di Kota Bogor, STIKes Bogor Husada, yang bernaung langsung di bawah Rumah Sakit Azra. Dapatkan Laptop Gratis untuk semua pendaftar!" />
    <meta property="og:url" content="https://pmb.sbh.ac.id" />
    <meta property="og:image"
        content="https://pmb.sbh.ac.id/dashboard_assets/assets/img/front-pages/landing-page/faq-foto.png" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:image:type" content="image/png" />
    <meta property="og:image:alt" content="Penerimaan Mahasiswa Baru STIKes Bogor Husada" />
    <meta property="fb:app_id" content="1058690100971693" />

    <!-- Google Site Verification -->
    <meta name="google-site-verification" content="Hf-tUtIVnBjdVd_xwX2KDz_B7coRKIPh0Be0mkY27ao" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('dashboard_assets/assets/img/logo/logo_sbh_bulet.png') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet">

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/css/rtl/core.css') }}"
        class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/css/rtl/theme-default.css') }}"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/css/pages/front-page.css') }}" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/libs/nouislider/nouislider.css') }}" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/libs/swiper/swiper.css') }}" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/libs/iziToast/css/iziToast.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/css/pages/front-page-landing.css') }}" />

    <!-- Helpers & Config -->
    <script src="{{ asset('dashboard_assets/assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/js/front-config.js') }}"></script>

    <style>
        /* =========================================
           1. RESET & LAYOUT DASAR
        ========================================= */
        body {
            width: 100vw;
            overflow-x: hidden;
            margin: 0;
            font-family: 'Public Sans', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        label.required::after {
            content: '*';
            color: red;
            margin-left: 5px;
        }

        .image-container img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
        }

        /* =========================================
           2. FLOATING WHATSAPP
        ========================================= */
        .cta-wa-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 12px;
        }

        .admin-options {
            display: flex;
            flex-direction: column;
            gap: 10px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(15px) scale(0.9);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .cta-wa-container:hover .admin-options {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }

        .admin-choice {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(5px);
            color: #333;
            padding: 12px 20px;
            border-radius: 15px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(37, 211, 102, 0.2);
            transition: all 0.3s ease;
        }

        .admin-choice .choice-icon {
            background: #25D366;
            color: white;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 18px;
        }

        .admin-choice:hover {
            background: #25D366;
            color: #fff;
            transform: translateX(-8px);
        }

        .admin-choice:hover .choice-icon {
            background: white;
            color: #25D366;
        }

        .cta-wa-float {
            background: #25D366;
            color: #fff;
            padding: 12px 24px;
            border-radius: 50px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 10px 25px rgba(37, 211, 102, 0.3);
            transition: all 0.3s ease;
            animation: float-pulse 3s infinite;
            text-decoration: none;
        }

        .cta-wa-float:hover {
            background: #20bd5a;
            transform: scale(1.05);
            color: #fff;
        }

        .wa-icon-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .wa-icon-wrapper i {
            font-size: 28px;
        }

        .cta-text {
            font-size: 15px;
            letter-spacing: 0.3px;
        }

        .notification-dot {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 12px;
            height: 12px;
            background-color: #ff3b30;
            border: 2px solid #fff;
            border-radius: 50%;
            animation: pulse-red 2s infinite;
        }

        /* =========================================
           3. ANIMASI
        ========================================= */
        @keyframes float-pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.5);
            }

            70% {
                box-shadow: 0 0 0 18px rgba(37, 211, 102, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(37, 211, 102, 0);
            }
        }

        @keyframes pulse-red {
            0% {
                transform: scale(0.95);
                opacity: 1;
            }

            50% {
                transform: scale(1.3);
                opacity: 0.7;
            }

            100% {
                transform: scale(0.95);
                opacity: 1;
            }
        }

        /* =========================================
           4. KARTU FITUR
        ========================================= */
        .feature-card {
            border-radius: 1rem;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            background: #fff;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
        }

        .feature-icon-box {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: transform 0.3s ease;
        }

        .feature-icon-box i {
            font-size: 36px;
        }

        .feature-card:hover .feature-icon-box {
            transform: scale(1.1);
        }

        /* =========================================
           5. BIAYA KULIAH SECTION
        ========================================= */
        .biaya-section {
            background: linear-gradient(135deg, #f8faff 0%, #f0f4ff 50%, #faf8ff 100%);
            position: relative;
            overflow: hidden;
        }

        .biaya-section::before {
            content: '';
            position: absolute;
            top: -120px;
            right: -120px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.08) 0%, transparent 70%);
            border-radius: 50%;
        }

        .biaya-section::after {
            content: '';
            position: absolute;
            bottom: -100px;
            left: -100px;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.08) 0%, transparent 70%);
            border-radius: 50%;
        }

        .biaya-tabs {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            padding: 6px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            border: 1px solid rgba(0, 0, 0, 0.06);
            max-width: 700px;
            margin: 0 auto 40px;
        }

        .biaya-tab-btn {
            border: none;
            background: transparent;
            padding: 10px 22px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
        }

        .biaya-tab-btn[data-prodi="d3"],
        .biaya-card[data-prodi="d3"] {
            --biaya-primary: #0284c7;
            --biaya-secondary: #38bdf8;
            --biaya-title: #075985;
            --biaya-soft: rgba(14, 165, 233, 0.09);
            --biaya-border: rgba(14, 165, 233, 0.22);
            --biaya-shadow: rgba(14, 165, 233, 0.24);
        }

        .biaya-tab-btn[data-prodi="farmasi"],
        .biaya-tab-btn[data-prodi="karyawan"],
        .biaya-card[data-prodi="farmasi"],
        .biaya-card[data-prodi="karyawan"] {
            --biaya-primary: #7c3aed;
            --biaya-secondary: #a855f7;
            --biaya-title: #6b21a8;
            --biaya-soft: rgba(124, 58, 237, 0.09);
            --biaya-border: rgba(124, 58, 237, 0.22);
            --biaya-shadow: rgba(124, 58, 237, 0.24);
        }

        .biaya-tab-btn[data-prodi="gizi"],
        .biaya-card[data-prodi="gizi"] {
            --biaya-primary: #d97706;
            --biaya-secondary: #f59e0b;
            --biaya-title: #92400e;
            --biaya-soft: rgba(245, 158, 11, 0.11);
            --biaya-border: rgba(245, 158, 11, 0.28);
            --biaya-shadow: rgba(245, 158, 11, 0.24);
        }

        .biaya-tab-btn:hover {
            color: var(--biaya-title, #334155);
            background: var(--biaya-soft, rgba(99, 102, 241, 0.06));
        }

        .biaya-tab-btn.active {
            background: linear-gradient(135deg, var(--biaya-primary, #6366f1), var(--biaya-secondary, #8b5cf6));
            color: #fff;
            box-shadow: 0 8px 20px var(--biaya-shadow, rgba(99, 102, 241, 0.3));
        }

        .biaya-card {
            background: #fff;
            border-radius: 20px;
            border: 1px solid rgba(0, 0, 0, 0.06);
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .biaya-card:hover {
            box-shadow: 0 14px 42px var(--biaya-shadow, rgba(99, 102, 241, 0.12));
            transform: translateY(-4px);
        }

        .biaya-card-header {
            padding: 28px 28px 20px;
            position: relative;
            overflow: hidden;
        }

        .biaya-card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--biaya-primary, #6366f1), var(--biaya-secondary, #8b5cf6));
        }

        .biaya-card-title {
            margin-bottom: 0.75rem;
            color: var(--biaya-title, #052078);
            font-weight: 800;
            line-height: 1.2;
        }

        .biaya-card-header .subtitle {
            color: #475569;
            font-weight: 500;
        }

        .gel-tabs {
            display: flex;
            gap: 0;
            padding: 0 28px;
            border-bottom: 1px solid #f1f5f9;
            overflow-x: auto;
        }

        .gel-tab {
            border: none;
            background: transparent;
            padding: 12px 20px;
            font-size: 13px;
            font-weight: 600;
            color: #94a3b8;
            cursor: pointer;
            position: relative;
            transition: color 0.3s;
            white-space: nowrap;
        }

        .gel-tab::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            border-radius: 2px;
            transition: width 0.3s;
        }

        .gel-tab.active {
            color: var(--biaya-primary, #6366f1);
        }

        .gel-tab.active::after {
            width: 70%;
            background: var(--biaya-primary, #6366f1);
        }

        .gel-tab:hover {
            color: var(--biaya-primary, #6366f1);
        }

        .semester-grid {
            padding: 24px 28px;
        }

        .semester-grid-wrapper {
            display: flex;
            gap: 24px;
            margin-bottom: 16px;
        }

        .semester-col {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .semester-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid transparent;
            transition: all 0.25s ease;
        }

        .semester-item:hover {
            background: var(--biaya-soft, #f0f4ff);
            border-color: var(--biaya-border, rgba(99, 102, 241, 0.15));
        }

        .semester-label {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            font-size: 14px;
            font-weight: 500;
            color: #475569;
        }

        .semester-num {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--biaya-primary, #6366f1), var(--biaya-secondary, #8b5cf6));
            color: #fff;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .semester-price {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            text-align: right;
            white-space: nowrap;
        }

        .semester-price small {
            font-weight: 400;
            color: #94a3b8;
            font-size: 12px;
        }

        .biaya-info-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(0, 0, 0, 0.06);
            height: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .biaya-info-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        }

        .biaya-info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }

        .info-card-periode::before {
            background: linear-gradient(180deg, #f59e0b, #f97316);
        }

        .info-card-termasuk::before {
            background: linear-gradient(180deg, #10b981, #34d399);
        }

        .info-card-belum::before {
            background: linear-gradient(180deg, #ef4444, #f97316);
        }

        .info-card-icon {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 20px;
            margin-bottom: 14px;
        }

        .info-card-icon.icon-periode {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .info-card-icon.icon-termasuk {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .info-card-icon.icon-belum {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .biaya-info-card h6 {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .biaya-info-card p,
        .biaya-info-card small {
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
            margin: 0;
        }

        .periode-item {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 6px;
            font-size: 13px;
            color: #475569;
        }

        .periode-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #f59e0b;
            flex-shrink: 0;
            margin-top: 5px;
        }

        /* =========================================
           6. YOUTUBE SHORTS CAROUSEL
        ========================================= */
        .shorts-carousel-wrapper {
            position: relative;
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 60px;
        }

        .shorts-card {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .shorts-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
        }

        .shorts-video-frame {
            position: relative;
            width: 100%;
            padding-top: 177.78%;
            /* 9:16 aspect ratio */
            overflow: hidden;
            background: #000;
        }

        .shorts-thumbnail {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.4s ease;
        }

        .shorts-card:hover .shorts-thumbnail {
            transform: scale(1.05);
            filter: brightness(0.75);
        }

        .shorts-play-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
            width: 56px;
            height: 56px;
            background: rgba(255, 0, 0, 0.85);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .shorts-card:hover .shorts-play-btn {
            opacity: 1;
        }

        .shorts-play-btn i {
            font-size: 28px;
            color: #fff;
            margin-left: 2px;
        }

        .shorts-yt-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 2;
            background: rgba(255, 0, 0, 0.85);
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        .shorts-yt-badge i {
            font-size: 12px;
        }

        .shorts-card-info {
            padding: 14px 16px;
        }

        .shorts-title {
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.4;
            margin: 0 0 6px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Playing state */
        .shorts-card.playing .shorts-video-frame {
            padding-top: 0;
        }

        .shorts-card.playing .shorts-thumbnail,
        .shorts-card.playing .shorts-play-btn,
        .shorts-card.playing .shorts-yt-badge {
            display: none;
        }

        .shorts-iframe {
            display: none;
            width: 100%;
            aspect-ratio: 9 / 16;
            border: none;
        }

        .shorts-card.playing .shorts-iframe {
            display: block;
        }

        .shorts-nav-prev,
        .shorts-nav-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            width: 42px;
            height: 42px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .shorts-nav-prev {
            left: 0;
        }

        .shorts-nav-next {
            right: 0;
        }

        .shorts-nav-prev:hover,
        .shorts-nav-next:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-50%) scale(1.1);
        }

        .shorts-nav-prev i,
        .shorts-nav-next i {
            font-size: 22px;
            color: #fff;
        }

        .shorts-pagination {
            margin-top: 24px !important;
            position: relative !important;
            bottom: auto !important;
        }

        .shorts-pagination .swiper-pagination-bullet {
            width: 8px;
            height: 8px;
            background: rgba(255, 255, 255, 0.3);
            opacity: 1;
            transition: all 0.3s;
        }

        .shorts-pagination .swiper-pagination-bullet-active {
            background: #ff0000;
            width: 24px;
            border-radius: 4px;
        }

        /* =========================================
           7. FAQ ACCORDION
        ========================================= */
        .custom-accordion .accordion-button:not(.collapsed) {
            background-color: rgba(255, 193, 7, 0.15);
            color: #000;
            box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.125);
        }

        .custom-accordion .accordion-button:focus {
            border-color: rgba(255, 193, 7, 0.5);
            box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25);
        }

        .custom-accordion .accordion-button {
            transition: all 0.3s ease;
        }

        .custom-accordion .accordion-button:hover {
            background-color: #f8f9fa;
        }

        .hover-scale {
            transition: transform 0.3s ease;
        }

        .hover-scale:hover {
            transform: scale(1.02);
        }

        /* =========================================
           8. RESPONSIVE
        ========================================= */
        @media (max-width: 768px) {
            .cta-wa-container {
                bottom: 20px;
                right: 20px;
            }

            .cta-wa-float {
                padding: 10px 18px;
            }

            .cta-text {
                font-size: 14px;
            }

            .wa-icon-wrapper i {
                font-size: 24px;
            }

            .admin-choice {
                padding: 10px 15px;
                font-size: 13px;
            }

            .biaya-tabs {
                gap: 6px;
                padding: 5px;
            }

            .biaya-tab-btn {
                padding: 8px 14px;
                font-size: 12px;
            }

            .biaya-card-header {
                padding: 20px 20px 16px;
            }

            .gel-tabs {
                padding: 0 20px;
            }

            .gel-tab {
                padding: 10px 14px;
                font-size: 12px;
            }

            .semester-grid {
                padding: 16px 20px;
            }

            .semester-grid-wrapper {
                flex-direction: column;
                gap: 10px;
                margin-bottom: 12px;
            }

            .semester-item {
                padding: 12px 14px;
                gap: 12px;
            }

            .semester-price {
                font-size: 14px;
            }

            .shorts-carousel-wrapper {
                padding: 0 40px;
            }

            .shorts-nav-prev,
            .shorts-nav-next {
                width: 34px;
                height: 34px;
            }

            .shorts-nav-prev i,
            .shorts-nav-next i {
                font-size: 18px;
            }
        }

        @media (max-width: 480px) {
            .biaya-tabs {
                flex-direction: column;
                border-radius: 14px;
            }

            .biaya-tab-btn {
                text-align: center;
                border-radius: 10px;
            }

            .semester-item {
                align-items: flex-start;
            }

            .semester-price {
                white-space: normal;
            }

            .shorts-carousel-wrapper {
                padding: 0 32px;
            }

            .shorts-nav-prev,
            .shorts-nav-next {
                width: 30px;
                height: 30px;
            }
        }
    </style>
</head>

<body>
    <script src="{{ asset('dashboard_assets/assets/vendor/js/dropdown-hover.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/js/mega-dropdown.js') }}"></script>

    <!-- ========== NAVBAR ========== -->
    <nav class="layout-navbar shadow-none py-0">
        <div class="container">
            <div class="navbar navbar-expand-lg landing-navbar px-3 px-md-4">
                <div class="navbar-brand app-brand demo d-flex py-0 me-4">
                    <button class="navbar-toggler border-0 px-0 me-2" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                        <i class="tf-icons bx bx-menu bx-sm align-middle"></i>
                    </button>
                    <a href="{{ route('home') }}" class="app-brand-link">
                        <img src="{{ asset('logo/logo_sbh.png') }}" alt="Logo STIKes Bogor Husada"
                            style="width: 200px; height: auto;" />
                    </a>
                </div>

                <div class="collapse navbar-collapse landing-nav-menu" id="navbarSupportedContent">
                    <button class="navbar-toggler border-0 text-heading position-absolute end-0 top-0 scaleX-n1-rtl"
                        type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <i class="tf-icons bx bx-x bx-sm"></i>
                    </button>
                    <ul class="navbar-nav m-auto">
                        <li class="nav-item">
                            <a class="nav-link fw-medium" href="#landingHero">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-medium" href="#landingFeatures">Kenapa STIKes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-medium" href="#biaya-kuliah-stikes-bogor-husada">Biaya Kuliah</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-medium" href="#landingFAQ">FAQ</a>
                        </li>
                    </ul>
                </div>

                <div class="landing-menu-overlay d-lg-none"></div>

                <ul class="navbar-nav flex-row align-items-center ms-auto">
                    <li>
                        @if (auth()->check())
                            <a href="{{ route('dashboard.index') }}" class="btn btn-warning" target="_blank">
                                <span class="tf-icons bx bx-grid-alt me-md-1"></span>
                                <span class="d-none d-md-block">Lihat Dashboard</span>
                            </a>
                        @else
                            <a href="{{ route('auth.login') }}" class="btn btn-warning" target="_blank">
                                <span class="tf-icons bx bx-user me-md-1"></span>
                                <span class="d-none d-md-block">Login</span>
                            </a>
                        @endif
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- ========== END NAVBAR ========== -->

    <!-- ========== SECTIONS ========== -->
    <div data-bs-spy="scroll" class="scrollspy-example">

        <!-- Hero -->
        @include('pages.hero')

        <!-- ========== FITUR UNGGULAN ========== -->
        <section id="landingFeatures" class="section-py bg-light position-relative overflow-hidden">
            <div class="position-absolute top-0 end-0 translate-middle-y bg-warning opacity-10 rounded-circle"
                style="width: 400px; height: 400px; filter: blur(100px); z-index: 0;"></div>

            <div class="container position-relative z-1">
                <div class="text-center mb-5">
                    <span class="badge bg-label-warning rounded-pill px-3 py-2 mb-3 fw-bold text-uppercase">
                        <i class='bx bxs-star me-1'></i> Keunggulan Kami
                    </span>
                    <h2 class="fw-bold display-6 text-dark mb-3">
                        Kenapa Harus <span class="text-primary">STIKes Bogor Husada?</span>
                    </h2>
                    <p class="text-muted fs-5 mx-auto" style="max-width: 700px;">
                        Kami berkomitmen mencetak tenaga kesehatan profesional yang siap kerja melalui kurikulum
                        unggulan dan fasilitas terbaik.
                    </p>
                </div>

                <div class="row g-4">
                    @foreach ($landingInfoCards->get('advantages', collect()) as $card)
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 border-0 shadow-sm feature-card p-4 text-center">
                                <div class="feature-icon-box mb-4 mx-auto bg-{{ $card->color }} bg-opacity-10 text-{{ $card->color }}">
                                    <i class="{{ $card->icon }}"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-3">{{ $card->title }}</h5>
                                <p class="text-secondary mb-0">{{ $card->description }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        <!-- ========== END FITUR ========== -->

        <!-- ========== BIAYA KULIAH ========== -->
        <section id="biaya-kuliah-stikes-bogor-husada" class="section-py biaya-section">
            <div class="container position-relative" style="z-index: 1;">
                <div class="text-center mb-5">
                    <span class="badge bg-label-warning px-3 py-2 rounded-pill fw-bold mb-3">
                        <i class='bx bx-money me-1'></i> Biaya Kuliah
                    </span>
                    <h2 class="fw-bold display-6 mb-3" style="color: #1e293b;">
                        Rincian Biaya <span style="color: #6366f1;">STIKes Bogor Husada</span>
                    </h2>
                    <p style="color: #64748b; font-size: 1.1rem; max-width: 700px; margin: 0 auto;">
                        Transparansi biaya pendidikan per program studi untuk membantu Anda merencanakan masa depan
                        dengan lebih baik.
                    </p>
                </div>

                <!-- Tab Prodi -->
                <div class="biaya-tabs" id="biayaProdiTabs">
                    <button class="biaya-tab-btn active" data-prodi="d3" data-target="panel-d3">D3 Kebidanan</button>
                    <button class="biaya-tab-btn" data-prodi="farmasi" data-target="panel-farmasi">S1 Farmasi Reguler</button>
                    <button class="biaya-tab-btn" data-prodi="karyawan" data-target="panel-karyawan">S1 Farmasi Karyawan</button>
                    <button class="biaya-tab-btn" data-prodi="gizi" data-target="panel-gizi">S1 Gizi</button>
                </div>

                <!-- Panel: D3 Kebidanan -->
                <div class="biaya-panel" id="panel-d3" data-prodi="d3">
                    <div class="biaya-card" data-prodi="d3">
                        <div class="biaya-card-header text-center">
                            <h2 class="biaya-card-title">Program Studi D3 Kebidanan</h2>
                            <span class="subtitle">Waktu Studi : 6 Semester (3 Tahun)</span>
                        </div>
                        <div class="gel-tabs" data-card="d3">
                            <button class="gel-tab active" data-gel="1">Gelombang I</button>
                            <button class="gel-tab" data-gel="2">Gelombang II</button>
                            <button class="gel-tab" data-gel="3">Gelombang III</button>
                        </div>
                        <div class="semester-grid" id="d3-semesters"></div>
                    </div>
                </div>

                <!-- Panel: S1 Farmasi Reguler -->
                <div class="biaya-panel" id="panel-farmasi" data-prodi="farmasi" style="display: none;">
                    <div class="biaya-card" data-prodi="farmasi">
                        <div class="biaya-card-header text-center">
                            <h2 class="biaya-card-title">Program Studi S1 Farmasi Reguler</h2>
                            <span class="subtitle">Waktu Studi : 8 Semester (4 Tahun)</span>
                        </div>
                        <div class="gel-tabs" data-card="farmasi">
                            <button class="gel-tab active" data-gel="1">Gelombang I</button>
                            <button class="gel-tab" data-gel="2">Gelombang II</button>
                            <button class="gel-tab" data-gel="3">Gelombang III</button>
                        </div>
                        <div class="semester-grid" id="farmasi-semesters"></div>
                    </div>
                </div>

                <!-- Panel: S1 Farmasi Karyawan -->
                <div class="biaya-panel" id="panel-karyawan" data-prodi="karyawan" style="display: none;">
                    <div class="biaya-card" data-prodi="karyawan">
                        <div class="biaya-card-header text-center">
                            <h2 class="biaya-card-title">Program Studi S1 Farmasi Karyawan</h2>
                            <span class="subtitle">Waktu Studi : 8 Semester (4 Tahun)</span>
                        </div>
                        <div class="gel-tabs" data-card="karyawan">
                            <button class="gel-tab active" data-gel="1">Gelombang I</button>
                            <button class="gel-tab" data-gel="2">Gelombang II</button>
                            <button class="gel-tab" data-gel="3">Gelombang III</button>
                        </div>
                        <div class="semester-grid" id="karyawan-semesters"></div>
                    </div>
                </div>

                <!-- Panel: S1 Gizi -->
                <div class="biaya-panel" id="panel-gizi" data-prodi="gizi" style="display: none;">
                    <div class="biaya-card" data-prodi="gizi">
                        <div class="biaya-card-header text-center">
                            <h2 class="biaya-card-title">Program Studi S1 Gizi</h2>
                            <span class="subtitle">Waktu Studi : 8 Semester (4 Tahun)</span>
                        </div>
                        <div class="gel-tabs" data-card="gizi">
                            <button class="gel-tab active" data-gel="1">Gelombang I</button>
                            <button class="gel-tab" data-gel="2">Gelombang II</button>
                            <button class="gel-tab" data-gel="3">Gelombang III</button>
                        </div>
                        <div class="semester-grid" id="gizi-semesters"></div>
                    </div>
                </div>

                <!-- Info Tambahan -->
                <div class="row mt-5 g-4">
                    @foreach ($landingInfoCards->get('cost_info', collect()) as $card)
                        <div class="col-md-4">
                            <div class="biaya-info-card info-card-{{ $card->variant }}">
                                <div class="info-card-icon icon-{{ $card->variant }}"><i class='{{ $card->icon }}'></i></div>
                                <h6>{{ $card->title }}</h6>
                                <div style="font-size: 13px; color: #475569; line-height: 1.8;">
                                    @foreach ($card->items ?? [] as $item)
                                        <div class="periode-item"><span class="periode-dot bg-{{ $card->color }}"></span>{{ $item }}</div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        <!-- ========== END BIAYA KULIAH ========== -->

        <!-- ========== YOUTUBE SHORTS ========== -->
        @if(isset($landingVideos) && $landingVideos->isNotEmpty())
        <section id="landingShorts" class="section-py bg-dark position-relative overflow-hidden">
            @include('landing-page._shorts_section')
        </section>
        @endif
        <!-- ========== END YOUTUBE SHORTS ========== -->

        <!-- ========== FAQ ========== -->
        <section id="landingFAQ" class="section-py bg-light position-relative overflow-hidden">
            <div class="position-absolute top-0 start-0 translate-middle bg-warning bg-opacity-10"
                style="width: 300px; height: 300px; filter: blur(80px);"></div>

            <div class="container position-relative z-1">
                <div class="text-center mb-5">
                    <span class="badge bg-label-warning px-3 py-2 rounded-pill fs-tiny fw-bold text-uppercase mb-3">
                        <i class='bx bx-question-mark me-1'></i> FAQ
                    </span>
                    <h2 class="fw-bold display-6 mb-3 text-dark">Pertanyaan Sering Diajukan</h2>
                    <p class="text-muted fs-5 mx-auto" style="max-width: 600px;">
                        Cari tahu informasi seputar pendaftaran, program studi, dan fasilitas unggulan di STIKes Bogor
                        Husada.
                    </p>
                </div>

                <div class="row gy-5 align-items-start">
                    <div class="col-lg-5 mb-4 mb-lg-0">
                        <div class="text-center position-relative px-4">
                            <div class="position-absolute top-50 start-50 translate-middle bg-white rounded-circle opacity-50"
                                style="width: 80%; height: 80%; z-index: -1;"></div>
                            <img src="{{ asset('dashboard_assets/assets/img/front-pages/landing-page/faq-stikes-bogor-husada.png') }}"
                                alt="FAQ STIKes Bogor Husada" class="img-fluid position-relative z-1 hover-scale"
                                style="max-width: 100%; height: auto; border-radius: 20px;">
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="accordion custom-accordion" id="accordionFAQ">

                            <div class="accordion-item border-0 mb-3 shadow-sm rounded-3 overflow-hidden">
                                <h2 class="accordion-header" id="headingBenefit">
                                    <button class="accordion-button fw-bold text-dark bg-white" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#accordionBenefit"
                                        aria-expanded="true">
                                        <i class='bx bx-star me-3 text-warning fs-4'></i>
                                        Apa saja benefit kuliah di STIKes Bogor Husada?
                                    </button>
                                </h2>
                                <div id="accordionBenefit" class="accordion-collapse collapse show"
                                    data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body text-secondary lh-lg">
                                        Lingkungan belajar nyaman, dosen berpengalaman, fasilitas praktik lengkap,
                                        magang di instansi kesehatan mitra, serta pembinaan karier yang terukur.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item border-0 mb-3 shadow-sm rounded-3 overflow-hidden">
                                <h2 class="accordion-header" id="headingProspek">
                                    <button class="accordion-button collapsed fw-bold text-dark bg-white" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#accordionProspek">
                                        <i class='bx bx-briefcase me-3 text-warning fs-4'></i>
                                        Bagaimana prospek kerja lulusannya?
                                    </button>
                                </h2>
                                <div id="accordionProspek" class="accordion-collapse collapse"
                                    data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body text-secondary lh-lg">
                                        Lulusan dibekali kompetensi sesuai kebutuhan industri kesehatan dan memiliki
                                        peluang besar bekerja di rumah sakit, klinik, laboratorium, instansi pemerintah
                                        (PNS/PPPK), serta sektor kesehatan lainnya.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item border-0 mb-3 shadow-sm rounded-3 overflow-hidden">
                                <h2 class="accordion-header" id="headingMitra">
                                    <button class="accordion-button collapsed fw-bold text-dark bg-white" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#accordionMitra">
                                        <i class='bx bx-buildings me-3 text-warning fs-4'></i>
                                        Apakah ada kerja sama dengan instansi kesehatan?
                                    </button>
                                </h2>
                                <div id="accordionMitra" class="accordion-collapse collapse"
                                    data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body text-secondary lh-lg">
                                        Ya, kami bekerja sama dengan berbagai rumah sakit dan puskesmas untuk praktik
                                        dan magang. Kampus kami berada di bawah naungan <strong>RS Azra</strong>, yang
                                        memberikan akses jejaring kesehatan yang kuat.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item border-0 mb-3 shadow-sm rounded-3 overflow-hidden">
                                <h2 class="accordion-header" id="headingLaptop">
                                    <button class="accordion-button collapsed fw-bold text-dark bg-white" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#accordionLaptop">
                                        <i class='bx bx-laptop me-3 text-warning fs-4'></i>
                                        Apakah laptop gratis perlu dikembalikan?
                                    </button>
                                </h2>
                                <div id="accordionLaptop" class="accordion-collapse collapse"
                                    data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body text-secondary lh-lg">
                                        <strong>Tidak.</strong> Laptop merupakan hak milik mahasiswa sepenuhnya. Jika
                                        terdapat kendala software, tim ICT kami siap membantu melakukan pengecekan.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item border-0 mb-3 shadow-sm rounded-3 overflow-hidden">
                                <h2 class="accordion-header" id="headingBeasiswa">
                                    <button class="accordion-button collapsed fw-bold text-dark bg-white" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#accordionBeasiswa">
                                        <i class='bx bx-medal me-3 text-warning fs-4'></i>
                                        Apakah tersedia program beasiswa?
                                    </button>
                                </h2>
                                <div id="accordionBeasiswa" class="accordion-collapse collapse"
                                    data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body text-secondary lh-lg">
                                        Ada. Tersedia program beasiswa untuk mahasiswa berprestasi
                                        (akademik/non-akademik) maupun mahasiswa yang memenuhi kriteria bantuan
                                        pendidikan tertentu.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item border-0 mb-3 shadow-sm rounded-3 overflow-hidden">
                                <h2 class="accordion-header" id="headingOnline">
                                    <button class="accordion-button collapsed fw-bold text-dark bg-white" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#accordionOnline">
                                        <i class='bx bx-globe me-3 text-warning fs-4'></i>
                                        Apakah pendaftaran bisa dilakukan secara online?
                                    </button>
                                </h2>
                                <div id="accordionOnline" class="accordion-collapse collapse"
                                    data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body text-secondary lh-lg">
                                        Ya, seluruh proses pendaftaran mulai dari pengisian data hingga upload berkas
                                        dapat dilakukan secara online melalui website PMB ini tanpa harus datang ke
                                        kampus.
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- ========== END FAQ ========== -->

    </div>
    <!-- ========== END SECTIONS ========== -->

    <!-- ========== FOOTER ========== -->
    @include('partials.footer')
    <!-- ========== END FOOTER ========== -->

    <!-- ========== FLOATING WHATSAPP ========== -->
    <div class="cta-wa-container">
        <div class="admin-options">
            <a href="https://wa.me/6281110111560?text=Halo%20Minda%201%2C%20saya%20ingin%20bertanya%20tentang%20PMB%20STIKes%20Bogor%20Husada"
                target="_blank" rel="noopener noreferrer" class="admin-choice">
                <div class="choice-icon"><i class="bx bxl-whatsapp"></i></div>
                <span>Admin Minda 1</span>
            </a>
            <a href="https://wa.me/6282321780950?text=Halo%20Minda%202%2C%20saya%20ingin%20bertanya%20tentang%20PMB%20STIKes%20Bogor%20Husada"
                target="_blank" rel="noopener noreferrer" class="admin-choice">
                <div class="choice-icon"><i class="bx bxl-whatsapp"></i></div>
                <span>Admin Minda 2</span>
            </a>
        </div>
        <div class="cta-wa-float" role="button" aria-label="Chat WhatsApp dengan admin PMB">
            <div class="wa-icon-wrapper">
                <i class="bx bxl-whatsapp"></i>
                <span class="notification-dot"></span>
            </div>
            <span class="cta-text">Chat Minda (PMB)</span>
        </div>
    </div>
    <!-- ========== END FLOATING WHATSAPP ========== -->

    <!-- ========== VENDOR JS ========== -->
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/nouislider/nouislider.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/swiper/swiper.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/iziToast/js/iziToast.min.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/js/front-main.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/js/custom.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/js/front-page-landing.js') }}"></script>
    <!-- ========== END VENDOR JS ========== -->

    <!-- ========== PAGE SCRIPTS ========== -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            /* --------------------------------------------------
               BIAYA KULIAH — render semester grid
            -------------------------------------------------- */
            var biayaData = @json($biayaData);

            function formatRp(val) {
                return 'Rp ' + val.toLocaleString('id-ID');
            }

            function renderSemesters(key, gel) {
                var info = biayaData[key];
                var prices = info.gel[gel];
                var container = document.getElementById(key + '-semesters');

                if (!info || !prices || !container) return;

                var half = Math.ceil(prices.length / 2);
                var html = '<div class="semester-grid-wrapper">';

                // Kolom kiri
                html += '<div class="semester-col">';
                for (var i = 0; i < half; i++) {
                    var label = (i === 0) ? 'Biaya Awal Masuk' : 'Semester ' + (i + 1);
                    html += '<div class="semester-item">'
                        + '<div class="semester-label">'
                        + '<span class="semester-num">' + (i + 1) + '</span>' + label
                        + '</div>'
                        + '<div class="semester-price">' + formatRp(prices[i]) + '</div>'
                        + '</div>';
                }
                html += '</div>';

                // Kolom kanan
                html += '<div class="semester-col">';
                for (var i = half; i < prices.length; i++) {
                    var label = 'Semester ' + (i + 1);
                    html += '<div class="semester-item">'
                        + '<div class="semester-label">'
                        + '<span class="semester-num">' + (i + 1) + '</span>' + label
                        + '</div>'
                        + '<div class="semester-price">' + formatRp(prices[i]) + '</div>'
                        + '</div>';
                }
                html += '</div>';

                html += '</div>'; // tutup semester-grid-wrapper
                container.innerHTML = html;
            }

            // Render semua prodi dengan gelombang 1 sebagai default
            Object.keys(biayaData).forEach(function (k) {
                renderSemesters(k, 1);
            });

            // Event: klik tab prodi
            document.querySelectorAll('.biaya-tab-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.biaya-tab-btn').forEach(function (b) {
                        b.classList.remove('active');
                    });
                    btn.classList.add('active');

                    document.querySelectorAll('.biaya-panel').forEach(function (p) {
                        p.style.display = 'none';
                    });
                    document.getElementById(btn.getAttribute('data-target')).style.display = 'block';
                });
            });

            // Event: klik tab gelombang
            document.querySelectorAll('.gel-tabs').forEach(function (tabGroup) {
                tabGroup.querySelectorAll('.gel-tab').forEach(function (tab) {
                    tab.addEventListener('click', function () {
                        tabGroup.querySelectorAll('.gel-tab').forEach(function (t) {
                            t.classList.remove('active');
                        });
                        tab.classList.add('active');
                        renderSemesters(
                            tabGroup.getAttribute('data-card'),
                            parseInt(tab.getAttribute('data-gel'))
                        );
                    });
                });
            });

            /* --------------------------------------------------
               YOUTUBE SHORTS — Swiper (single init)
            -------------------------------------------------- */
            var shortsSwiper = new Swiper('.shortsSwiper', {
                slidesPerView: 1,
                spaceBetween: 16,
                centeredSlides: true,
                loop: true,
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                pagination: {
                    el: '.shorts-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.shorts-nav-next',
                    prevEl: '.shorts-nav-prev',
                },
                breakpoints: {
                    480: { slidesPerView: 1.5, spaceBetween: 14 },
                    640: { slidesPerView: 2, spaceBetween: 18 },
                    768: { slidesPerView: 2.5, spaceBetween: 20 },
                    1024: { slidesPerView: 3, spaceBetween: 24 },
                },
            });

            // Klik kartu → play video inline
            document.querySelectorAll('.shorts-card').forEach(function (card) {
                card.addEventListener('click', function () {
                    if (card.classList.contains('playing')) return;

                    // Hentikan video lain yang sedang bermain
                    document.querySelectorAll('.shorts-card.playing').forEach(function (c) {
                        c.classList.remove('playing');
                        var old = c.querySelector('.shorts-iframe');
                        if (old) old.remove();
                    });

                    var videoId = card.getAttribute('data-video');
                    var frame = card.querySelector('.shorts-video-frame');
                    var iframe = document.createElement('iframe');
                    iframe.className = 'shorts-iframe';
                    iframe.src = 'https://www.youtube.com/embed/' + videoId + '?autoplay=1&rel=0';
                    iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
                    iframe.allowFullscreen = true;
                    frame.appendChild(iframe);
                    card.classList.add('playing');
                    shortsSwiper.autoplay.stop();
                });
            });

        }); // end DOMContentLoaded
    </script>
    <!-- ========== END PAGE SCRIPTS ========== -->

    <!-- Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-NDC7V4P44N"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-NDC7V4P44N');
    </script>
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-1008078404"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'AW-1008078404');
    </script>

    <!-- WatzApp Widget -->
    <script async data-watzapkey="tuT61864" src="https://cdn.watzap.id/widget-api.js"></script>

    @include('dashboard.components.notification')

</body>

</html>
