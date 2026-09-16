<footer class="footer bg-dark text-light pt-5 mt-5 position-relative overflow-hidden">
    <div class="position-absolute top-0 start-0 translate-middle"
        style="width: 300px; height: 300px; filter: blur(80px);"></div>

    <div class="container position-relative z-1">
        <div class="row gy-5 gx-lg-5">
            <div class="col-lg-5 col-md-12">
                <div class="bg-white p-2 px-3 rounded-3 d-inline-block mb-4 shadow-sm">
                    <img src="{{ asset('dashboard_assets/assets/img/logo/logo_sbh_panjang.png') }}"
                        alt="Logo STIKes Bogor Husada" style="height: 35px; width: auto;" />
                </div>

                <p class="text-white-50 mb-4 lh-lg" style="max-width: 90%;">
                    STIKes Bogor Husada (SBH) adalah kampus kesehatan di bawah naungan RS Azra,
                    berkomitmen mencetak tenaga kesehatan profesional, unggul, dan berkarakter.
                </p>

                <h6 class="text-white fw-bold mb-3">Ikuti Kami</h6>
                <div class="d-flex gap-2">
                    <a href="https://www.instagram.com/stikesbogorhusada/" class="social-btn" target="_blank">
                        <i class="bx bxl-instagram"></i>
                    </a>
                    <a href="https://www.facebook.com/stikesboda" class="social-btn" target="_blank">
                        <i class="bx bxl-facebook-circle"></i>
                    </a>
                    <a href="https://www.tiktok.com/@stikesbogorhusada" class="social-btn" target="_blank">
                        <i class="bx bxl-tiktok"></i>
                    </a>
                    <a href="https://www.youtube.com/channel/UCLVS17eZrNYWgiAuMCYkkXA" class="social-btn"
                        target="_blank">
                        <i class="bx bxl-youtube"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <h5 class="text-white fw-bold mb-4 position-relative d-inline-block">
                    Akses Cepat
                    <span
                        class="position-absolute bottom-0 start-0 w-50 border-bottom border-warning border-2 pb-1"></span>
                </h5>
                <ul class="list-unstyled footer-nav">
                    <li class="mb-2">
                        <a href="{{ route('auth.login') }}" target="_blank" class="d-flex align-items-center">
                            <i class='bx bx-chevron-right text-warning me-2'></i> Login Mahasiswa
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('auth.register') }}" target="_blank" class="d-flex align-items-center">
                            <i class='bx bx-chevron-right text-warning me-2'></i> Daftar Sekarang (PMB)
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="https://sbh.ac.id/" class="d-flex align-items-center">
                            <i class='bx bx-chevron-right text-warning me-2'></i> Website STIkes Bogor Husada
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-6">
                <h5 class="text-white fw-bold mb-4 position-relative d-inline-block">
                    Hubungi Kami
                    <span
                        class="position-absolute bottom-0 start-0 w-50 border-bottom border-warning border-2 pb-1"></span>
                </h5>

                <ul class="list-unstyled text-white-50">
                    <li class="d-flex mb-3">
                        <div class="flex-shrink-0 text-warning me-3">
                            <i class="bx bxs-map fs-4"></i>
                        </div>
                        <div>
                            <span class="d-block text-white fw-semibold">Kampus Utama</span>
                            <span class="fs-small">Jl. Sholeh Iskandar No.4, Kota Bogor, Jawa Barat</span>
                        </div>
                    </li>
                    <li class="d-flex mb-3">
                        <div class="flex-shrink-0 text-warning me-3">
                            <i class="bx bxs-envelope fs-4"></i>
                        </div>
                        <div>
                            <span class="d-block text-white fw-semibold">Email</span>
                            <span>admisi@sbh.ac.id</span>
                        </div>
                    </li>
                    <li class="d-flex mb-3">
                        <div class="flex-shrink-0 text-warning me-3">
                            <i class="bx bxs-phone-call fs-4"></i>
                        </div>
                        <div>
                            <span class="d-block text-white fw-semibold">Layanan PMB (WhatsApp)</span>
                            <a href="https://wa.me/6281110111560" target="_blank"
                                class="text-white-50 text-decoration-none d-block hover-text-warning">
                                Admin 1: +62 811-1011-1560
                            </a>
                            <a href="https://wa.me/6282321780950" target="_blank"
                                class="text-white-50 text-decoration-none d-block hover-text-warning">
                                Admin 2: +62 823-2178-0950
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <hr class="border-secondary my-5 opacity-25" />

        <div class="row align-items-center pb-4">
            <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                <p class="text-white-50 mb-0 small">
                    &copy; 2025 <strong class="text-white">STIKes Bogor Husada</strong>. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <p class="text-white-50 mb-0 small">
                    Built with <i class="bx bxs-heart text-danger mx-1"></i> by <span class="text-white">ICT BODA</span>
                </p>
            </div>
        </div>
    </div>
</footer>

<style>
    /* Tombol Social Media */
    .social-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 1.2rem;
    }

    .social-btn:hover {
        background: #ffc107;
        /* Warna Kuning */
        color: #000;
        transform: translateY(-3px);
    }

    /* Link Navigasi */
    .footer-nav a {
        color: rgba(255, 255, 255, 0.6);
        text-decoration: none;
        transition: all 0.3s ease;
        padding-left: 0;
    }

    .footer-nav a:hover {
        color: #ffc107;
        padding-left: 8px;
        /* Efek geser kanan */
    }

    /* Hover Text Warning */
    .hover-text-warning:hover {
        color: #ffc107 !important;
    }

    .fs-small {
        font-size: 0.9rem;
    }
</style>
<script async data-watzapkey="tuT61864" src="https://cdn.watzap.id/widget-api.js"></script>

<script src="{{ asset('dashboard_assets/assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('dashboard_assets/assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('dashboard_assets/assets/vendor/js/bootstrap.js') }}"></script>

<script async src="https://www.googletagmanager.com/gtag/js?id=G-NDC7V4P44N"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('js', new Date());
    gtag('config', 'G-NDC7V4P44N');
</script>

<script src="{{ asset('dashboard_assets/assets/vendor/libs/nouislider/nouislider.js') }}"></script>
<script src="{{ asset('dashboard_assets/assets/vendor/libs/swiper/swiper.js') }}"></script>
<script src="{{ asset('dashboard_assets/assets/vendor/libs/iziToast/js/iziToast.min.js') }}"></script>

<script src="{{ asset('dashboard_assets/assets/js/front-main.js') }}"></script>
<script src="{{ asset('dashboard_assets/assets/js/custom.js') }}"></script>
<script src="{{ asset('dashboard_assets/assets/js/front-page-landing.js') }}"></script>
<script async data-watzapkey="xb3L1863" src="https://cdn.watzap.id/widget-api.js"></script>