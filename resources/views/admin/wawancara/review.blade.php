@extends('admin_dashboard.layout.master')

@php
    $isOnlineInterview = isset($queue) && $queue?->isOnlineSelection();
    $pageTitle = $isOnlineInterview ? 'Hasil Wawancara Online' : 'Hasil Wawancara Offline';
    $studentAnswers = [
        'Bagaimana Anda menceritakan diri Anda?' => $wawancara->jawaban_1,
        'Dapatkah Anda menceritakan tentang pekerjaan orang tua / wali?' => $wawancara->jawaban_2,
        'Kelebihan yang Anda miliki' => $wawancara->kelebihan,
        'Kekurangan yang Anda miliki' => $wawancara->kekurangan,
        'Jurusan yang dipilih dan alasannya' => $wawancara->jawaban_4,
        'Alasan memilih STIKes Bogor Husada' => $wawancara->jawaban_5,
        'Sumber informasi STIKes Bogor Husada' => $wawancara->sumber_informasi,
        'Gambaran diri di masa depan' => $wawancara->jawaban_6,
    ];
@endphp

@section('title', $pageTitle)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">{{ $pageTitle }}</h4>
        <p class="text-muted mb-0">
            @if($isOnlineInterview)
                Review jawaban form wawancara yang sudah dikirim mahasiswa, lalu isi catatan dosen dan rekomendasi akhir.
            @else
                Isi catatan wawancara dan rekomendasi akhir. Jika dibuka dari antrean offline, tombol simpan akan menyelesaikan tahap peserta.
            @endif
        </p>

        <span class="badge bg-label-info mt-2">
            Status: {{ ucfirst($wawancara->status) }}
        </span>
    </div>

    @if(session('success'))
    <div class="alert alert-success d-flex align-items-center">
        <i class="bx bx-check-circle me-2"></i>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Data Calon Mahasiswa</h6>

                    <div class="mb-2">
                        <small class="text-muted">Nama</small>
                        <div class="fw-semibold">{{ $wawancara->user->name }}</div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Email</small>
                        <div>{{ $wawancara->user->email }}</div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Program Studi</small>
                        <div>{{ $wawancara->user->jurusan->nama_jurusan ?? '-' }}</div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted">Status Wawancara</small>
                        <div>{{ ucfirst($wawancara->status) }}</div>
                    </div>

                    @if(request('queue_uuid'))
                    <div class="alert alert-primary mt-3 mb-0">
                        @if($isOnlineInterview)
                            Simpan hasil untuk menyelesaikan tahap wawancara online peserta ini.
                        @else
                            Simpan hasil untuk menyelesaikan wawancara dan membuka ruangan bagi peserta berikutnya.
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            @if($isOnlineInterview)
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Jawaban Form Mahasiswa</h6>
                    <p class="text-muted mb-4">
                        Gunakan jawaban berikut sebagai bahan review sebelum menulis kesimpulan dosen.
                    </p>

                    @foreach($studentAnswers as $question => $answer)
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="fw-semibold mb-1">{{ $loop->iteration }}. {{ $question }}</div>
                            <div class="text-muted lh-lg">{{ filled($answer) ? $answer : '-' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Panduan Wawancara</h6>
                    <p class="text-muted mb-3">
                        Gunakan poin berikut sebagai bahan tanya jawab offline, lalu tuliskan kesimpulan dosen pada form hasil.
                    </p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-semibold mb-1">Motivasi Kuliah</div>
                                <div class="text-muted small">Alasan memilih kampus dan program studi.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-semibold mb-1">Kesiapan Belajar</div>
                                <div class="text-muted small">Komitmen, dukungan keluarga, dan kesiapan mengikuti perkuliahan.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-semibold mb-1">Komunikasi</div>
                                <div class="text-muted small">Cara peserta menjawab, menjelaskan diri, dan berdiskusi.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-semibold mb-1">Kesesuaian Prodi</div>
                                <div class="text-muted small">Kecocokan minat dan karakter peserta dengan pilihan program studi.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(in_array($wawancara->status, ['submitted','reviewed'], true))
            <form method="POST" action="{{ route('admin.wawancara.review', $wawancara) }}">
                @csrf
                @if(request('queue_uuid'))
                <input type="hidden" name="queue_uuid" value="{{ request('queue_uuid') }}">
                @endif

                <div class="card">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Catatan & Rekomendasi Dosen</h6>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Catatan/Kesimpulan Wawancara</label>
                            <textarea name="kesimpulan_kaprodi" rows="5" class="form-control @error('kesimpulan_kaprodi') is-invalid @enderror" placeholder="Tuliskan hasil tanya jawab, catatan penting, atau pertimbangan dosen">{{ old('kesimpulan_kaprodi', $wawancara->kesimpulan_kaprodi) }}</textarea>
                            @error('kesimpulan_kaprodi')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Rekomendasi</label>
                            <select name="rekomendasi" class="form-select @error('rekomendasi') is-invalid @enderror">
                                <option value="">-- Pilih Rekomendasi --</option>
                                <option value="direkomendasikan" @selected(old('rekomendasi', $wawancara->rekomendasi) === 'direkomendasikan')>
                                    Direkomendasikan
                                </option>
                                <option value="direkomendasikan_bersyarat" @selected(old('rekomendasi', $wawancara->rekomendasi) === 'direkomendasikan_bersyarat')>
                                    Direkomendasikan Bersyarat
                                </option>
                                <option value="tidak_direkomendasikan" @selected(old('rekomendasi', $wawancara->rekomendasi) === 'tidak_direkomendasikan')>
                                    Tidak Direkomendasikan
                                </option>
                            </select>
                            @error('rekomendasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between gap-2 flex-wrap">
                            <a href="{{ request('queue_uuid') ? url()->previous() : route('admin.wawancara.index') }}" class="btn btn-outline-secondary">
                                Kembali
                            </a>

                            @if($wawancara->status === 'submitted')
                            <button type="submit" class="btn btn-primary">
                                {{ request('queue_uuid') ? 'Simpan & Selesaikan Wawancara' : 'Simpan Review' }}
                            </button>
                            @else
                            <a href="{{ route('admin.wawancara.show', $wawancara) }}" class="btn btn-outline-primary">
                                Lihat Hasil
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
            @else
            <div class="alert alert-warning mb-0">
                Wawancara tidak dapat diedit pada status saat ini.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
