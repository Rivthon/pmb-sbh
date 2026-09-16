<section id="landingHero" class="position-relative overflow-hidden bg-white section-py py-4 py-lg-5">
    <div class="position-absolute top-0 end-0 translate-middle-y d-none d-lg-block"
        style="width: 600px; height: 600px; z-index: 0;">
    </div>

    <div class="container position-relative z-1 section-py py-4 py-lg-5">
        <div class="row align-items-center gy-4 gy-lg-5">

            <div class="col-lg-6 text-center text-lg-start order-2 order-lg-1">
                <div
                    class="d-inline-flex align-items-center border border-warning bg-warning bg-opacity-10 rounded-pill px-3 py-1 mb-3 mb-lg-4">
                    <span class="badge bg-warning rounded-pill me-2">Baru</span>
                    <span class="text-dark fw-bold fs-tiny">PMB 2026/2027</span>
                </div>

                <h1 class="display-5 fw-extrabold text-dark mb-3 lh-sm responsive-title">
                    Cari Kampus Kesehatan? <br class="d-none d-md-block">
                    <span class="text-warning">STIKes Bogor Husada</span> Aja!
                </h1>

                <p class="fs-6 fs-lg-5 text-muted mb-4 mb-lg-5 mx-auto mx-lg-0" style="max-width: 550px;">
                    Wujudkan impian menjadi tenaga kesehatan profesional dengan fasilitas lengkap, biaya terjangkau, dan
                    jaminan karier cemerlang.
                </p>

                <div class="d-grid d-sm-flex gap-3 justify-content-center justify-content-lg-start mb-4 mb-lg-5">
                    <a href="{{ route('auth.register') }}"
                        class="btn btn-warning btn-lg fw-bold shadow-sm hover-lift px-4">
                        <i class='bx bx-edit-alt me-2'></i> Daftar Sekarang
                    </a>
                    <a href="#landingFeatures" class="btn btn-outline-dark btn-lg fw-semibold hover-lift px-4">
                        <i class='bx bx-search-alt me-2'></i> Pelajari Dulu
                    </a>
                </div>

                <div class="row g-2 g-md-3 text-start">
                    @foreach ($heroFeatures as $feature)
                        <div class="col-12 col-sm-6">
                            <div class="d-flex align-items-center p-2 p-md-3 rounded-3 bg-light hover-bg-white border-transparent h-100">
                                <div class="icon-box bg-{{ $feature->color }} bg-opacity-10 text-{{ $feature->color }} rounded-circle me-3 flex-shrink-0">
                                    <i class='{{ $feature->icon }} fs-4'></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark fs-6">{{ $feature->title }}</h6>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">{{ $feature->description }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-lg-6 text-center position-relative order-1 order-lg-2 pt-5 pt-lg-0 mt-4 mt-lg-5">
                <div class="position-absolute top-50 start-50 translate-middle bg-primary opacity-10 rounded-circle d-none d-md-block"
                    style="width: 400px; height: 400px; filter: blur(60px); z-index: -1;"></div>

                <div class="position-relative d-inline-block animate-float">
                    <img src="{{ $landingMedia->get('hero_image')?->url ?? asset('dashboard_assets/assets/img/front-pages/backgrounds/foto_utama.png') }}"
                        alt="Mahasiswa STIKes Bogor Husada"
                        class="img-fluid rounded-4 shadow-lg position-relative z-1 hero-img-main mt-3 mt-lg-0">

                    <div
                        class="position-absolute bottom-0 start-0 mb-4 ms-n2 ms-md-4 bg-white p-2 p-md-3 rounded-3 shadow-lg z-2 animate-bounce-slow">
                        <div class="d-flex align-items-center">
                            <i class='bx bxs-star text-warning fs-4 fs-md-3 me-2'></i>
                            <div class="text-start">
                                <h6 class="mb-0 fw-bold" style="font-size: 0.85rem;">Terakreditasi</h6>
                                <small class="text-muted" style="font-size: 0.7rem;">Baik Sekali</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<style>
    /* Responsive Title Adjustments */
    .responsive-title {
        font-size: calc(1.6rem + 1.5vw);
        font-weight: 800;
    }

    .hero-img-main {
        border: 4px solid rgba(255, 255, 255, 0.8);
        max-height: 400px;
        object-fit: cover;
    }

    @media (min-width: 992px) {
        .hero-img-main {
            max-height: 550px;
        }

        .responsive-title {
            font-size: 3.5rem;
        }
    }

    /* Icon Box */
    .icon-box {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @media (min-width: 768px) {
        .icon-box {
            width: 48px;
            height: 48px;
        }
    }

    /* Hover & Animations */
    .hover-lift {
        transition: transform 0.2s ease;
    }

    .hover-lift:hover {
        transform: translateY(-3px);
    }

    .animate-float {
        animation: float 6s ease-in-out infinite;
    }

    .animate-bounce-slow {
        animation: bounce-slow 4s infinite;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    @keyframes bounce-slow {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-5px);
        }
    }
</style>
