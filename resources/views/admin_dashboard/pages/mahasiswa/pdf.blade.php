<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Data Calon Mahasiswa PMB</title>
    <style>
        /* PDF Global Styling */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8px;
            color: #2D3748;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }

        /* Letterhead / Kop Laporan */
        .kop-container {
            border-bottom: 2px solid #1A202C;
            padding-bottom: 8px;
            margin-bottom: 12px;
            position: relative;
        }

        .kop-logo {
            position: absolute;
            top: 0;
            left: 0;
            width: 70px;
            height: auto;
        }

        .kop-text {
            text-align: center;
            padding-left: 70px;
            padding-right: 70px;
        }

        .kop-title {
            font-size: 14px;
            font-weight: bold;
            color: #4F46E5;
            margin: 0 0 2px 0;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .kop-sub {
            font-size: 10px;
            font-weight: bold;
            color: #1A202C;
            margin: 0 0 4px 0;
            text-transform: uppercase;
        }

        .kop-desc {
            font-size: 7.5px;
            color: #718096;
            margin: 0;
            line-height: 1.4;
        }

        /* Metadata & Filter Box */
        .meta-container {
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 12px;
            width: 100%;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 2px 4px;
            font-size: 8px;
            vertical-align: top;
            border: none;
        }

        .meta-label {
            font-weight: bold;
            color: #4A5568;
            width: 12%;
        }

        .meta-value {
            color: #2D3748;
            width: 21%;
        }

        /* Table Styling */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 12px;
        }

        .report-table th {
            background-color: #4F46E5;
            color: #FFFFFF;
            font-weight: bold;
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            padding: 6px 5px;
            border: 1px solid #4F46E5;
            vertical-align: middle;
            text-align: center;
        }

        .report-table td {
            padding: 5px 4px;
            border: 1px solid #E2E8F0;
            vertical-align: middle;
            font-size: 7.5px;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        .report-chunk {
            page-break-after: always;
        }

        .report-chunk:last-child {
            page-break-after: auto;
        }

        .report-table tr:nth-child(even) td {
            background-color: #F8FAFC;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        /* Empty State */
        .empty-row {
            padding: 24px !important;
            font-style: italic;
            color: #718096;
            font-size: 9px;
        }

        /* Dynamic Footer / Page Numbering */
        @page {
            margin: 1.2cm;
        }

        .footer {
            position: fixed;
            bottom: -15px;
            left: 0;
            right: 0;
            height: 20px;
            text-align: center;
            font-size: 7.5px;
            color: #A0AEC0;
            border-top: 1px solid #E2E8F0;
            padding-top: 4px;
        }

        .footer .page-number:after {
            content: counter(page);
        }
    </style>
</head>

<body>
    {{-- Letterhead / Kop Kampus --}}
    <div class="kop-container">
        @php
            $logoPath = public_path('logo/logo_kotak.png');
            $logoExists = file_exists($logoPath);
        @endphp
        @if($logoExists)
            <img src="{{ $logoPath }}" class="kop-logo" alt="Logo Kampus">
        @endif
        <div class="kop-text">
            <h1 class="kop-title">Sekolah Tinggi Ilmu Kesehatan (STIKES)</h1>
            <h2 class="kop-sub">BOGOR HUSADA</h2>
            <p class="kop-desc">Jl. Sholeh Iskandar No.4, RT.02/RW.03, Kedungbadak, Tanah Sareal, Kota Bogor, Jawa Barat 16164</p>
        </div>
    </div>


    <div style="text-align: center; margin-bottom: 10px;">
        <h3 style="font-size: 11px; font-weight: bold; margin: 0 0 2px 0; text-transform: uppercase; color: #1A202C;">Laporan Data Calon Mahasiswa Baru (PMB)</h3>
        <span style="font-size: 8px; color: #4A5568;">Status penyaringan data pendaftaran aktif</span>
    </div>

    {{-- Metadata Filter Box --}}
    <div class="meta-container">
        <table class="meta-table">
            <tr>
                <td class="meta-label">Periode Akademik</td>
                <td class="meta-value">: {{ $meta['periode'] ?? 'Semua' }}</td>
                <td class="meta-label">Gelombang PMB</td>
                <td class="meta-value">: {{ $meta['gelombang'] ?? 'Semua' }}</td>
                <td class="meta-label">Tanggal Cetak</td>
                <td class="meta-value">: {{ $meta['date'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Status PMB</td>
                <td class="meta-value">: {{ $meta['status'] ?? 'Semua' }}</td>
                <td class="meta-label">Dicetak Oleh</td>
                <td class="meta-value">: {{ $meta['admin'] ?? '-' }}</td>
                <td class="meta-label">Jumlah Pendaftar</td>
                <td class="meta-value">: <strong style="color: #4F46E5;">{{ $data->count() }} Pendaftar</strong></td>
            </tr>
        </table>
    </div>

    {{-- Pecah tabel agar DomPDF tidak menghabiskan memori untuk ribuan sel sekaligus. --}}
    @forelse ($data->chunk(40) as $chunkIndex => $rows)
    <div class="report-chunk">
    <table class="report-table">
        <thead>
            <tr>
                <th style="width:3%">No</th>
                <th style="width:7%">Tgl Daftar</th>
                <th style="width:10%">Nama Lengkap</th>
                <th style="width:11%">Email</th>
                <th style="width:7%">No. HP</th>
                <th style="width:8%">NIK</th>
                <th style="width:7%">NISN</th>
                <th style="width:8%">Nama Ayah</th>
                <th style="width:8%">Nama Ibu</th>
                <th style="width:9%">Program Studi</th>
                <th style="width:7%">Periode</th>
                <th style="width:7%">Gelombang</th>
                <th style="width:8%">Status PMB</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $index => $mhs)
            <tr>
                <td class="text-center fw-bold">{{ ($chunkIndex * 40) + $loop->iteration }}</td>
                <td class="text-center">{{ optional($mhs->created_at)->format('d-m-Y') ?? '-' }}</td>
                <td class="fw-bold">{{ $mhs->name }}</td>
                <td>{{ $mhs->email }}</td>
                <td class="text-center">{{ $mhs->phone ?? '-' }}</td>
                <td class="text-center">{{ $mhs->nik ?? '-' }}</td>
                <td class="text-center">{{ $mhs->nisn ?? '-' }}</td>
                <td>{{ $mhs->nama_ayah ?? '-' }}</td>
                <td>{{ $mhs->nama_ibu ?? '-' }}</td>
                <td class="fw-bold">{{ $mhs->jurusan->nama_jurusan ?? '-' }}</td>
                <td class="text-center">{{ $mhs->periode->deskripsi ?? '-' }}</td>
                <td class="text-center">{{ $mhs->gelombang->nama_gelombang ?? '-' }}</td>
                <td class="text-center fw-bold">
                    {{ \App\Enums\PmbStatus::fromValue($mhs->status_pemb)->label() }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @empty
    <table class="report-table">
        <tbody>
            <tr><td class="text-center empty-row">Tidak ada data calon mahasiswa PMB yang sesuai dengan filter pencarian saat ini.</td></tr>
        </tbody>
    </table>
    @endforelse

    {{-- Total Summary --}}
    @if($data->isNotEmpty())
    <div style="margin-top: 15px; font-size: 9px; font-weight: bold; color: #4F46E5;">
        TOTAL CALON MAHASISWA BARU PMB: {{ $data->count() }} DATA
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        Halaman <span class="page-number"></span> | Laporan PMB STIKES BOGOR Husada
    </div>
</body>


</html>
