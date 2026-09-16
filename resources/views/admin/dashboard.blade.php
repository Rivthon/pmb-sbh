@extends('admin_dashboard.layout.master')
@section('title', 'Dashboard PMB')

@section('content')

{{-- Global Page Hero --}}
@include('admin_dashboard.components.page-hero', [
    'title' => 'Dashboard Utama',
    'subtitle' => 'Selamat datang di panel admin PMB SBH. Pantau statistik, tren pendaftaran, dan data terkini.',
    'icon' => 'home-alt',
    'breadcrumbs' => [
        ['label' => 'Dashboard']
    ]
])

{{-- Row 1: Stat Cards --}}
<div class="row g-4 mb-4 animate-stagger">
    <!-- Stat Total Pendaftar -->
    <div class="col-md-6 col-lg-4">
        <div class="card admin-card admin-stat-card stat-primary h-100">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <span class="d-block text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Total Pendaftar</span>
                    <h3 class="card-title mb-0 fw-bold display-6 text-primary">{{ $totalMahasiswa }}</h3>
                    <small class="text-success fw-semibold mt-2 d-inline-flex align-items-center gap-1">
                        <i class="bx bx-trending-up"></i> Calon Mahasiswa Baru
                    </small>
                </div>
                <div class="avatar avatar-lg">
                    <span class="avatar-initial rounded-circle bg-label-primary p-3" style="width: 3.5rem; height: 3.5rem; display: flex; align-items: center; justify-content: center; font-size: 1.75rem;">
                        <i class="bx bx-group"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Berdasarkan Jurusan -->
    <div class="col-md-6 col-lg-4">
        <div class="card admin-card admin-stat-card stat-info h-100">
            <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Pendaftar per Jurusan</span>
                <i class="bx bx-bookmark text-info fs-4"></i>
            </div>
            <div class="card-body p-4 pt-2">
                <div class="vstack gap-2">
                    @foreach ($jurusanStats as $jr)
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="d-flex align-items-center" style="font-size: 0.875rem;">
                            <i class="bx {{ $jr['icon'] }} me-2 text-{{ $jr['color'] }} fs-5"></i>
                            <span>{{ $jr['nama'] }}</span>
                        </span>
                        <span class="badge bg-label-{{ $jr['color'] }} rounded-pill px-2 py-1 fw-bold">{{ $jr['count'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Berdasarkan Status -->
    <div class="col-md-6 col-lg-4">
        <div class="card admin-card admin-stat-card stat-success h-100">
            <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Status Registrasi</span>
                <i class="bx bx-check-shield text-success fs-4"></i>
            </div>
            <div class="card-body p-4 pt-2">
                <div class="vstack gap-2">
                    @foreach ($statusStats as $status)
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="d-flex align-items-center" style="font-size: 0.875rem;">
                            <i class="bx {{ $status['icon'] }} me-2 text-{{ $status['color'] }} fs-5"></i>
                            {{ $status['nama'] }}
                        </span>
                        <span class="badge bg-label-{{ $status['color'] }} rounded-pill px-2 py-1 fw-bold">{{ $status['count'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Row 2: Charts --}}
<div class="row g-4 mb-4">
    <!-- Tren Pendaftaran Harian -->
    <div class="col-lg-8">
        <div class="card admin-card h-100">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                <h5 class="card-title mb-0 fw-semibold">
                    <i class='bx bx-line-chart me-2 text-primary'></i>Tren Pendaftaran Harian
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <input type="date" id="startDate" class="form-control form-control-sm" style="width: 120px; font-size: 0.8125rem;">
                    <span class="text-muted" style="font-size: 0.8125rem;">s/d</span>
                    <input type="date" id="endDate" class="form-control form-control-sm" style="width: 120px; font-size: 0.8125rem;">
                    <button id="filterBtn" class="btn btn-sm btn-primary px-3 btn-action" style="font-size: 0.8125rem;"><i class="bx bx-filter-alt"></i></button>
                </div>
            </div>
            <div class="card-body">
                {{-- Chart canvas for registration statistics --}}
                <div id="registrasiChart" style="height: 350px;"></div>
            </div>
        </div>
    </div>

    <!-- Komposisi Prodi -->
    <div class="col-lg-4">
        <div class="card admin-card h-100">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class='bx bx-pie-chart-alt-2 me-2 text-primary'></i>Komposisi Prodi
                    </h5>
                    <small class="text-muted">Berdasarkan gelombang</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <select id="filterGelombang" class="form-select form-select-sm" style="font-size: 0.8125rem; min-width: 110px;">
                        <option value="">Semua Gelombang</option>
                        @foreach ($gelombangs as $gel)
                        <option value="{{ $gel->id }}">{{ $gel->nama_gelombang }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="card-body position-relative d-flex flex-column p-4">
                {{-- Wadah untuk Pie Chart --}}
                <div id="prodiPieChartContainer" class="flex-grow-1 d-flex justify-content-center align-items-center w-100" style="min-height: 220px;">
                    <div id="prodiPieChart" style="min-height: 220px; width: 100%;"></div>
                </div>

                {{-- Wadah untuk Legend/Info Kuota --}}
                <div id="prodiChartLegend" class="pt-3 border-top mt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div id="prodiKuotaInfo" class="d-flex align-items-center w-100"></div>
                    </div>
                </div>

                {{-- Loading & Empty States Overlay --}}
                <div id="prodi-chart-loading" class="position-absolute top-0 start-0 w-100 h-100 d-none justify-content-center align-items-center bg-white bg-opacity-75 rounded" style="z-index: 10;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <span class="ms-2 text-muted" style="font-size: 0.875rem;">Memuat...</span>
                </div>
                <div id="prodi-chart-empty" class="position-absolute top-0 start-0 w-100 h-100 d-none flex-column justify-content-center align-items-center bg-light rounded" style="z-index: 10;">
                    <i class="bx bx-info-circle fs-1 text-muted mb-2"></i>
                    <p class="mb-0 text-muted" style="font-size: 0.875rem;">Data tidak ditemukan</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Row 3: Sumber Informasi Kuesioner --}}
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card admin-card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                <h5 class="card-title mb-0 fw-semibold">
                    <i class='bx bx-bar-chart-square me-2 text-primary'></i>Sumber Informasi Pendaftar (Kuesioner)
                </h5>
                {{-- Filter Group --}}
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <i class="bx bx-filter-alt text-muted" style="font-size: 0.875rem;"></i>
                    <select id="chart-filter-status" class="form-select form-select-sm" style="font-size: 0.8125rem; width: auto;">
                        <option value="" selected>Semua Status</option>
                        <option value="3">Lulus</option>
                        <option value="4">Tidak Lulus</option>
                        <option value="2">Verified</option>
                        <option value="1">Menunggu Validasi</option>
                        <option value="0">Belum Update</option>
                    </select>
                    <select id="chart-filter-periode" class="form-select form-select-sm" style="font-size: 0.8125rem; width: auto;">
                        <option value="">Semua Periode</option>
                        @foreach($periodes as $periode)
                        <option value="{{ $periode->id }}">{{ $periode->deskripsi }}</option>
                        @endforeach
                    </select>
                    <select id="chart-filter-gelombang" class="form-select form-select-sm" style="font-size: 0.8125rem; width: auto;">
                        <option value="">Semua Gelombang</option>
                        @foreach($gelombangs as $gelombang)
                        <option value="{{ $gelombang->id }}">{{ $gelombang->nama_gelombang }}</option>
                        @endforeach
                    </select>
                    <select id="chart-filter-jurusan" class="form-select form-select-sm" style="font-size: 0.8125rem; width: auto;">
                        <option value="">Semua Jurusan</option>
                        @foreach($jurusan as $item)
                        <option value="{{ $item->id }}">{{ $item->nama_jurusan }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="card-body position-relative" style="min-height: 380px;">
                <canvas id="kuesionerChart" style="max-height: 400px; width: 100%;"></canvas>
                {{-- Loading & Empty States --}}
                <div id="chart-loading-overlay" class="position-absolute top-0 start-0 w-100 h-100 d-none justify-content-center align-items-center bg-white bg-opacity-75 rounded" style="z-index: 10;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <span class="ms-2 text-muted">Memuat data...</span>
                </div>
                <div id="chart-empty-state" class="position-absolute top-0 start-0 w-100 h-100 d-none flex-column justify-content-center align-items-center bg-light rounded" style="z-index: 10;">
                    <i class="bx bx-info-circle fs-1 text-muted mb-2"></i>
                    <p class="mb-0 text-muted">Tidak ada data untuk ditampilkan</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Row 4: Recent Activity & Quick Action --}}
<div class="row g-4">
    <!-- Recent Activity -->
    <div class="col-lg-8">
        <div class="card admin-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-semibold">
                    <i class="bx bx-history me-2 text-primary"></i>Aktivitas Terbaru
                </h5>
                <span class="badge bg-label-secondary rounded-pill px-2 py-1">Audit Trail</span>
            </div>
            <div class="card-body p-4">
                @forelse ($recentActivities as $activity)
                    <div class="d-flex gap-3 pb-3 mb-3 border-bottom align-items-start">
                        <span class="avatar avatar-sm flex-shrink-0">
                            <span class="avatar-initial rounded-circle bg-label-primary p-2" style="width: 2.25rem; height: 2.25rem; display: flex; align-items: center; justify-content: center;">
                                <i class="bx bx-shield-quarter"></i>
                            </span>
                        </span>
                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap justify-content-between gap-2 mb-1">
                                <div class="fw-semibold text-dark" style="font-size: 0.9rem;">{{ $activity->description ?? $activity->action }}</div>
                                <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="small text-muted d-flex align-items-center gap-2" style="font-size: 0.75rem;">
                                <span class="badge bg-light text-muted border px-1.5 py-0.5">{{ strtoupper($activity->module) }}</span>
                                <span>• Action: <strong>{{ $activity->action }}</strong></span>
                                @if($activity->ip_address)
                                    <span>• IP: {{ $activity->ip_address }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    @include('admin_dashboard.components.empty-state', [
                        'title' => 'Belum ada aktivitas',
                        'description' => 'Aktivitas admin akan muncul setelah audit trail mulai mencatat.',
                        'icon' => 'bx-history'
                    ])
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Action -->
    <div class="col-lg-4">
        <div class="card admin-card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0 fw-semibold">
                    <i class="bx bx-bolt-circle me-2 text-primary"></i>Aksi Cepat
                </h5>
            </div>
            <div class="list-group list-group-flush p-2">
                <a href="{{ route('admin.mahasiswa-baru.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-0 rounded mb-1 px-3 py-2.5">
                    <span class="d-flex align-items-center gap-2"><i class="bx bx-group text-primary"></i> Data Mahasiswa PMB</span>
                    <i class="bx bx-chevron-right text-muted"></i>
                </a>
                <a href="{{ route('admin.mahasiswa-baru.create') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-0 rounded mb-1 px-3 py-2.5">
                    <span class="d-flex align-items-center gap-2"><i class="bx bx-user-plus text-success"></i> Tambah Mahasiswa Baru</span>
                    <i class="bx bx-chevron-right text-muted"></i>
                </a>
                <a href="{{ route('admin.periode.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-0 rounded mb-1 px-3 py-2.5">
                    <span class="d-flex align-items-center gap-2"><i class="bx bx-calendar text-warning"></i> Kelola Periode PMB</span>
                    <i class="bx bx-chevron-right text-muted"></i>
                </a>
                <a href="{{ route('admin.gelombang.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-0 rounded mb-1 px-3 py-2.5">
                    <span class="d-flex align-items-center gap-2"><i class="bx bx-layer text-info"></i> Kelola Gelombang</span>
                    <i class="bx bx-chevron-right text-muted"></i>
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('script')
{{-- Load Chart.js and DataLabels plugins here only for dashboard page --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // --- Helper: Debounce ---
        const debounce = (func, wait = 300) => {
            let timeout;
            return (...args) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        };

        // --- Element References ---
        const filterElements = {
            status: document.getElementById('chart-filter-status'),
            periode: document.getElementById('chart-filter-periode'),
            gelombang: document.getElementById('chart-filter-gelombang'),
            jurusan: document.getElementById('chart-filter-jurusan')
        };

        const chartCanvas = document.getElementById('kuesionerChart');
        const loadingOverlay = document.getElementById('chart-loading-overlay');
        const emptyState = document.getElementById('chart-empty-state');

        if (!chartCanvas || !loadingOverlay || !emptyState) {
            return;
        }

        const ctx = chartCanvas.getContext('2d');

        // --- Color Configuration ---
        const getCssVar = name => getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        const chartColors = {
            primary: '#696cff',
            success: '#71dd37',
            info: '#03c3ec',
            warning: '#ffab00',
            danger: '#ff3e1d',
            secondary: '#8592a3',
            dark: '#233446'
        };

        // --- Chart Setup ---
        const chartConfig = {
            type: 'bar',
            plugins: [ChartDataLabels],
            data: { labels: [], datasets: [{
                label: 'Jumlah Mahasiswa',
                data: [],
                backgroundColor: [],
                borderRadius: 8,
                borderSkipped: false,
                barPercentage: 0.7,
                categoryPercentage: 0.8
            }] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: chartColors.dark,
                        bodyColor: chartColors.dark,
                        borderColor: 'rgba(0,0,0,0.1)',
                        borderWidth: 1,
                        padding: 12,
                        boxPadding: 4,
                        callbacks: {
                            label: (ctx) => `${ctx.dataset.label}: ${ctx.raw}`
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        formatter: Math.round,
                        color: chartColors.dark,
                        font: { weight: 'bold' }
                    }
                },
                scales: {
                    x: { grid: { display: false, drawBorder: false }, ticks: { color: chartColors.secondary }},
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false }, ticks: { color: chartColors.secondary, precision: 0 }}
                }
            }
        };

        const kuesionerChart = new Chart(ctx, chartConfig);

        // --- Helper UI ---
        const showLoading = () => {
            loadingOverlay.classList.remove('d-none');
            loadingOverlay.classList.add('d-flex');
            emptyState.classList.add('d-none');
        };

        const hideLoading = () => {
            loadingOverlay.classList.add('d-none');
            loadingOverlay.classList.remove('d-flex');
        };

        const showEmptyState = () => {
            emptyState.classList.remove('d-none');
            emptyState.classList.add('d-flex');
            kuesionerChart.data.labels = [];
            kuesionerChart.data.datasets[0].data = [];
            kuesionerChart.update();
        };

        const showErrorState = (message = 'Gagal memuat data') => {
            emptyState.innerHTML = `
                <div class="text-center p-4">
                    <i class="bx bx-error-alt fs-1 text-danger mb-3"></i>
                    <p class="mb-0 text-muted">${message}</p>
                    <button id="retry-btn" class="btn btn-sm btn-primary mt-3 btn-action">Coba Lagi</button>
                </div>`;
            emptyState.classList.remove('d-none');
            document.getElementById('retry-btn').addEventListener('click', fetchChartData);
        };

        // --- Data Processing ---
        const processChartData = (data = []) => data.map(item => ({
            kuesioner_id: item.kuesioner_id ?? 0,
            nama_kusioner: item.nama_kusioner ?? 'Unknown',
            total: item.total ?? 0
        }));

        const updateChartData = (chartData) => {
            const colors = Object.values(chartColors);
            kuesionerChart.data = {
                labels: chartData.map(i => i.nama_kusioner),
                datasets: [{
                    ...kuesionerChart.data.datasets[0],
                    data: chartData.map(i => i.total),
                    backgroundColor: chartData.map((i, idx) => i.total > 0 ? colors[idx % colors.length] : '#e9ecef')
                }]
            };
            kuesionerChart.update();
        };

        // --- Fetch Data ---
        const fetchChartData = debounce(async () => {
            try {
                showLoading();

                const params = new URLSearchParams();
                Object.entries(filterElements).forEach(([key, el]) => {
                    if (el && el.value) params.append(key, el.value);
                });

                const res = await fetch(`{{ url('/admin/api/charts/kuesioner') }}?${params.toString()}`);
                const contentType = res.headers.get('Content-Type') || '';

                if (!res.ok) {
                    const errMsg = contentType.includes('json')
                        ? (await res.json()).message
                        : `HTTP ${res.status}`;
                    throw new Error(errMsg);
                }

                const json = contentType.includes('json') ? await res.json() : [];
                const data = Array.isArray(json.data) ? json.data : json;

                if (!data.length) return showEmptyState();

                updateChartData(processChartData(data));
            } catch (e) {
                console.error('Chart Load Error:', e);
                showErrorState(e.message);
            } finally {
                hideLoading();
            }
        }, 300);

        // --- Bind Filters ---
        Object.values(filterElements)
            .filter(Boolean)
            .forEach(f => f.addEventListener('change', fetchChartData));

        // --- Initial Load ---
        fetchChartData();

        // --- Responsive Resize ---
        window.addEventListener('resize', debounce(() => kuesionerChart.resize(), 200));

    });

    // === APEX CHARTS FOR REGISTRASI & PRODI ===
    document.addEventListener('DOMContentLoaded', function() {
        const chartEl = document.querySelector("#registrasiChart");

        // data awal dari backend
        let initialLabels = @json($bulanLabels);
        let initialData = Array(12).fill(0);

        // inisialisasi chart registrasi
        let registrasiChart = new ApexCharts(chartEl, {
            chart: {
                type: 'line',
                height: 350,
                toolbar: { show: false },
                zoom: { enabled: false }
            },
            stroke: {
                width: 4,
                curve: 'smooth'
            },
            markers: {
                size: 5,
                hover: { size: 7 }
            },
            colors: ['#696cff'],
            grid: {
                borderColor: '#e9ecef',
                strokeDashArray: 4,
                padding: { right: 20, left: 10 }
            },
            series: [{
                name: 'Total Registrasi',
                data: initialData
            }],
            xaxis: {
                categories: initialLabels,
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                title: { text: 'Jumlah Mahasiswa', style: { color: '#8592a3', fontWeight: 500 } },
                min: 0,
                labels: {
                    formatter: val => Math.round(val)
                }
            },
            tooltip: {
                y: { formatter: val => val + ' orang' }
            }
        });

        registrasiChart.render();

        // fungsi untuk ambil data via AJAX
        function loadChartData(start = null, end = null) {
            let url = `{{ route('admin.dashboard.registrasi-data') }}?`;
            if (start && end) {
                url += `start=${start}&end=${end}`;
            }

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    registrasiChart.updateOptions({
                        xaxis: { categories: data.labels }
                    });
                    registrasiChart.updateSeries([{
                        name: 'Total Registrasi',
                        data: data.data
                    }]);
                })
                .catch(err => console.error('Error:', err));
        }

        // event klik tombol filter
        document.getElementById('filterBtn').addEventListener('click', () => {
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;

            if (!start || !end) {
                alert('Silakan isi tanggal mulai dan akhir.');
                return;
            }

            loadChartData(start, end);
        });

        // pertama kali load
        loadChartData();
    });

    // === PIE CHART PRODI ===
    document.addEventListener('DOMContentLoaded', function() {
        const prodiChartEl = document.querySelector("#prodiPieChart");
        let prodiChart = null;

        // fungsi untuk load data prodi dari controller
        function loadProdiChart(gelombangId = '') {
            const loadingOverlay = document.getElementById('prodi-chart-loading');
            const emptyOverlay = document.getElementById('prodi-chart-empty');

            loadingOverlay.classList.remove('d-none');
            emptyOverlay.classList.add('d-none');

            let url = `{{ route('admin.dashboard.prodi-chart') }}`;
            if (gelombangId) url += `?gelombang_id=${gelombangId}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    loadingOverlay.classList.add('d-none');
                    if (!data.data || data.data.length === 0 || data.data.reduce((a, b) => a + b, 0) === 0) {
                        emptyOverlay.classList.remove('d-none');
                        if (prodiChart) {
                            prodiChart.destroy();
                            prodiChart = null;
                        }
                        document.getElementById('prodiKuotaInfo').innerHTML = '';
                        return;
                    }

                    const options = {
                        series: data.data,
                        chart: {
                            height: 280,
                            type: 'pie'
                        },
                        labels: data.labels,
                        colors: data.colors || ['#696cff', '#03c3ec', '#71dd37', '#ffab00', '#ff3e1d'],
                        legend: {
                            position: 'bottom',
                            fontSize: '12px',
                            horizontalAlign: 'center',
                            itemMargin: { horizontal: 5, vertical: 2 }
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: (val, opts) => {
                                const total = data.data.reduce((a, b) => a + b, 0);
                                const jumlah = data.data[opts.seriesIndex];
                                const persen = ((jumlah / total) * 100).toFixed(1);
                                return `${persen}%`;
                            }
                        },
                        tooltip: {
                            y: {
                                formatter: val => `${val} Pendaftar`
                            }
                        }
                    };

                    if (prodiChart) {
                        prodiChart.destroy();
                    }
                    prodiChart = new ApexCharts(prodiChartEl, options);
                    prodiChart.render();

                    // update kuota info
                    let infoHTML = '<div class="d-flex justify-content-center flex-wrap gap-2 w-100">';
                    data.labels.forEach((label, i) => {
                        const jumlah = data.data[i];
                        const target = data.kuota[i] || 0;
                        const diff = target - jumlah;
                        const badgeClass = diff > 0 ? 'bg-label-warning' : 'bg-label-success';
                        infoHTML += `
                        <span class="badge ${badgeClass} text-capitalize" style="font-size: 0.75rem; border: 1px solid currentColor;">
                            ${label}: <strong>${jumlah}/${target}</strong>
                        </span>
                        `;
                    });
                    infoHTML += '</div>';
                    document.getElementById('prodiKuotaInfo').innerHTML = infoHTML;
                })
                .catch(err => {
                    console.error('Error prodi chart:', err);
                    loadingOverlay.classList.add('d-none');
                    emptyOverlay.classList.remove('d-none');
                });
        }

        // event filter
        document.getElementById('filterGelombang').addEventListener('change', function() {
            const gelId = this.value;
            loadProdiChart(gelId);
        });

        // load default data
        loadProdiChart();
    });
</script>
@endpush
