<div class="container position-relative z-1">
    <div class="text-center mb-5">
        <h2 class="fw-bold display-6 mb-3 text-white">
            Cerita <span class="text-warning">Mahasiswa Kami</span>
        </h2>
        <p class="text-white-50 fs-5 mx-auto" style="max-width: 600px;">
            Dengarkan langsung pengalaman dan cerita inspiratif dari para mahasiswa STIKes Bogor Husada.
        </p>
    </div>

    <div class="shorts-carousel-wrapper">
        <div class="swiper shortsSwiper">
            <div class="swiper-wrapper">
                @foreach ($landingVideos as $video)
                    <div class="swiper-slide">
                        <div class="shorts-card" data-video="{{ $video->youtube_video_id }}">
                            <div class="shorts-video-frame">
                                <img src="https://img.youtube.com/vi/{{ $video->youtube_video_id }}/hqdefault.jpg"
                                    alt="{{ $video->title }}" class="shorts-thumbnail"
                                    loading="lazy">
                                <div class="shorts-play-btn"><i class='bx bx-play'></i></div>
                            </div>
                            <div class="shorts-card-info">
                                <p class="shorts-title">{{ $video->title }}</p>
                                <small class="text-white-50"><i class='bx bxs-graduation text-danger me-1'></i>STIKes Bogor Husada</small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="swiper-pagination shorts-pagination"></div>
        </div>
        <div class="shorts-nav-prev"><i class='bx bx-chevron-left'></i></div>
        <div class="shorts-nav-next"><i class='bx bx-chevron-right'></i></div>
    </div>
</div>