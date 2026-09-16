<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Formulir Kesehatan PMB - {{ $anamnesa->user->name ?? '-' }}</title>
    <style>
        /* Mengatur halaman untuk cetak/PDF */
        @page {
            size: A4;
            /* Margin halaman: atas, kanan, bawah, kiri */
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
        }

        body {
            font-family: DejaVu Sans, "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 11px;
            /* Ukuran font 11px lebih umum untuk laporan padat */
            line-height: 1.5;
            color: #333;
            margin: 0;
            /* Margin tubuh diatur oleh @page */
        }

        /* --- HEADER --- */
        .header {
            display: table;
            /* Menggunakan 'table' lebih aman untuk PDF daripada flex */
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .header-logo {
            display: table-cell;
            width: 90px;
            vertical-align: middle;
        }

        .header-logo img {
            width: 85px;
            height: auto;
        }

        .header-title {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
            line-height: 1.4;
        }

        .header-title h3 {
            margin: 0 0 2px 0;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            color: #000;
        }

        .header-title h4 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: #222;
        }

        .header-subtext {
            font-size: 10px;
            margin-top: 4px;
            color: #444;
        }

        /* Hapus <hr> lama, border-bottom di .header sudah menggantikan */

        /* --- KONTEN UTAMA --- */
        .section-title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 1px solid #555;
            padding-bottom: 4px;
            color: #000;
        }

        /* --- TABEL DATA (Mahasiswa & Pemeriksaan) --- */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        /* Beri kelas khusus untuk tabel data profil & fisik */
        .table-data th,
        .table-data td {
            padding: 7px 8px;
            border: 1px solid #ccc;
            /* Border abu-abu lebih lembut */
            text-align: left;
            vertical-align: top;
        }

        .table-data th {
            width: 30%;
            /* Lebar tetap untuk kolom label */
            font-weight: bold;
            background-color: #f9f9f9;
            /* Latar abu-abu sangat muda */
        }

        /* --- TABEL RIWAYAT KESEHATAN (Pengganti Grid) --- */
        .table-riwayat {
            border: 1px solid #ccc;
        }

        .table-riwayat td {
            padding: 6px 10px;
            width: 25%;
            /* 4 kolom */
            vertical-align: top;
            border-bottom: 1px solid #eee;
            /* Garis pemisah antar baris */
        }

        /* Hapus border di baris terakhir */
        .table-riwayat tr:last-child td {
            border-bottom: none;
        }

        .table-riwayat strong {
            display: inline-block;
            color: #333;
            font-weight: 600;
            /* Sedikit tebal */
        }

        .table-riwayat span {
            font-weight: bold;
            color: #000;
            float: right;
            /* Posisikan 'Ya/Tidak' di kanan */
        }

        /* Styling untuk 'Keterangan Tambahan' */
        .keterangan-tambahan td {
            width: 100%;
            background-color: #fcfcfc;
        }

        .keterangan-tambahan strong {
            display: block;
            margin-bottom: 4px;
        }

        .keterangan-tambahan span {
            float: none;
            font-weight: normal;
            font-style: italic;
            color: #222;
        }

        /* --- FOOTER / TANDA TANGAN --- */
        .footer {
            text-align: right;
            margin-top: 40px;
            page-break-inside: avoid;
            /* Hindari tanda tangan terpotong di halaman baru */
        }

        .footer-date {
            margin-bottom: 10px;
        }

        .signature-space {
            height: 60px;
            /* Ruang kosong untuk tanda tangan basah */
        }

        .signature-name {
            margin: 0;
            font-weight: bold;
            text-transform: capitalize;
            /* Pastikan nama diawali huruf besar */
        }

        .signature-title {
            margin: 0;
            font-size: 11px;
            color: #444;
        }
    </style>
</head>

<body>
    @php
        $includeUrine = (bool) $anamnesa->user?->jurusan?->isD3Kebidanan();
        $officialExamSections = \App\Models\TesKesehatanPemeriksaan::officialExamSections($includeUrine);
        $anamnesaQuestions = \App\Models\TesKesehatanAnamnesa::officerAnamnesaQuestions();
        $yesNoLabel = fn ($value) => $value === 1 || $value === true ? 'Ya' : ($value === 0 || $value === false ? 'Tidak' : '-');
        $academicYear = $testSession?->periode?->deskripsi
            ?? $anamnesa->user?->periode?->deskripsi
            ?? '-';
        $testDate = $pemeriksaan->tanggal_pemeriksaan
            ? \Carbon\Carbon::parse($pemeriksaan->tanggal_pemeriksaan)
            : now();
        $birthDate = $anamnesa->user?->tgl_lahir
            ? \Carbon\Carbon::parse($anamnesa->user->tgl_lahir)->translatedFormat('d F Y')
            : null;
        $birthPlaceDate = collect([$anamnesa->user?->tempat_lahir, $birthDate])->filter()->implode(', ') ?: '-';
    @endphp

    <div class="header">
        <div class="header-logo">
            <img src="{{ public_path('logo/logo_kotak.png') }}" alt="Logo" />
        </div>
        <div class="header-title">
            <h3>SEKOLAH TINGGI ILMU KESEHATAN BOGOR HUSADA</h3>
            <h4>Formulir Kesehatan Seleksi Penerimaan Mahasiswa Baru</h4>
            <h4>Tahun Akademik {{ $academicYear }}</h4>
            <div class="header-subtext">
                Jl. Sholeh Iskandar No.4, RT.02/RW.03, Kedungbadak, Tanah Sareal, Kota Bogor, Jawa Barat 16164
            </div>
        </div>
    </div>

    <div class="section-title">Data Calon Mahasiswa</div>
    <table class="table-data">
        <tr>
            <th>Nama Calon Mahasiswa</th>
            <td>{{ $anamnesa->user->name ?? '-' }}</td>
        </tr>
        <tr>
            <th>Program Studi</th>
            <td>{{ $anamnesa->user?->jurusan?->nama_jurusan ?? '-' }}</td>
        </tr>
        <tr>
            <th>Tempat, Tanggal Lahir</th>
            <td>{{ $birthPlaceDate }}</td>
        </tr>
        <tr>
            <th>Sesi Tes</th>
            <td>{{ $testSession?->name ?? $testSession?->code ?? '-' }}</td>
        </tr>
        <tr>
            <th>Tanggal Tes</th>
            <td>{{ $testDate->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <th>Pemeriksa</th>
            <td>{{ $pemeriksaan->nama_pemeriksa ?? '-' }}</td>
        </tr>
    </table>


    <div class="section-title">Riwayat Kesehatan (Anamnesa)</div>
    <table class="table-riwayat">
        <tr>
            <th style="width: 28px;">No</th>
            <th>Pernyataan</th>
            <th style="width: 55px;">Jawaban</th>
            <th>Keterangan</th>
        </tr>
        @foreach($anamnesaQuestions as $index => $question)
            @php
                $field = $question['field'];
                $noteField = "{$field}_keterangan";
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $question['label'] }}</strong></td>
                <td>{{ $yesNoLabel(data_get($anamnesa, $field)) }}</td>
                <td>{{ data_get($anamnesa, $noteField) ?: '-' }}</td>
            </tr>
        @endforeach
        <tr class="keterangan-tambahan">
            <td colspan="4">
                <strong>Keterangan Tambahan:</strong>
                <span>{{ $anamnesa->keterangan ?? '-' }}</span>
            </td>
        </tr>
    </table>
    <div class="section-title">Data Pemeriksaan Fisik</div>
    <table class="table-data">
        <tr>
            <th>Nama Pemeriksa</th>
            <td>{{ $pemeriksaan->nama_pemeriksa ?? '-' }}</td>
        </tr>
        <tr>
            <th>Tanggal Pemeriksaan</th>
            <td>{{ $pemeriksaan->tanggal_pemeriksaan ?
                \Carbon\Carbon::parse($pemeriksaan->tanggal_pemeriksaan)->translatedFormat('d F Y') : '-' }}
            </td>
        </tr>
        <tr>
            <th>Tinggi Badan</th>
            <td>{{ $pemeriksaan->tinggi_badan ? $pemeriksaan->tinggi_badan . ' cm' : '-' }}</td>
        </tr>
        <tr>
            <th>Berat Badan</th>
            <td>{{ $pemeriksaan->berat_badan ? $pemeriksaan->berat_badan . ' kg' : '-' }}</td>
        </tr>
        <tr>
            <th>Tekanan Darah</th>
            <td>{{ $pemeriksaan->tekanan_darah ? $pemeriksaan->tekanan_darah . ' mmHg' : '-' }}</td>
        </tr>
    </table>

    @foreach($officialExamSections as $sectionTitle => $items)
    <div class="section-title">{{ $sectionTitle }}</div>
    <table class="table-data">
        <tr>
            <th>Jenis Pemeriksaan</th>
            <th style="width: 13%; text-align: center;">Normal</th>
            <th style="width: 13%; text-align: center;">Kelainan</th>
            <th>Keterangan</th>
        </tr>
        @foreach($items as $field => $label)
        @php
            $condition = data_get($pemeriksaan, "{$field}_kondisi");
        @endphp
        <tr>
            <td>{{ $label }}</td>
            <td style="text-align: center;">{{ $condition === 'normal' ? 'Ya' : '-' }}</td>
            <td style="text-align: center;">{{ $condition === 'kelainan' ? 'Ya' : '-' }}</td>
            <td>{{ data_get($pemeriksaan, "{$field}_keterangan") ?: '-' }}</td>
        </tr>
        @endforeach
    </table>
    @endforeach

    {{-- CATATAN & REKOMENDASI --}}
    <div class="section-title">Catatan & Rekomendasi</div>
    <table class="table-data">
        <tr>
            <th>Catatan Pemeriksa</th>
            <td>{{ $pemeriksaan->catatan ?? '-' }}</td>
        </tr>
        <tr style="background-color: #fdfde2;">
            {{-- Beri highlight --}}
            <th>Rekomendasi</th>
            <td><strong>{{ ucfirst($pemeriksaan->rekomendasi ?? '-') }}</strong></td>
        </tr>
    </table>

    <div class="footer">
        <p class="footer-date">Bogor, {{ $testDate->translatedFormat('d F Y') }}</p>
        <div class="signature-space">
        </div>
        <p class="signature-name">{{ $pemeriksaan->nama_pemeriksa ?? '.........................' }}</p>
        <p class="signature-title">Pemeriksa Kesehatan</p>
    </div>
</body>

</html>
