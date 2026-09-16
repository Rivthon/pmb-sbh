@extends('layouts.app_berita')

@section('content')
<section class="position-relative overflow-hidden py-5" style="min-height: 300px;">
    <img src="{{ asset('dashboard_assets/assets/img/front-pages/landing-page/header.png') }}" alt="Header Kebidanan"
        class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover z-n1" style="opacity: 0.6;" />
    <div class="text-center text-white position-relative z-1" style="margin-top: 8rem;">
        <h1 class="display-4 fw-bold">Program Studi Diploma Tiga Kebidanan</h1>
        <p class="lead mb-0 text-dark">Menghasilkan bidan yang unggul, humanis, dan mampu memberikan pelayanan kesehatan ibu dan anak</p>
    </div>
</section>


<div class="container-xxl flex-grow-1 container-p-y">

    <div class="row">
        <div class="col-lg-12 mx-auto">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="position-relative mb-4">
                        <div class="ratio ratio-16x9">
                            <iframe id="heroVideo"
                                src="https://www.youtube.com/embed/JKkfJVV4RHQ?start=196&enablejsapi=1&rel=0&autoplay=1&mute=1&controls=0&modestbranding=1"
                                title="Video Profil Prodi" allow="autoplay; encrypted-media" allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                    {{-- <h3 class="card-title fw-bold mb-2">{{ $data['nama'] ?? 'Program Studi' }}</h3> --}}
                    {{-- <h4 class="mb-3">{{ $page['title']['rendered'] }}</h4> --}}
                    <div class="mb-4">
                        {!! $page['content']['rendered'] !!}
                    </div>
                    <a href="/" class="btn btn-outline-warning">
                        <i class="bx bx-arrow-back"></i> Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
        @include('pages.prodi')
    </div>

</div>
@endsection