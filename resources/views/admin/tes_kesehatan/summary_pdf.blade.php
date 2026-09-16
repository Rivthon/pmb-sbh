<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rangkuman Laporan Tes Kesehatan</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 11px;
            line-height: 1.35;
        }
        h1 {
            font-size: 17px;
            text-align: center;
            margin: 0 0 4px;
            text-transform: uppercase;
        }
        .meta {
            margin: 12px 0;
            color: #374151;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th,
        td {
            border: 1px solid #111827;
            padding: 6px;
            vertical-align: top;
        }
        th {
            text-align: center;
            background: #f3f4f6;
        }
        .text-center {
            text-align: center;
        }
        .nowrap {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <h1>Rangkuman Laporan Tes Kesehatan</h1>
    <div class="meta">
        <strong>Filter:</strong>
        {{ $selectedRecommendation && isset($recommendationOptions[$selectedRecommendation]) ? $recommendationOptions[$selectedRecommendation] : 'Semua Rekomendasi' }}
        <br>
        <strong>Dicetak:</strong> {{ now()->translatedFormat('d F Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 24%;">Nama</th>
                <th style="width: 18%;">Rekomendasi</th>
                <th>Hasil Tes Kesehatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($anamnesaList as $item)
                @php
                    $exam = $item->pemeriksaan;
                @endphp
                <tr>
                    <td class="text-center nowrap">{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $item->user->name ?? '-' }}</strong><br>
                        {{ $item->user?->jurusan?->nama_jurusan ?? '-' }}
                    </td>
                    <td>{{ $exam?->recommendationLabel() ?? '-' }}</td>
                    <td>{{ $exam ? $exam->healthSummary() : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Tidak ada data tes kesehatan untuk filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
