@extends('dashboard.layout.master')
@section('title', 'Dashboard')

@section('content')
    @role(\App\Models\User::ADMIN_ROLE)
        <div class="row" style="row-gap: 15px;">
            <div class="col-lg-8">
                <div class="row" style="row-gap: 15px;">
                    <div class="col-md-6">
                        <a href="{{ route('dashboard.users.user.index') }}" class="card card-hover h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div class="card-info">
                                        <p class="card-text mb-2">Jumlah Pengguna</p>
                                        <div class="d-flex align-items-end mb-2">
                                            <h4 class="card-title mb-0 me-2">{{ $data['count_users'] }}</h4>
                                        </div>
                                    </div>
                                    <div class="card-icon align-self-center">
                                        <span class="badge bg-label-primary rounded p-2">
                                        <i class="bx bx-group bx-sm"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('dashboard.items.item.index') }}" class="card card-hover h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div class="card-info">
                                        <p class="card-text mb-2">Jumlah Barang</p>
                                        <div class="d-flex align-items-end mb-2">
                                            <h4 class="card-title mb-0 me-2">{{ $data['count_items'] }}</h4>
                                        </div>
                                    </div>
                                    <div class="card-icon align-self-center">
                                        <span class="badge bg-label-primary rounded p-2">
                                        <i class="bx bxs-package bx-sm"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('dashboard.supplier.index') }}" class="card card-hover h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div class="card-info">
                                        <p class="card-text mb-2">Jumlah Supplier</p>
                                        <div class="d-flex align-items-end mb-2">
                                            <h4 class="card-title mb-0 me-2">{{ $data['count_suppliers'] }}</h4>
                                        </div>
                                    </div>
                                    <div class="card-icon align-self-center">
                                        <span class="badge bg-label-primary rounded p-2">
                                        <i class="bx bxs-user-account bx-sm"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('dashboard.customer.index') }}" class="card card-hover h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div class="card-info">
                                        <p class="card-text mb-2">Jumlah Pelanggan</p>
                                        <div class="d-flex align-items-end mb-2">
                                            <h4 class="card-title mb-0 me-2">{{ $data['count_items'] }}</h4>
                                        </div>
                                    </div>
                                    <div class="card-icon align-self-center">
                                        <span class="badge bg-label-primary rounded p-2">
                                        <i class="bx bx-user-pin bx-sm"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="d-flex align-items-center justify-content-between pe-4  ">
                                <h5 class="card-header mb-0">List Barang Berdasarkan Transaksi Terbanyak</h5>
                            </div>
                            <div class="table-responsive text-nowrap">
                                <table class="table table-hover">
                                    <caption class="ms-4">
                                        List Barang Berdasarkan Transaksi Terbanyak
                                    </caption>
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Nama Barang</th>
                                            <th>Jumlah (Qty)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['best_items'] as $item)
                                        <tr>
                                            <td>{{ $item->item->code }}</td>
                                            <td>{{ $item->item->name }}</td>
                                            <td>{{ "{$item->total_quantity} {$item->item->unit}" }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <h5 class="card-header text-center">Stok Barang Berdasarkan User</h5>
                    <div class="card-body">
                        <canvas id="categoriesChart" class="chartjs" data-height="100"></canvas>
                        <ul class="doughnut-legend d-flex justify-content-around ps-0 mb-2 mt-4 pt-1 flex-wrap gap-2">
                            @foreach ($data['graph_categories'] as $item)
                                <li class="ct-series-0 d-flex flex-column">
                                    <h5 class="mb-0">{{ $item->user_name }}</h5>
                                    <span class="badge badge-dot my-2 cursor-pointer rounded-pill" style="background-color: rgb(102, 110, 232);width:35px; height:6px;"></span>
                                    <div class="text-muted">{{ $item['percentage'] }} %</div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div>
                            <h5 class="card-title mb-2">Grafik Transaksi Penjualan Berdasarkan User</h5>
                            <h6 class="text-muted mb-2 d-flex align-items-center gap-2">
                                <i class="bx bx-calendar"></i>
                                {{ \Carbon\Carbon::parse($dateRange->start_date)->translatedFormat('F Y') }}
                            </h6>
                        </div>
                        <div class="d-flex flex-row flex-wrap align-items-center gap-3">
                            <form action="" class="d-flex flex-row align-items-center gap-2">
                                @php
                                    $selectedYear = request()->query('year') ?? now()->year;
                                    $selectedMonth = request()->query('month') ?? now()->month;
                                @endphp

                                <select name="month" id="month" class="form-select">
                                    <option value="">Pilih Bulan</option>
                                    @foreach (\App\Constants\Month::LIST_OF_MONTHS as $number => $month)
                                        <option value="{{ $number }}" @selected($selectedMonth  == $number)>{{ $month }}</option>
                                    @endforeach
                                </select>

                                <select name="year" id="year" class="form-select">
                                    <option value="">Pilih Tahun</option>
                                    @for ($i = now()->year; $i > (now()->year - 15); $i--)
                                        <option value="{{ $i }}" @selected($selectedYear == $i)>{{ $i }}</option>
                                    @endfor
                                </select>
                                <button class="btn btn-primary btn-icon" type="submit">
                                    <i class="bx bx-search-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body" style="position: relative;">
                        <canvas id="userSaleChart" class="chartjs" data-height="400"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endrole

    @role(\App\Models\User::USER_ROLE)
        <div class="row" style="row-gap: 15px;">
            <div class="col-lg-8">
                <div class="row" style="row-gap: 15px;">
                    <div class="col-md-6">
                        <a href="{{ route('dashboard.items.item.index') }}" class="card card-hover h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div class="card-info">
                                        <p class="card-text mb-2">Jumlah Barang</p>
                                        <div class="d-flex align-items-end mb-2">
                                            <h4 class="card-title mb-0 me-2">{{ $data['count_items'] }}</h4>
                                        </div>
                                    </div>
                                    <div class="card-icon align-self-center">
                                        <span class="badge bg-label-primary rounded p-2">
                                          <i class="bx bxs-package bx-sm"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('dashboard.supplier.index') }}" class="card card-hover h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div class="card-info">
                                        <p class="card-text mb-2">Jumlah Supplier</p>
                                        <div class="d-flex align-items-end mb-2">
                                            <h4 class="card-title mb-0 me-2">{{ $data['count_suppliers'] }}</h4>
                                        </div>
                                    </div>
                                    <div class="card-icon align-self-center">
                                        <span class="badge bg-label-primary rounded p-2">
                                          <i class="bx bxs-user-account bx-sm"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('dashboard.customer.index') }}" class="card card-hover h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div class="card-info">
                                        <p class="card-text mb-2">Jumlah Pelanggan</p>
                                        <div class="d-flex align-items-end mb-2">
                                            <h4 class="card-title mb-0 me-2">{{ $data['count_items'] }}</h4>
                                        </div>
                                    </div>
                                    <div class="card-icon align-self-center">
                                        <span class="badge bg-label-primary rounded p-2">
                                          <i class="bx bx-user-pin bx-sm"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('dashboard.transaction.sale.index') }}" class="card card-hover h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div class="card-info">
                                        <p class="card-text mb-2">Jumlah Keuntungan</p>
                                        <div class="d-flex align-items-end mb-2">
                                            <h4 class="card-title mb-0 me-2">Rp {{ number_format($data['profits']) }}</h4>
                                        </div>
                                    </div>
                                    <div class="card-icon align-self-center">
                                        <span class="badge bg-label-primary rounded p-2">
                                          <i class="bx bx-dollar bx-sm"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="d-flex align-items-center justify-content-between pe-4  ">
                                <h5 class="card-header mb-0">List Stok Barang Menipis</h5>
                                <a href="{{ route('dashboard.transaction.incoming.create') }}" class="btn btn-icon btn-primary" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" data-bs-original-title="Restok Barang">
                                    <i class="bx bx-refresh"></i>
                                </a>
                            </div>
                            <div class="table-responsive text-nowrap">
                                <table class="table table-hover">
                                    <caption class="ms-4">
                                        List Stok Barang Menipis
                                    </caption>
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Nama Barang</th>
                                            <th>Jumlah Stok</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['warning_items'] as $item)
                                        <tr>
                                            <td>{{ $item->code }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ "{$item->stock} {$item->unit}" }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <h5 class="card-header text-center">Stok Barang Berdasarkan Kategori</h5>
                    <div class="card-body">
                        <canvas id="categoriesChart" class="chartjs" data-height="100"></canvas>
                        <ul class="doughnut-legend d-flex justify-content-around ps-0 mb-2 mt-4 pt-1">
                            @foreach ($data['graph_categories'] as $item)
                                <li class="ct-series-0 d-flex flex-column">
                                    <h5 class="mb-0">{{ $item->category_name }}</h5>
                                    <span class="badge badge-dot my-2 cursor-pointer rounded-pill" style="background-color: rgb(102, 110, 232);width:35px; height:6px;"></span>
                                    <div class="text-muted">{{ $item['percentage'] }} %</div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div>
                            <h5 class="card-title mb-2">Grafik Transaksi Penjualan</h5>
                            <h6 class="text-muted mb-2 d-flex align-items-center gap-2">
                                <i class="bx bx-calendar"></i>
                                {{ \Carbon\Carbon::parse($dateRange->start_date)->translatedFormat('F Y') }}
                            </h6>
                        </div>
                        <div class="d-flex flex-row flex-wrap align-items-center gap-3">
                            <form action="" class="d-flex flex-row align-items-center gap-2">
                                @php
                                    $selectedYear = request()->query('year') ?? now()->year;
                                    $selectedMonth = request()->query('month') ?? now()->month;
                                @endphp

                                <select name="month" id="month" class="form-select">
                                    <option value="">Pilih Bulan</option>
                                    @foreach (\App\Constants\Month::LIST_OF_MONTHS as $number => $month)
                                        <option value="{{ $number }}" @selected($selectedMonth == $number)>{{ $month }}</option>
                                    @endforeach
                                </select>

                                <select name="year" id="year" class="form-select">
                                    <option value="">Pilih Tahun</option>
                                    @for ($i = now()->year; $i > (now()->year - 15); $i--)
                                        <option value="{{ $i }}" @selected($selectedYear == $i)>{{ $i }}</option>
                                    @endfor
                                </select>
                                <button class="btn btn-primary btn-icon" type="submit">
                                    <i class="bx bx-search-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body" style="position: relative;">
                        <div id="lineChart"></div>

                        <div class="resize-triggers">
                            <div class="expand-trigger">
                                <div style="width: 949px; height: 425px;"></div>
                            </div>
                            <div class="contract-trigger"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endrole
@endsection

@role(\App\Models\User::ADMIN_ROLE)
    @push('script')
        {{-- Categories Stock Graph --}}
        <script>
            // Color Variables
            const cyanColor = '#28dac6',
            orangeLightColor = '#FDAC34';
            let cardColor, headingColor, labelColor, borderColor, legendColor;

            let availableColors = @json($data['graph_categories']->pluck('color'));

            if (isDarkStyle) {
                cardColor = config.colors_dark.cardColor;
                headingColor = config.colors_dark.headingColor;
                labelColor = config.colors_dark.textMuted;
                legendColor = config.colors_dark.bodyColor;
                borderColor = config.colors_dark.borderColor;
            } else {
                cardColor = config.colors.cardColor;
                headingColor = config.colors.headingColor;
                labelColor = config.colors.textMuted;
                legendColor = config.colors.bodyColor;
                borderColor = config.colors.borderColor;
            }
            const categoriesChart = document.getElementById('categoriesChart');
            if (categoriesChart) {
                const categoriesChartVar = new Chart(categoriesChart, {
                    type: 'doughnut',
                    data: {
                        labels: @json($data['graph_categories']->pluck('user_name')),
                        datasets: [
                            {
                                data: @json($data['graph_categories']->pluck('percentage')),
                                backgroundColor: availableColors,
                                borderWidth: 0,
                                pointStyle: 'rectRounded'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        animation: {
                            duration: 500
                        },
                        cutout: '68%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                    const label = context.labels || '',
                                        value = context.parsed;
                                    const output = ' ' + label + ' : ' + value + ' %';
                                    return output;
                                    }
                                },
                                // Updated default tooltip UI
                                rtl: isRtl,
                                backgroundColor: cardColor,
                                titleColor: headingColor,
                                bodyColor: legendColor,
                                borderWidth: 1,
                                borderColor: borderColor
                            }
                        }
                    }
                });
            }
        </script>
        
        {{-- Sale Graph --}}
        {{-- <script>
            const lineChartEl = document.querySelector('#lineChart'),
            lineChartConfig = {
                chart: {
                    height: 400,
                    type: 'line',
                    parentHeightOffset: 0,
                    zoom: {
                        enabled: false
                    },
                    toolbar: {
                        show: false
                    }
                },
                series: [
                    {
                        data: @json($data['sale_graphs']['series_data'])
                    }
                ],
                markers: {
                    strokeWidth: 7,
                    strokeOpacity: 1,
                    strokeColors: [config.colors.white],
                    colors: [config.colors.primary]
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'straight'
                },
                colors: [config.colors.primary],
                grid: {
                    borderColor: borderColor,
                    xaxis: {
                        lines: {
                        show: true
                        }
                    },
                    padding: {
                        top: -20,
                        left: 30,
                    }
                },
                tooltip: {
                    custom: function ({ series, seriesIndex, dataPointIndex, w }) {
                        return '<div class="px-3 py-2">' + '<span>' + series[seriesIndex][dataPointIndex] + ' Transaksi</span>' + '</div>';
                    }
                },
                xaxis: {
                    categories: @json($data['sale_graphs']['series_categories']),
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    labels: {
                        style: {
                        colors: labelColor,
                        fontSize: '13px'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                        colors: labelColor,
                        fontSize: '13px'
                        }
                    }
                }
            };

            if (typeof lineChartEl !== undefined && lineChartEl !== null) {
                const lineChart = new ApexCharts(lineChartEl, lineChartConfig);
                lineChart.render();
            }
        </script> --}}

        <script>
            if (isDarkStyle) {
            cardColor = config.colors_dark.cardColor;
                headingColor = config.colors_dark.headingColor;
                labelColor = config.colors_dark.textMuted;
                legendColor = config.colors_dark.bodyColor;
                borderColor = config.colors_dark.borderColor;
            } else {
                cardColor = config.colors.cardColor;
                headingColor = config.colors.headingColor;
                labelColor = config.colors.textMuted;
                legendColor = config.colors.bodyColor;
                borderColor = config.colors.borderColor;
            }
            const userSaleChart = document.getElementById('userSaleChart');

            if (userSaleChart) {
                const userSaleChartVar = new Chart(userSaleChart, {
                    type: 'bar',
                    data: {
                        labels: @json($data['sale_graphs']['series_categories']),
                        datasets: [
                            {
                                data: @json($data['sale_graphs']['series_data']),
                                backgroundColor: config.colors.primary,
                                borderColor: 'transparent',
                                maxBarThickness: 15,
                                borderRadius: {
                                    topRight: 15,
                                    topLeft: 15
                                }
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 500
                        },
                        plugins: {
                            tooltip: {
                                rtl: isRtl,
                                backgroundColor: cardColor,
                                titleColor: headingColor,
                                bodyColor: legendColor,
                                borderWidth: 1,
                                borderColor: borderColor
                            },
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    color: borderColor,
                                    drawBorder: false,
                                    borderColor: borderColor
                                },
                                ticks: {
                                    color: labelColor
                                }
                            },
                            y: {
                                min: 0,
                                max: {{ $data['sale_graphs']['series_data']->max() }},
                                grid: {
                                    color: borderColor,
                                    drawBorder: false,
                                    borderColor: borderColor
                                },
                                ticks: {
                                    stepSize: 15,
                                    color: labelColor
                                }
                            }
                        }
                    }
                });
            }
        </script>
    @endpush
@endrole

@role(\App\Models\User::USER_ROLE)
    @push('script')
        {{-- Categories Stock Graph --}}
        <script>
            // Color Variables
            const cyanColor = '#28dac6',
            orangeLightColor = '#FDAC34';
            let cardColor, headingColor, labelColor, borderColor, legendColor;

            let availableColors = @json($data['graph_categories']->pluck('color'));

            if (isDarkStyle) {
                cardColor = config.colors_dark.cardColor;
                headingColor = config.colors_dark.headingColor;
                labelColor = config.colors_dark.textMuted;
                legendColor = config.colors_dark.bodyColor;
                borderColor = config.colors_dark.borderColor;
            } else {
                cardColor = config.colors.cardColor;
                headingColor = config.colors.headingColor;
                labelColor = config.colors.textMuted;
                legendColor = config.colors.bodyColor;
                borderColor = config.colors.borderColor;
            }
            const categoriesChart = document.getElementById('categoriesChart');
            if (categoriesChart) {
                const categoriesChartVar = new Chart(categoriesChart, {
                    type: 'doughnut',
                    data: {
                        labels: @json($data['graph_categories']->pluck('category_name')),
                        datasets: [
                            {
                                data: @json($data['graph_categories']->pluck('percentage')),
                                backgroundColor: availableColors,
                                borderWidth: 0,
                                pointStyle: 'rectRounded'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        animation: {
                            duration: 500
                        },
                        cutout: '68%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                    const label = context.labels || '',
                                        value = context.parsed;
                                    const output = ' ' + label + ' : ' + value + ' %';
                                    return output;
                                    }
                                },
                                // Updated default tooltip UI
                                rtl: isRtl,
                                backgroundColor: cardColor,
                                titleColor: headingColor,
                                bodyColor: legendColor,
                                borderWidth: 1,
                                borderColor: borderColor
                            }
                        }
                    }
                });
            }
        </script>

        {{-- Sale Graph --}}
        <script>
            const lineChartEl = document.querySelector('#lineChart'),
            lineChartConfig = {
                chart: {
                    height: 400,
                    type: 'line',
                    parentHeightOffset: 0,
                    zoom: {
                        enabled: false
                    },
                    toolbar: {
                        show: false
                    }
                },
                series: [
                    {
                        data: @json($data['sale_graphs']['series_data'])
                    }
                ],
                markers: {
                    strokeWidth: 7,
                    strokeOpacity: 1,
                    strokeColors: [config.colors.white],
                    colors: [config.colors.primary]
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'straight'
                },
                colors: [config.colors.primary],
                grid: {
                    borderColor: borderColor,
                    xaxis: {
                        lines: {
                        show: true
                        }
                    },
                    padding: {
                        top: -20
                    }
                },
                tooltip: {
                    custom: function ({ series, seriesIndex, dataPointIndex, w }) {
                        return '<div class="px-3 py-2">' + '<span>' + series[seriesIndex][dataPointIndex] + ' Transaksi</span>' + '</div>';
                    }
                },
                xaxis: {
                    categories: @json($data['sale_graphs']['series_categories']),
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    labels: {
                        style: {
                        colors: labelColor,
                        fontSize: '13px'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                        colors: labelColor,
                        fontSize: '13px'
                        }
                    }
                }
            };

            if (typeof lineChartEl !== undefined && lineChartEl !== null) {
                const lineChart = new ApexCharts(lineChartEl, lineChartConfig);
                lineChart.render();
            }
        </script>
    @endpush
@endrole