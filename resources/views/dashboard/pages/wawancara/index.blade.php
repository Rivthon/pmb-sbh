@extends('dashboard.layout.master')
@section('title', 'Form Wawancara PMB')

@section('content')
@php
    $interviewOpen = $interviewOpen ?? true;
    $readonly = $wawancara->status !== 'draft' || !$interviewOpen;
    $statusMeta = match ($wawancara->status) {
        'submitted' => ['label' => 'Menunggu Review', 'tone' => 'info', 'icon' => 'bx-time-five'],
        'reviewed', 'locked' => ['label' => 'Wawancara Selesai', 'tone' => 'success', 'icon' => 'bx-check-circle'],
        default => $interviewOpen
            ? ['label' => 'Perlu Diisi', 'tone' => 'warning', 'icon' => 'bx-edit-alt']
            : ['label' => 'Belum Dibuka', 'tone' => 'info', 'icon' => 'bx-calendar'],
    };
    $questions = [
        ['name' => 'jawaban_1', 'label' => 'Bagaimana Anda menceritakan diri Anda?', 'rows' => 3],
        ['name' => 'jawaban_2', 'label' => 'Dapatkah Anda menceritakan tentang pekerjaan orang tua / wali?', 'rows' => 3],
        ['name' => 'kelebihan', 'label' => 'Apa kelebihan yang Anda miliki?', 'rows' => 2],
        ['name' => 'kekurangan', 'label' => 'Apa kekurangan yang Anda miliki?', 'rows' => 2],
        ['name' => 'jawaban_4', 'label' => 'Anda memilih jurusan apa dan berikan alasannya!', 'rows' => 3],
        ['name' => 'jawaban_5', 'label' => 'Apa yang membuat Anda memilih STIKes Bogor Husada?', 'rows' => 3],
        ['name' => 'jawaban_6', 'label' => 'Bagaimana Anda melihat diri Anda di masa depan?', 'rows' => 3],
    ];
@endphp

<div class="student-page-shell">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h4 class="fw-bold mb-1">Form Wawancara PMB</h4>
            <p class="text-muted mb-0">Jawab pertanyaan berikut dengan jujur dan lengkap sebagai bahan wawancara kaprodi.</p>
        </div>
        <div class="d-flex flex-column flex-sm-row gap-2">
            @include('dashboard.components.student-status-badge', [
                'label' => $statusMeta['label'],
                'tone' => $statusMeta['tone'],
                'icon' => $statusMeta['icon'],
            ])
            @if($wawancara->status !== 'draft')
                <a href="{{ route('dashboard.wawancara.cetak') }}" target="_blank" class="btn btn-primary">
                    <i class="bx bx-printer me-1"></i> Cetak Form
                </a>
            @endif
        </div>
    </div>

    @unless($interviewOpen)
        <div class="alert alert-info d-flex gap-2" role="alert">
            <i class="bx bx-calendar fs-4"></i>
            <div>
                <strong>Form wawancara belum dibuka.</strong>
                <div>Form dapat diisi mulai {{ optional($queue->session?->interview_starts_at)->translatedFormat('d F Y H:i') ?? 'sesuai jadwal panitia' }}.</div>
            </div>
        </div>
    @endunless

    @if($wawancara->status !== 'draft')
        <div class="alert alert-info d-flex gap-2" role="alert">
            <i class="bx bx-info-circle fs-4"></i>
            <div>
                <strong>Form wawancara telah dikirim.</strong>
                <div>Jawaban tidak dapat diubah. Anda dapat mencetak hard copy jika diperlukan.</div>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="bx bx-check-circle me-2"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <div class="fw-semibold mb-1">
                <i class="bx bx-error-circle me-1"></i> Form belum bisa dikirim.
            </div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('dashboard.wawancara.submit') }}" class="student-assessment-form">
        @csrf

        <div class="student-assessment-card">
            <div class="student-assessment-card__head">
                <div>
                    <span class="badge bg-label-info mb-2">Wawancara</span>
                    <h5 class="mb-1">Pertanyaan Calon Mahasiswa</h5>
                    <p class="text-muted mb-0">Minimal 10 karakter untuk jawaban utama. Gunakan kalimat yang jelas.</p>
                </div>
                <div class="student-assessment-count">{{ count($questions) + 1 }} isian</div>
            </div>

            <div class="student-written-grid">
                @foreach($questions as $question)
                    <div class="student-written-question">
                        <label class="student-question-label" for="{{ $question['name'] }}">
                            {{ $loop->iteration }}. {{ $question['label'] }}
                        </label>
                        @if($readonly)
                            <div class="student-readonly-answer">{{ $wawancara->{$question['name']} ?: '-' }}</div>
                        @else
                            <textarea name="{{ $question['name'] }}" id="{{ $question['name'] }}" rows="{{ $question['rows'] }}" class="form-control @error($question['name']) is-invalid @enderror">{{ old($question['name'], $wawancara->{$question['name']} ?? '') }}</textarea>
                            @error($question['name'])
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                @endforeach

                <div class="student-written-question">
                    <label class="student-question-label" for="sumber_informasi">Dari mana Anda mendapatkan informasi STIKes Bogor Husada?</label>
                    @if($readonly)
                        <div class="student-readonly-answer">{{ $wawancara->sumber_informasi ?: '-' }}</div>
                    @else
                        <input type="text" name="sumber_informasi" id="sumber_informasi" class="form-control @error('sumber_informasi') is-invalid @enderror" value="{{ old('sumber_informasi', $wawancara->sumber_informasi) }}" placeholder="Contoh: Instagram, sekolah, teman, keluarga">
                        @error('sumber_informasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <!-- <div class="form-text">Opsional, tetapi membantu panitia mengetahui sumber informasi PMB.</div> -->
                        @enderror
                    @endif
                </div>
            </div>
        </div>

        @if($wawancara->status === 'draft' && $interviewOpen)
            <div class="student-cta-card">
                <div>
                    <span class="badge bg-label-warning mb-2">Konfirmasi</span>
                    <h5 class="mb-1">Kirim Form Wawancara</h5>
                    <p class="text-muted mb-0">Setelah dikirim, jawaban tidak dapat diubah.</p>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-send me-1"></i> Kirim Wawancara
                </button>
            </div>
        @endif
    </form>
</div>
@endsection
