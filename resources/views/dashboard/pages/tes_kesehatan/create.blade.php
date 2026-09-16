@extends('dashboard.layout.master')
@section('title', 'Form Tes Kesehatan')

@section('content')
@php
    $pertanyaanAnamnesa = [
        'riwayat_penyakit_keluarga' => 'Apakah Anda memiliki riwayat penyakit dalam keluarga, seperti jantung, TBC, diabetes, kanker, hipertensi, asma, atau HIV/AIDS?',
        'riwayat_penyakit_pribadi' => 'Apakah Anda memiliki riwayat penyakit yang pernah Anda derita?',
        'riwayat_operasi' => 'Apakah Anda pernah menjalani tindakan operasi sebelumnya?',
        'konsumsi_obat_rutin' => 'Apakah saat ini Anda mengonsumsi obat secara rutin?',
        'penyakit_menular' => 'Apakah Anda pernah atau sedang menderita penyakit menular seksual, hepatitis B, gonore, atau sejenisnya?',
        'masalah_kulit' => 'Apakah Anda memiliki riwayat masalah kulit seperti eksim, jamur, herpes, kurap, atau kudis?',
        'tumor_benjolan' => 'Apakah Anda memiliki atau pernah didiagnosis tumor, benjolan abnormal, kista, atau kanker?',
        'epilepsi' => 'Apakah Anda memiliki riwayat epilepsi atau kejang-kejang?',
        'cedera_kepala' => 'Apakah Anda pernah mengalami cedera kepala atau gegar otak?',
        'batuk_kronis' => 'Apakah Anda mengalami batuk kronis lebih dari 2 minggu?',
        'gangguan_pencernaan' => 'Apakah Anda memiliki gangguan pencernaan seperti maag atau GERD?',
        'gangguan_keseimbangan' => 'Apakah Anda memiliki gangguan keseimbangan atau koordinasi tubuh?',
        'claustrophobia' => 'Apakah Anda merasa takut berada di ruang sempit atau tertutup?',
        'takut_darah' => 'Apakah Anda merasa takut atau tidak nyaman saat melihat darah?',
        'kacamata' => 'Apakah Anda menggunakan kacamata atau alat bantu penglihatan?',
        'gagap' => 'Apakah Anda memiliki gangguan bicara seperti gagap?',
        'alat_bantu_tulang' => 'Apakah Anda menggunakan alat bantu tulang, sendi, pin, atau penyangga?',
        'lemah_otot' => 'Apakah Anda mengalami kelemahan otot atau gangguan saraf?',
        'pikiran_bunuh_diri' => 'Apakah Anda pernah memiliki percobaan atau pikiran untuk bunuh diri?',
        'kelainan_darah' => 'Apakah Anda memiliki kelainan darah seperti hemofilia, thalassemia, atau perdarahan tanpa sebab?',
        'riwayat_psikolog' => 'Apakah Anda pernah berkonsultasi atau menjalani perawatan dengan psikolog atau psikiater?',
    ];
@endphp

<div class="student-page-shell">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <h4 class="fw-bold mb-1">Form Tes Kesehatan</h4>
            <p class="text-muted mb-0">Isi anamnesa dengan jujur. Jawaban ini membantu petugas memahami kondisi kesehatan awal Anda.</p>
        </div>
        @include('dashboard.components.student-status-badge', [
            'label' => 'Perlu Diisi',
            'tone' => 'warning',
            'icon' => 'bx-time-five',
        ])
    </div>

    @if ($errors->any())
        <div class="alert alert-danger d-flex gap-2" role="alert">
            <i class="bx bx-error-circle fs-4"></i>
            <div>
                <strong>Form belum bisa dikirim.</strong>
                <div>Pastikan semua pertanyaan wajib sudah dijawab.</div>
            </div>
        </div>
    @endif

    <form action="{{ route('dashboard.tes-kesehatan.anamnesa.store') }}" method="POST" class="student-assessment-form">
        @csrf

        <div class="student-assessment-card">
            <div class="student-assessment-card__head">
                <div>
                    <span class="badge bg-label-info mb-2">Anamnesa</span>
                    <h5 class="mb-1">Riwayat Kesehatan</h5>
                    <p class="text-muted mb-0">Pilih Ya atau Tidak untuk setiap pertanyaan berikut.</p>
                </div>
                <div class="student-assessment-count">{{ count($pertanyaanAnamnesa) }} pertanyaan</div>
            </div>

            <div class="student-question-grid">
                @foreach ($pertanyaanAnamnesa as $field => $label)
                    @php($currentAnswer = old($field, data_get($anamnesa, $field)))
                    @php($noteField = "{$field}_keterangan")
                    @php($currentNote = old($noteField, data_get($anamnesa, $noteField)))
                    <div class="student-question-card @error($field) border-danger @enderror">
                        <label class="student-question-label" for="{{ $field }}_ya">
                            {{ $loop->iteration }}. {{ $label }}
                        </label>
                        <div class="student-segmented-radio">
                            <input class="btn-check health-answer-radio" type="radio" name="{{ $field }}" value="1" id="{{ $field }}_ya" data-note-target="{{ $noteField }}" @checked((string) $currentAnswer === '1') required>
                            <label class="btn btn-outline-primary" for="{{ $field }}_ya">Ya</label>

                            <input class="btn-check health-answer-radio" type="radio" name="{{ $field }}" value="0" id="{{ $field }}_tidak" data-note-target="{{ $noteField }}" @checked((string) $currentAnswer === '0') required>
                            <label class="btn btn-outline-secondary" for="{{ $field }}_tidak">Tidak</label>
                        </div>
                        @error($field)
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                        <div class="mt-3 {{ (string) $currentAnswer === '1' ? '' : 'd-none' }}" data-health-note="{{ $noteField }}">
                            <label class="form-label small fw-semibold" for="{{ $noteField }}">Keterangan jawaban Ya</label>
                            <textarea
                                name="{{ $noteField }}"
                                id="{{ $noteField }}"
                                class="form-control @error($noteField) is-invalid @enderror"
                                rows="2"
                                maxlength="255"
                                placeholder="Jelaskan kondisi, waktu kejadian, obat, atau informasi pendukung lainnya..."
                                @if((string) $currentAnswer === '1') required @endif
                            >{{ $currentNote }}</textarea>
                            @error($noteField)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Wajib diisi jika memilih Ya.</div>
                            @enderror
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="student-question-card mt-3">
                <label class="student-question-label" for="keterangan">Keterangan Tambahan</label>
                <textarea name="keterangan" id="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="4" placeholder="Tuliskan kondisi kesehatan lain jika ada...">{{ old('keterangan', $anamnesa?->keterangan) }}</textarea>
                @error('keterangan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="form-text">Opsional. Isi jika ada informasi kesehatan yang perlu diketahui petugas.</div>
                @enderror
            </div>

        </div>

        <div class="student-cta-card">
            <div>
                <span class="badge bg-label-warning mb-2">Konfirmasi</span>
                <h5 class="mb-1">Kirim Anamnesa</h5>
                <p class="text-muted mb-0">Surat kesehatan diunggah terpisah setelah panitia memberi tahu tenggatnya.</p>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bx bx-send me-1"></i> Kirim Formulir
            </button>
        </div>
    </form>
</div>
@endsection

@push('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('.student-assessment-form');
        if (!form) return;

        const syncHealthNote = (radio) => {
            const noteName = radio.dataset.noteTarget;
            if (!noteName) return;

            const noteWrap = form.querySelector(`[data-health-note="${noteName}"]`);
            const noteInput = form.querySelector(`[name="${noteName}"]`);
            if (!noteWrap || !noteInput) return;

            const shouldShow = radio.checked && radio.value === '1';
            noteWrap.classList.toggle('d-none', !shouldShow);
            noteInput.required = shouldShow;
            if (!shouldShow) {
                noteInput.value = '';
            }
        };

        form.querySelectorAll('.health-answer-radio').forEach((radio) => {
            if (radio.checked) syncHealthNote(radio);
            radio.addEventListener('change', () => syncHealthNote(radio));
        });

        if (typeof Swal === 'undefined') return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Konfirmasi Pengisian',
                text: 'Apakah Anda sudah yakin dengan semua jawaban yang telah diisi?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Kirim',
                cancelButtonText: 'Periksa Kembali',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });
    });
</script>
@endpush
