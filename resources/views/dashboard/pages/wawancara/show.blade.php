@extends('dashboard.layout.master')
@section('title', 'Hasil Wawancara PMB')

@section('content')
@php
    $statusMeta = match ($wawancara->status) {
        'submitted' => ['label' => 'Menunggu Review', 'tone' => 'info', 'icon' => 'bx-time-five'],
        'reviewed', 'locked' => ['label' => 'Wawancara Selesai', 'tone' => 'success', 'icon' => 'bx-check-circle'],
        default => ['label' => ucfirst($wawancara->status), 'tone' => 'warning', 'icon' => 'bx-edit-alt'],
    };
    $answers = [
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

<div class="student-page-shell">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h4 class="fw-bold mb-1">Hasil Wawancara PMB</h4>
            <p class="text-muted mb-0">Berikut jawaban wawancara yang telah Anda kirimkan.</p>
        </div>
        <div class="d-flex flex-column flex-sm-row gap-2">
            @include('dashboard.components.student-status-badge', [
                'label' => $statusMeta['label'],
                'tone' => $statusMeta['tone'],
                'icon' => $statusMeta['icon'],
            ])
            <a href="{{ route('dashboard.wawancara.cetak') }}" target="_blank" class="btn btn-primary">
                <i class="bx bx-printer me-1"></i> Cetak Form
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="bx bx-check-circle me-2"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <div class="student-summary-band">
        <div>
            <span>Status Wawancara</span>
            <strong>{{ $statusMeta['label'] }}</strong>
        </div>
        <div>
            <span>Tanggal Kirim</span>
            <strong>{{ $wawancara->updated_at?->translatedFormat('d F Y H:i') ?? '-' }}</strong>
        </div>
        <div>
            <span>Program Studi</span>
            <strong>{{ $wawancara->user->jurusan->nama_jurusan ?? '-' }}</strong>
        </div>
    </div>

    <div class="student-assessment-card">
        <div class="student-assessment-card__head">
            <div>
                <span class="badge bg-label-info mb-2">Jawaban</span>
                <h5 class="mb-1">Form Wawancara</h5>
                <p class="text-muted mb-0">Jawaban ini sudah tersimpan di sistem PMB.</p>
            </div>
        </div>

        <div class="student-written-grid">
            @foreach($answers as $label => $answer)
                <div class="student-written-question">
                    <div class="student-question-label">{{ $loop->iteration }}. {{ $label }}</div>
                    <div class="student-readonly-answer">{{ $answer ?: '-' }}</div>
                </div>
            @endforeach
        </div>
    </div>

    @if(in_array($wawancara->status, ['reviewed', 'locked'], true))
        <div class="student-assessment-card">
            <div class="student-assessment-card__head">
                <div>
                    <span class="badge bg-label-success mb-2">Review Kaprodi</span>
                    <h5 class="mb-1">Hasil Wawancara</h5>
                </div>
            </div>
            <div class="student-summary-band mb-0">
                <div>
                    <span>Kesimpulan</span>
                    <strong>{{ $wawancara->kesimpulan_kaprodi ?: '-' }}</strong>
                </div>
                <div>
                    <span>Rekomendasi</span>
                    <strong>{{ ucfirst(str_replace('_', ' ', $wawancara->rekomendasi ?? '-')) }}</strong>
                </div>
                <div>
                    <span>Reviewer</span>
                    <strong>{{ $wawancara->pewawancara->name ?? '-' }}</strong>
                </div>
            </div>
        </div>
    @endif

    <div class="d-flex justify-content-end">
        <a href="{{ route('dashboard.index') }}" class="btn btn-outline-secondary">
            Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection
