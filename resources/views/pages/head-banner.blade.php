<section class="banner-carousel py-4 mt-10">
    <div class="container mt-10">
        <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner rounded shadow" style="max-height: 850px; overflow: hidden;">
                <!-- Banner 1 -->
                <div class="carousel-item active">
                    <a href="https://join.sbh.ac.id/" target="_blank">
                        <img src="{{ asset('dashboard_assets/assets/img/banner/Tiket_Beasiswa_SNBT_SNBP.jpg') }}"
                            class="d-block w-100" alt="Tiket Beasiswa STIKes Bogor Husada"
                            style="object-fit: cover; height: 100%; border-radius: 16px;" />
                    </a>
                </div>

                <!-- Banner 2 -->
                <div class="carousel-item">

                    <img src="{{ asset('dashboard_assets/assets/img/banner/pmb_web_new.jpg') }}" class="d-block w-100"
                        alt="PMB Web Banner" style="object-fit: cover; height: 100%; border-radius: 16px;" />

                </div>
            </div>

            <!-- Tombol Geser -->
            <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Sebelumnya</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Berikutnya</span>
            </button>
        </div>
    </div>
</section>