<section id="berita" class="section-py portfolio">
    <div class="container">
        <div class="text-center mb-4">
            <span class="badge bg-label-primary">Berita</span>
        </div>
        <h4 class="text-center mb-1">
            <span class="position-relative fw-extrabold z-1">
                Berita Terkini
                <i class="bx bx-news section-title-icon position-absolute object-fit-contain bottom-0 z-n1"></i>
            </span>
        </h4>
        <p class="text-center mb-5">Informasi terbaru dari STIKes Bogor Husada yang perlu kamu ketahui.</p>

        <!-- Filter Kategori -->
        <div class="text-center mb-5">
            <button class="btn btn-outline-primary m-1 berita-filter active" data-id="all">Semua</button>
            @foreach($kategori as $kat)
            <button class="btn btn-outline-primary m-1 berita-filter" data-id="{{ $kat['id'] }}">
                {{ $kat['name'] }}
            </button>
            @endforeach
        </div>

        <!-- Grid Berita -->
        <div class="row g-4 berita-container">
            <!-- Data akan dimuat dengan AJAX -->
        </div>
    </div>
</section>