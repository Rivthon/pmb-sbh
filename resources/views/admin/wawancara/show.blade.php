@extends('admin_dashboard.layout.master')

@section('content')
<div class="container-fluid">

    {{-- =========================
    HEADER
    ========================= --}}
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Detail Wawancara PMB</h4>
        <p class="text-muted mb-2">
            Detail jawaban wawancara calon mahasiswa.
        </p>

        @if ($wawancara->status === 'submitted')
        <span class="badge bg-warning">Menunggu Review</span>
        @elseif ($wawancara->status === 'reviewed')
        <span class="badge bg-info">Sudah Direview</span>
        @elseif ($wawancara->status === 'locked')
        <span class="badge bg-success">Final</span>
        @endif
    </div>

    {{-- =========================
    ALERT
    ========================= --}}
    @if(session('success'))
    <div class="alert alert-success d-flex align-items-center">
        <i class="bx bx-check-circle me-2"></i>
        {{ session('success') }}
    </div>
    @endif

    <div class="row g-4">

        {{-- =========================
        DATA MAHASISWA
        ========================= --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Data Calon Mahasiswa</h6>

                    <div class="mb-3">
                        <small class="text-muted">Nama Lengkap</small>
                        <div class="fw-semibold">
                            {{ $wawancara->user->name ?? '-' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Email</small>
                        <div>{{ $wawancara->user->email ?? '-' }}</div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Program Studi</small>
                        <div>{{ $wawancara->user->jurusan->nama_jurusan ?? '-' }}</div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Tanggal Submit</small>
                        <div>{{ $wawancara->updated_at?->format('d M Y H:i') }}</div>
                    </div>

                    <div>
                        <small class="text-muted">Reviewer</small>
                        <div>{{ $wawancara->kaprodi->name ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- =========================
        JAWABAN WAWANCARA
        ========================= --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h6 class="fw-bold mb-4">Jawaban Wawancara</h6>

                    {{-- 1 --}}
                    <div class="mb-4">
                        <div class="fw-semibold mb-1">
                            1. Bagaimana Anda menceritakan diri Anda?
                        </div>
                        <div class="lh-lg text-muted">
                            {{ $wawancara->jawaban_1 ?? '-' }}
                        </div>
                    </div>

                    <hr>

                    {{-- 2 --}}
                    <div class="mb-4">
                        <div class="fw-semibold mb-1">
                            2. Cerita tentang pekerjaan orang tua / wali
                        </div>
                        <div class="lh-lg text-muted">
                            {{ $wawancara->jawaban_2 ?? '-' }}
                        </div>
                    </div>

                    <hr>

                    {{-- 3 --}}
                    <div class="mb-3">
                        <div class="fw-semibold mb-1">3. Kelebihan</div>
                        <div class="lh-lg text-muted">
                            {{ $wawancara->kelebihan ?? '-' }}
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="fw-semibold mb-1">Kekurangan</div>
                        <div class="lh-lg text-muted">
                            {{ $wawancara->kekurangan ?? '-' }}
                        </div>
                    </div>

                    <hr>

                    {{-- 4 --}}
                    <div class="mb-4">
                        <div class="fw-semibold mb-1">
                            4. Jurusan yang dipilih dan alasannya
                        </div>
                        <div class="lh-lg text-muted">
                            {{ $wawancara->jawaban_4 ?? '-' }}
                        </div>
                    </div>

                    <hr>

                    {{-- 5 --}}
                    <div class="mb-4">
                        <div class="fw-semibold mb-1">
                            5. Alasan memilih STIKes Bogor Husada
                        </div>
                        <div class="lh-lg text-muted">
                            {{ $wawancara->jawaban_5 ?? '-' }}
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="fw-semibold mb-1">Sumber Informasi Kampus</div>
                        <div class="lh-lg text-muted">
                            {{ $wawancara->sumber_informasi ?? '-' }}
                        </div>
                    </div>

                    <hr>

                    {{-- 6 --}}
                    <div class="mb-0">
                        <div class="fw-semibold mb-1">
                            6. Gambaran diri di masa depan
                        </div>
                        <div class="lh-lg text-muted">
                            {{ $wawancara->jawaban_6 ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- =========================
    HASIL REVIEW KAPRODI
    ========================= --}}
    @if(in_array($wawancara->status, ['reviewed', 'locked']))
    <div class="card mt-4 border-info">
        <div class="card-body">
            <h6 class="fw-bold mb-3 text-info">
                Hasil Review Kaprodi
            </h6>

            <div class="mb-3">
                <small class="text-muted">Kesimpulan Kaprodi</small>
                <div class="fw-semibold lh-lg">
                    {{ $wawancara->kesimpulan_kaprodi }}
                </div>
            </div>

            <div class="mb-3">
                <small class="text-muted">Rekomendasi</small>
                <div>
                    @if($wawancara->rekomendasi === 'direkomendasikan')
                    <span class="badge bg-success">Direkomendasikan</span>
                    @elseif($wawancara->rekomendasi === 'direkomendasikan_bersyarat')
                    <span class="badge bg-warning text-dark">
                        Direkomendasikan Bersyarat
                    </span>
                    @elseif($wawancara->rekomendasi === 'tidak_direkomendasikan')
                    <span class="badge bg-danger">
                        Tidak Direkomendasikan
                    </span>
                    @endif
                </div>
            </div>

            <small class="text-muted">
                Direview oleh
                <strong>{{ $wawancara->kaprodi->name ?? '-' }}</strong>
                • {{ $wawancara->tanggal_review?->format('d M Y H:i') }}
            </small>
        </div>
    </div>
    @endif

    {{-- =========================
    ACTION BUTTON
    ========================= --}}
    <div class="mt-4 d-flex justify-content-between">
        <!-- <a href="{{ route('admin.wawancara.index') }}" class="btn btn-outline-secondary">
            Kembali
        </a> -->

        @if ($wawancara->status === 'submitted')
        <a href="{{ route('admin.wawancara.review.form', $wawancara) }}" class="btn btn-primary">
            Review Wawancara
        </a>
        @endif
    </div>

</div>
@endsection