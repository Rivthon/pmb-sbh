<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Hasil Tes Tulis</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }

        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #000;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }

        .header-logo {
            display: table-cell;
            width: 82px;
            vertical-align: middle;
        }

        .header-logo img {
            width: 72px;
            height: auto;
        }

        .header-title {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
            padding-right: 82px;
        }

        .header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }

        .info {
            margin-bottom: 20px;
        }

        .info table {
            width: 100%;
            border-collapse: collapse;
        }

        .info td {
            padding: 4px 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th,
        .table td {
            border: 1px solid #888;
            padding: 6px;
            text-align: center;
        }

        .table th {
            background: #eee;
        }

        .summary {
            margin-top: 15px;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-logo">
            <img src="{{ public_path('logo/logo_kotak.png') }}" alt="Logo">
        </div>
        <div class="header-title">
            <h3>STIKes Bogor Husada</h3>
            <div>Jl. Sholeh Iskandar No.4, RT.02/RW.03, Kedungbadak, Tanah Sareal, Kota Bogor, Jawa Barat 16164</div>
            <h4 style="margin:10px 0 0;">Laporan Hasil Tes Tulis Calon Mahasiswa Baru</h4>
        </div>
    </div>

    <div class="info">
        <table>
            <tr>
                <td width="30%">Nama</td>
                <td>: {{ $hasil->user->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>: {{ $hasil->user->email ?? '-' }}</td>
            </tr>
            <tr>
                <td>Waktu Tes</td>
                <td>: {{ $hasil->waktu_mulai?->format('d M Y H:i') }} - {{ $hasil->waktu_selesai?->format('H:i') }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>: {{ $hasil->status ? 'Selesai' : 'Belum Selesai' }}</td>
            </tr>
        </table>
    </div>

    <h4>Rincian Hasil Per Kategori</h4>
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kategori</th>
                <th>Jumlah Soal</th>
                <th>Benar</th>
                <th>Salah</th>
                <th>Skor</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($hasil->kategoriHasil as $kategori)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td style="text-align:left">{{ $kategori->kategori->nama_kategori ?? '-' }}</td>
                <td>{{ $kategori->jumlah_soal }}</td>
                <td>{{ $kategori->jawaban_benar }}</td>
                <td>{{ $kategori->jawaban_salah }}</td>
                <td><strong>{{ $kategori->skor }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <p><strong>Total Skor:</strong> {{ $hasil->skor }}</p>
    </div>

    <div style="margin-top:30px; text-align:right;">
        <p>Dicetak pada: {{ now()->format('d M Y H:i') }}</p>
        <p style="margin-top:40px;">_____________________________<br>
            <strong>Panitia PMB</strong>
        </p>
    </div>
</body>

</html>
