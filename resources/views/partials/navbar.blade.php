<!-- resources/views/partials/navbar.blade.php -->
<nav class="layout-navbar shadow-none py-0">
    <div class="container">
        <div class="navbar navbar-expand-lg landing-navbar px-3 px-md-4">
            <!-- Logo & Toggle -->
            <div class="navbar-brand app-brand demo d-flex py-0 me-4 align-items-center">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                    <i class="tf-icons bx bx-menu bx-sm align-middle"></i>
                </button>
                <a href="{{ url('/') }}" class="app-brand-link d-flex align-items-center">
                    <img src="{{ asset('dashboard_assets/assets/img/logo/logo_sbh_panjang.png') }}"
                        alt="Logo STIKes Bogor Husada" style="height: 30px; width: auto;">
                </a>
            </div>

            <!-- Navbar Menu -->
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mx-auto gap-2">
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="{{ url('/') }}">
                            <i class="bx bxs-home me-1"></i> Beranda
                        </a>
                    </li>
                    <!-- Tentang Kami -->
                    <li class="nav-item dropdown">
                        <a class="nav-link fw-medium dropdown-toggle" href="#" id="tentangKamiDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bxs-info-circle me-1"></i> Tentang Kami
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg-end dropdown-menu-samping"
                            aria-labelledby="tentangKamiDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('sejarah') }}">
                                    <i class="bx bxs-book-content me-1"></i> Sejarah
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('sambutan') }}">
                                    <i class="bx bxs-user-voice me-1"></i> Sambutan Ketua SBH
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-primary fw-semibold" href="{{ route('tentang') }}">
                                    <i class="bx bx-link-external me-1"></i> Lihat Selengkapnya
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link fw-medium dropdown-toggle" href="#" id="prodiDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                             <i class='bx bx-book-open me-2 fs-5'></i> Program Studi
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg-end dropdown-menu-samping"
                            aria-labelledby="prodiDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ url('pendidikan/program-studi-d3-kebidanan') }}">
                                    <i class="bx bxs-user-plus me-1"></i> DIII Kebidanan
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ url('pendidikan/program-studi-farmasi') }}">
                                    <i class="bx bxs-capsule me-1"></i> SI Farmasi
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ url('pendidikan/program-studi-s1-gizi') }}">
                                    <i class="bx bxs-bowl-hot me-1"></i> SI Gizi
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-primary fw-semibold" href="{{ route('pendidikan') }}">
                                    <i class="bx bx-link-external me-1"></i> Lihat Semua Program Studi
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Artikel / Berita Dropdown with Categories -->
                    <li class="nav-item dropdown">
                        <a class="nav-link fw-medium dropdown-toggle" href="#" id="beritaDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bxs-news me-1"></i> Artikel / Berita
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg-end dropdown-menu-samping"
                            aria-labelledby="beritaDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('berita.index') }}">
                                    <i class="bx bxs-news me-1"></i> Semua Artikel
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ url('/berita#kategori-pengumuman') }}">
                                    <i class="bx bxs-megaphone me-1"></i> Pengumuman
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ url('/berita#kategori-kegiatan') }}">
                                    <i class="bx bxs-calendar-event me-1"></i> Kegiatan Kampus
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ url('/berita#kategori-prestasi') }}">
                                    <i class="bx bxs-trophy me-1"></i> Prestasi
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ url('/berita#kategori-lainnya') }}">
                                    <i class="bx bxs-category me-1"></i> Lainnya
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-primary fw-semibold" href="{{ route('berita.index') }}">
                                    <i class="bx bx-link-external me-1"></i> Lihat Selengkapnya
                                </a>
                            </li>
                        </ul>
                    </li>



                </ul>

                <!-- Overlay (for mobile) -->
                <div class="landing-menu-overlay d-lg-none"></div>
            </div>

            <!-- Call to Action -->
            <ul class="navbar-nav flex-row align-items-center ms-auto">
                <li class="nav-item">
                    <a href="https://api.whatsapp.com/send?phone=6281110111560&text=Hai+MinDa%21+Aku+lihat+info+STIKes+Bogor+Husada+di+Instagram%2C+penasaran+nih.+Boleh+tanya-tanya+lebih+lanjut%3F" class="btn btn-warning d-flex align-items-center">
                        <i class="tf-icons bx bx-phone-call me-1"></i>
                        <span class="d-none d-md-inline">Kontak</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

@push('scripts')
<script src="{{ asset('dashboard_assets/assets/js/front-page.js') }}"></script>
@endpush