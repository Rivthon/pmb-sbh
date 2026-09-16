@extends('dashboard.layout.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y d-flex justify-content-center align-items-center"
    style="min-height: 80vh;">
    <div class="card shadow-lg w-100" style="max-width: 600px;">

        {{-- BANNER HEADER (WARNA DIUBAH MENJADI MERAH) --}}
        <div class="card-header text-center bg-danger">
            <h2 class="text-white mb-1">Pengumuman Hasil Seleksi</h2>
            <p class="text-white-50 mb-0">
                PMB {{ config('app.institution_name', 'STIKes Bogor Husada') }} T.A {{ $tahunAkademik }}
            </p>
        </div>

        {{-- KONTEN UTAMA --}}
        <div class="card-body p-4">
            <p class="mb-2">Assalamualaikum Wr. Wb.</p>
            <p>
                Kepada Yth. Sdr/i <strong class="text-danger">{{ $nama }}</strong>,<br>
                Peserta Tes Seleksi PMB Program Studi <strong>{{ $jurusan }}</strong>.
            </p>

            <hr class="my-4">

            {{-- KALIMAT DISESUAIKAN --}}
            <p>
                Sehubungan dengan telah dilaksanakannya Tes Seleksi PMB pada hari
                @if($tanggalTes)
                <strong>{{ \Carbon\Carbon::parse($tanggalTes)->translatedFormat('l, d F Y') }}</strong>,
                @endif
                dengan berat hati kami informasikan hasil seleksi Anda:
            </p>

            {{-- KOTAK STATUS (DIUBAH MENJADI TIDAK LULUS) --}}
            <div class="text-center p-3 my-4 bg-light-danger" style="border: 1px dashed #ff3e1d; border-radius: 8px;">
                <p class="mb-2">Anda dinyatakan:</p>
                <span class="badge bg-danger fs-5 rounded-pill px-4 py-2">TIDAK LULUS</span>
            </div>

            {{-- PARAGRAF PENUTUP DISESUAIKAN --}}
            <p>
                Kami mengucapkan terima kasih atas partisipasi Anda dalam proses seleksi PMB STIKes Bogor Husada. Jangan
                berkecil hati dan tetap semangat dalam mengejar cita-cita Anda di kesempatan lainnya.
            </p>

            <hr class="my-4">

            {{-- PENUTUP --}}
            <p class="mb-2">Terima kasih atas perhatiannya.</p>
            <p class="mb-2">Wassalamualaikum Wr. Wb.</p>

            <p class="mt-4 mb-0 fw-semibold">- Panitia PMB {{ config('app.institution_name', 'STIKes Bogor Husada') }} -
            </p>
        </div>
    </div>
</div>
@endsection