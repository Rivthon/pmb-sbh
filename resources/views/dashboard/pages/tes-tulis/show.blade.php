@extends('dashboard.layout.master')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<style>
    .sidebar-sticky {
        position: sticky;
        top: 20px;
    }

    .test-topbar {
        position: sticky;
        top: 0;
        z-index: 1020;
        border: 1px solid #d9e2ec;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .08);
    }

    .test-progress {
        min-width: 170px;
    }

    .story-card {
        border: 1px solid #bfdbfe;
        background: #f8fbff;
    }

    .story-text {
        color: #334155;
        line-height: 1.7;
        white-space: pre-line;
    }

    .answer-option {
        border: 1px solid #e0e0e0;
        border-radius: .5rem;
        padding: 1rem 1.25rem;
        margin-bottom: 1rem;
        cursor: pointer;
        transition: all .2s;
        display: flex;
        align-items: center;
        gap: .85rem;
    }

    .answer-option:hover {
        background-color: #f8f9fa;
        border-color: #0d6efd;
    }

    .answer-option.selected {
        background-color: #dcfce7;
        border: 2px solid #16a34a !important;
        box-shadow: 0 10px 24px rgba(22, 163, 74, .15);
        color: #0f172a;
    }

    .answer-option .badge {
        font-size: 1rem;
        padding: .5rem .75rem;
        border-radius: .4rem;
        min-width: 38px;
        text-align: center;
    }

    .answer-option.selected .badge {
        background-color: #16a34a !important;
        color: #ffffff;
    }

    .question-nav a {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        transition: .2s;
    }

    @keyframes blink {
        50% {
            opacity: 0;
        }
    }

    @media (max-width: 768px) {
        .test-topbar {
            margin-left: -12px;
            margin-right: -12px;
            border-radius: 0;
        }

        .test-topbar .btn {
            width: 100%;
        }

        .test-progress {
            width: 100%;
        }

        .answer-option {
            align-items: flex-start;
            padding: .9rem;
        }

        .answer-option .badge {
            margin-bottom: 0;
        }

        .sidebar-sticky {
            position: static !important;
            margin-top: 1rem;
        }

        .card-body.text-center #timer {
            font-size: 2rem;
        }

        .btn {
            font-size: 0.9rem !important;
        }

        .card .d-flex.flex-wrap.gap-2 button {
            width: 35px !important;
            height: 35px !important;
            font-size: 0.85rem;
        }
    }
</style>

<meta name="csrf-token" content="{{ csrf_token() }}">

@section('content')
<div class="container-fluid py-4" x-data="tesApp()"
    x-init="init(@js($soal), {{ $sisaWaktu ?? ($tesTulis->durasi_menit * 60) }}, @js($kategoriList))">

    <form id="formTes" action="{{ route('dashboard.tes-tulis.submit', $tesTulis->id) }}" method="POST"
        @submit.prevent="confirmSubmit">
        @csrf

        <div class="card test-topbar mb-4">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3 p-3 p-md-4">
                <div class="flex-grow-1">
                    <div class="text-muted small">Tes Tulis Online</div>
                    <h3 class="fw-bold mb-0 text-truncate">{{ $tesTulis->nama_tes }}</h3>
                </div>
                <div class="test-progress">
                    <div class="d-flex justify-content-between align-items-center small text-muted mb-1">
                        <span>Progres</span>
                        <strong class="text-dark"><span x-text="answeredCount"></span>/<span x-text="totalQuestions"></span></strong>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" :style="`width: ${progressPercent}%`"></div>
                    </div>
                </div>
                <div class="text-md-end">
                    <div class="text-muted small">Sisa Waktu</div>
                    <div id="timer" class="h3 fw-bold text-primary mb-0" x-text="formattedTime"></div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- LEFT SIDE --}}
            <div class="col-lg-8">

                <template x-if="currentKategori && currentKategori.cerita_bacaan && !currentKategoriHasQuestionPassages">
                    <div class="card story-card shadow-sm mb-4">
                        <div class="card-body p-4">
                            <span class="badge bg-info text-dark mb-2">Bacaan</span>
                            <h5 class="fw-bold mb-3" x-text="'Bacaan ' + currentKategori.nama"></h5>
                            <div class="story-text" x-text="currentKategori.cerita_bacaan"></div>
                        </div>
                    </div>
                    </div>
                </template>

                <!-- ✅ DAFTAR SOAL -->
                <template x-for="(item, index) in filteredSoal" :key="item.id">
                    <div>
                        <template x-if="item.cerita_bacaan">
                            <div class="card story-card shadow-sm mb-4">
                                <div class="card-body p-4">
                                    <span class="badge bg-info text-dark mb-2">Bacaan</span>
                                    <h5 class="fw-bold mb-3" x-text="'Bacaan mulai soal ' + item.nomor"></h5>
                                    <div class="story-text" x-text="item.cerita_bacaan"></div>
                                </div>
                            </div>
                        </template>
                        <div class="card mb-4 shadow-sm border-0" :id="'soal-' + item.id">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start mb-3">
                                <h5 class="fw-semibold me-3" x-text="item.nomor + '.'"></h5>
                                <h5 class="fw-semibold flex-grow-1" x-text="item.pertanyaan"></h5>
                            </div>

                            <div class="ps-4">
                                <template x-for="pilihan in item.pilihan_jawaban" :key="pilihan.id">
                                    <div class="form-check answer-option"
                                        :class="isSelected(item.id, pilihan.id) ? 'selected' : 'border'"
                                        @click="selectJawaban(item.id, pilihan.id)">
                                        <input type="radio" class="form-check-input d-none"
                                            :id="'pilihan-' + item.id + '-' + pilihan.id"
                                            :name="'jawaban[' + item.id + ']'" :value="pilihan.id"
                                            :checked="isSelected(item.id, pilihan.id)" />
                                        <span class="badge bg-primary" x-text="pilihan.kode_pilihan"></span>
                                        <label class="form-check-label flex-grow-1 mb-0"
                                            :for="'pilihan-' + item.id + '-' + pilihan.id"
                                            x-text="pilihan.teks_pilihan"></label>
                                        <i class="bx bx-check-circle fs-3 text-success ms-auto" x-show="isSelected(item.id, pilihan.id)"></i>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>


                <!-- Tombol Next Kategori -->
                <div class="text-center mt-4" x-show="filteredSoal.length > 0">
                    <button type="button" class="btn btn-outline-primary px-4" @click="nextKategori"
                        x-show="!isLastKategori">
                        Lanjut ke Kategori Berikutnya
                        <i class="bx bx-right-arrow-circle ms-2"></i>
                    </button>
                </div>

            </div>

            {{-- RIGHT SIDE --}}
            <div class="col-lg-4">
                <div class="sidebar-sticky">

                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title text-muted mb-3">Kategori Soal</h5>

                            <template x-for="kategori in kategoriList" :key="kategori.id">
                                <div class="mb-3">
                                    <button type="button" class="btn w-100 text-start mb-2"
                                        :class="selectedKategori === kategori.id ? 'btn-primary text-white' : 'btn-outline-secondary'"
                                        @click="filterByKategori(kategori.id)">
                                        <i class="bi bi-folder me-2"></i>
                                        <span x-text="kategori.nama"></span>
                                    </button>

                                    <div class="d-flex flex-wrap gap-2">
                                        <template x-for="(item, idx) in soalByKategori(kategori.id)" :key="item.id">
                                            <button type="button" @click="scrollTo(item.id)"
                                                class="btn position-relative"
                                                :class="jawaban[item.id] ? 'btn-success' : 'btn-outline-secondary'"
                                                style="width:40px;height:40px;">
                                                <span x-text="item.nomor"></span>
                                                <template x-if="jawaban[item.id]">
                                                    <i
                                                        class="bi bi-check-circle-fill text-white position-absolute top-0 end-0 small"></i>
                                                </template>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Tombol Kirim Jawaban -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="text-muted small">Pengiriman Jawaban</div>
                    <h5 class="fw-bold mb-0" x-text="allAnswered ? 'Semua jawaban sudah lengkap' : 'Lengkapi semua jawaban terlebih dahulu'"></h5>
                </div>
                <button type="submit" class="btn btn-lg px-5 shadow-sm"
                    :class="allAnswered ? 'btn-success' : 'btn-secondary'"
                    :disabled="!allAnswered || submitted">
                    <i class="bx bx-send me-2"></i>
                    <span x-text="allAnswered ? 'Kirim Jawaban' : 'Lengkapi Semua Jawaban'"></span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    function tesApp() {
    return {
        soal: [],
        jawaban: {},
        kategoriList: [],
        selectedKategori: null,
        duration: 0,
        formattedTime: '00:00',
        countdown: null,
        submitted: false,
        _boundBeforeUnload: null,

        init(soalData, waktu, kategoriData = []) {
            this.soal = (Array.isArray(soalData) ? soalData : Object.values(soalData || []))
                .map((item, index) => ({
                    ...item,
                    kategori_id: Number(item.kategori_id || 0),
                    cerita_bacaan: item.cerita_bacaan || '',
                    nomor: index + 1,
                }));
            this.duration = Number(waktu) || 0;

            this.kategoriList = (Array.isArray(kategoriData) ? kategoriData : Object.values(kategoriData || []))
                .map((kategori) => ({
                    id: Number(kategori.id),
                    nama: kategori.nama_kategori || kategori.nama || '-',
                    cerita_bacaan: kategori.cerita_bacaan || '',
                }))
                .filter((kategori) => this.soal.some((item) => item.kategori_id === kategori.id));

            if (this.soal.some((item) => item.kategori_id === 0)) {
                this.kategoriList.unshift({ id: 0, nama: 'Tanpa Kategori', cerita_bacaan: '' });
            }

            this.selectedKategori = this.kategoriList[0]?.id ?? null;
            this.updateTimerDisplay();
            this.startTimer();
            this.tryResendPending();
        },

        get totalQuestions() {
            return this.soal.length;
        },

        get answeredCount() {
            return this.soal.filter((item) => this.jawaban[item.id]).length;
        },

        get progressPercent() {
            return this.totalQuestions ? Math.round((this.answeredCount / this.totalQuestions) * 100) : 0;
        },

        get allAnswered() {
            return this.totalQuestions > 0 && this.answeredCount === this.totalQuestions;
        },

        get currentKategori() {
            return this.kategoriList.find((kategori) => kategori.id === this.selectedKategori) || null;
        },

        get currentKategoriHasQuestionPassages() {
            return this.filteredSoal.some((item) => item.cerita_bacaan);
        },

        get isLastKategori() {
            return this.kategoriList.findIndex((kategori) => kategori.id === this.selectedKategori) >= this.kategoriList.length - 1;
        },

        get filteredSoal() {
            return this.soal.filter(s => Number(s.kategori_id) === Number(this.selectedKategori));
        },

        soalByKategori(id) {
            return this.soal.filter(s => Number(s.kategori_id) === Number(id));
        },

        selectJawaban(soalId, pilihanId) {
            if (this.submitted) return;
            this.jawaban = { ...this.jawaban, [soalId]: pilihanId };
        },

        isSelected(soalId, pilihanId) {
            return Number(this.jawaban[soalId]) === Number(pilihanId);
        },

        scrollTo(soalId) {
            const el = document.getElementById(`soal-${soalId}`);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },

        filterByKategori(id) {
            this.selectedKategori = id;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        formatTime(seconds) {
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        },

        updateTimerDisplay() {
            this.formattedTime = this.formatTime(Math.max(this.duration, 0));
            const timerEl = document.getElementById('timer');
            if (!timerEl) return;

            timerEl.classList.remove('text-primary', 'text-warning', 'text-danger');
            timerEl.style.animation = '';

            if (this.duration <= 10) {
                timerEl.classList.add('text-danger');
                timerEl.style.animation = 'blink 1s infinite';
            } else if (this.duration <= 60) {
                timerEl.classList.add('text-danger');
            } else if (this.duration <= 180) {
                timerEl.classList.add('text-warning');
            } else {
                timerEl.classList.add('text-primary');
            }
        },

        startTimer() {
            if (this.countdown) clearInterval(this.countdown);
            this.countdown = setInterval(() => {
                this.duration--;
                this.updateTimerDisplay();
                if (this.duration <= 0) {
                    clearInterval(this.countdown);
                    this.autoSubmit();
                }
            }, 1000);

            this._boundBeforeUnload = (e) => {
                if (!this.submitted) {
                    e.preventDefault();
                    e.returnValue = 'Tes sedang berlangsung! Jika Anda meninggalkan halaman ini, jawaban tidak akan tersimpan.';
                }
            };
            window.addEventListener('beforeunload', this._boundBeforeUnload);
        },

        nextKategori() {
            const currentIndex = this.kategoriList.findIndex(k => k.id === this.selectedKategori);
            if (currentIndex < this.kategoriList.length - 1) {
                this.selectedKategori = this.kategoriList[currentIndex + 1].id;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                Swal.fire({
                    title: 'Semua Kategori Selesai!',
                    text: 'Anda sudah menjawab semua soal, silakan kirim jawaban.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            }
        },

        confirmSubmit() {
            if (this.submitted) return;
            if (!this.allAnswered) {
                Swal.fire({
                    title: 'Jawaban Belum Lengkap',
                    text: `Masih ada ${this.totalQuestions - this.answeredCount} soal yang belum dijawab.`,
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            Swal.fire({
                title: 'Kirim Jawaban?',
                text: 'Pastikan semua jawaban sudah diisi.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, kirim!',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (result.isConfirmed) this.sendAjaxSubmit(false);
            });
        },

        async autoSubmit() {
            if (this.submitted) return;
            this.submitted = true;
            document.querySelectorAll('input[type="radio"]').forEach(el => el.disabled = true);

            Swal.fire({
                title: '⏰ Waktu Habis!',
                text: 'Jawaban Anda sedang dikirim otomatis...',
                icon: 'warning',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });

            await this.sendAjaxSubmit(true);
        },

        async sendAjaxSubmit(isAuto = false) {
            if (this.submitted && !isAuto) return;
            this.submitted = true;
            document.querySelectorAll('input[type="radio"]').forEach(el => el.disabled = true);

            const form = document.getElementById('formTes');
            const action = form.getAttribute('action');
            const token = document.querySelector('meta[name="csrf-token"]').content;

            const formData = new FormData();
            formData.append('_token', token);
            formData.append('is_auto', isAuto ? '1' : '0');
            Object.entries(this.jawaban).forEach(([k, v]) => {
                formData.append(`jawaban[${k}]`, v);
            });

            try {
                const response = await fetch(action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData
                });

                if (response.redirected) {
                    localStorage.removeItem('pendingTes');
                    Swal.fire({
                        title: isAuto ? 'Waktu Habis!' : 'Berhasil!',
                        text: 'Jawaban Anda telah dikirim dan disimpan.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        if (this._boundBeforeUnload)
                            window.removeEventListener('beforeunload', this._boundBeforeUnload);
                        window.location.href = response.url;
                    });
                    return;
                }

                const payload = await response.json();
                if (!response.ok || payload.success === false) {
                    throw new Error(payload.message || 'Jawaban gagal dikirim.');
                }

                localStorage.removeItem('pendingTes');
                Swal.fire({
                    title: isAuto ? 'Waktu Habis!' : 'Berhasil!',
                    text: payload.message || 'Jawaban Anda telah dikirim.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    if (this._boundBeforeUnload)
                        window.removeEventListener('beforeunload', this._boundBeforeUnload);
                    if (payload.redirect) {
                        window.location.href = payload.redirect;
                    }
                });
            } catch (err) {
                console.error('Gagal kirim:', err);
                const pendingData = { jawaban: this.jawaban, is_auto: isAuto };
                localStorage.setItem('pendingTes', JSON.stringify({
                    url: action,
                    data: pendingData
                }));
                this.submitted = false;
                Swal.fire({
                    title: '⚠️ Koneksi Terputus',
                    text: 'Jawaban Anda disimpan sementara dan akan dikirim ulang otomatis saat koneksi kembali.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            }
        },

        async tryResendPending() {
            const saved = localStorage.getItem('pendingTes');
            if (!saved) return;

            const pending = JSON.parse(saved);
            const token = document.querySelector('meta[name="csrf-token"]').content;

            try {
                const formData = new FormData();
                formData.append('_token', token);

                if (pending.data && pending.data.jawaban) {
                    Object.entries(pending.data.jawaban).forEach(([k, v]) => {
                        formData.append(`jawaban[${k}]`, v);
                    });
                }
                formData.append('is_auto', pending.data?.is_auto ? '1' : '0');

                const response = await fetch(pending.url, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData
                });

                if (response.ok) {
                    localStorage.removeItem('pendingTes');
                    Swal.fire({
                        title: '✅ Jawaban Dikirim Ulang',
                        text: 'Data tertunda berhasil dikirim ke server.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    console.warn('Resend gagal:', await response.text());
                }
            } catch (e) {
                console.warn('Gagal kirim ulang data pending:', e);
            }
        }
    };
}

</script>
