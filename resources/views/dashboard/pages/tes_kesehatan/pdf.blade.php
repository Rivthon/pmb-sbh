<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Form Tes Kesehatan - {{ $anamnesa->user->name ?? '-' }}</title>
    <style>
        @page { size: A4; margin: 1.5cm; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; line-height: 1.45; color: #111; }
        .header { display: table; width: 100%; border-bottom: 2px solid #111; padding-bottom: 10px; margin-bottom: 14px; }
        .logo { display: table-cell; width: 82px; vertical-align: middle; }
        .logo img { width: 72px; }
        .title { display: table-cell; text-align: center; vertical-align: middle; }
        .title h1 { font-size: 16px; margin: 0 0 3px; text-transform: uppercase; }
        .title h2 { font-size: 13px; margin: 0; }
        .muted { color: #555; }
        .section { font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #777; margin: 16px 0 8px; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cfcfcf; padding: 6px 7px; vertical-align: top; }
        th { width: 32%; text-align: left; background: #f4f6f8; }
        .check td { width: 25%; }
        .answer { min-height: 36px; }
        .signatures { width: 100%; margin-top: 34px; page-break-inside: avoid; }
        .signatures td { border: 0; text-align: center; width: 50%; }
        .space { height: 58px; }
    </style>
</head>

<body>
    <div class="header">
        <div class="logo">
            <img src="{{ public_path('logo/logo_kotak.png') }}" alt="Logo">
        </div>
        <div class="title">
            <h1>Sekolah Tinggi Ilmu Kesehatan Bogor Husada</h1>
            <h2>Form Anamnesa Tes Kesehatan PMB</h2>
            <div class="muted">Jl. Sholeh Iskandar No.4, RT.02/RW.03, Kedungbadak, Tanah Sareal, Kota Bogor, Jawa Barat 16164</div>
        </div>
    </div>

    <div class="section">Data Peserta</div>
    <table>
        <tr><th>Nama</th><td>{{ $anamnesa->user->name ?? '-' }}</td></tr>
        <tr><th>Email</th><td>{{ $anamnesa->user->email ?? '-' }}</td></tr>
        <tr><th>Nomor HP</th><td>{{ $anamnesa->user->phone ?? '-' }}</td></tr>
        <tr><th>Program Studi</th><td>{{ $anamnesa->user->jurusan->nama_jurusan ?? '-' }}</td></tr>
        <tr>
            <th>Tanggal Pengisian</th>
            <td>{{ $anamnesa->tanggal_pengisian ? \Carbon\Carbon::parse($anamnesa->tanggal_pengisian)->translatedFormat('d F Y H:i') : '-' }}</td>
        </tr>
        <tr><th>Status Pemeriksaan</th><td>{{ ucfirst($anamnesa->status ?? 'belum diperiksa') }}</td></tr>
        <tr><th>Surat Kesehatan Klinik/RS</th><td>{{ $anamnesa->surat_kesehatan_path ? 'Sudah diunggah' : '-' }}</td></tr>
    </table>

    <div class="section">Riwayat Kesehatan</div>
    <table class="check">
        @foreach([
            ['Riwayat penyakit keluarga', 'riwayat_penyakit_keluarga', 'Riwayat penyakit pribadi', 'riwayat_penyakit_pribadi'],
            ['Riwayat operasi', 'riwayat_operasi', 'Konsumsi obat rutin', 'konsumsi_obat_rutin'],
            ['Penyakit menular', 'penyakit_menular', 'Masalah kulit', 'masalah_kulit'],
            ['Tumor/benjolan/kista/kanker', 'tumor_benjolan', 'Epilepsi/kejang', 'epilepsi'],
            ['Cedera kepala/gegar otak', 'cedera_kepala', 'Batuk kronis', 'batuk_kronis'],
            ['Gangguan pencernaan', 'gangguan_pencernaan', 'Gangguan keseimbangan', 'gangguan_keseimbangan'],
            ['Takut ruang sempit', 'claustrophobia', 'Takut melihat darah', 'takut_darah'],
            ['Kacamata/alat bantu penglihatan', 'kacamata', 'Gagap/gangguan bicara', 'gagap'],
            ['Penyangga tulang/sendi', 'alat_bantu_tulang', 'Lemah otot/gangguan saraf', 'lemah_otot'],
            ['Pikiran/percobaan bunuh diri', 'pikiran_bunuh_diri', 'Kelainan darah', 'kelainan_darah'],
            ['Riwayat psikolog/psikiater', 'riwayat_psikolog', '', null],
        ] as $row)
            <tr>
                <td>{{ $row[0] }}</td>
                <td><strong>{{ $anamnesa->{$row[1]} ? 'Ya' : 'Tidak' }}</strong></td>
                <td>{{ $row[2] }}</td>
                <td><strong>{{ $row[3] ? ($anamnesa->{$row[3]} ? 'Ya' : 'Tidak') : '-' }}</strong></td>
            </tr>
        @endforeach
    </table>

    <div class="section">Keterangan Tambahan</div>
    <table>
        <tr>
            <td class="answer">{{ $anamnesa->keterangan ?: '-' }}</td>
        </tr>
    </table>

    @if($pemeriksaan)
        <div class="section">Catatan Pemeriksaan</div>
        <table>
            <tr><th>Nama Pemeriksa</th><td>{{ $pemeriksaan->nama_pemeriksa ?? '-' }}</td></tr>
            <tr><th>Tanggal Pemeriksaan</th><td>{{ $pemeriksaan->tanggal_pemeriksaan ? \Carbon\Carbon::parse($pemeriksaan->tanggal_pemeriksaan)->translatedFormat('d F Y') : '-' }}</td></tr>
            <tr><th>Rekomendasi</th><td>{{ ucfirst(str_replace('_', ' ', $pemeriksaan->rekomendasi ?? '-')) }}</td></tr>
            <tr><th>Catatan</th><td>{{ $pemeriksaan->catatan ?? '-' }}</td></tr>
        </table>
    @endif

    <table class="signatures">
        <tr>
            <td>
                Bogor, {{ now()->translatedFormat('d F Y') }}<br>
                Calon Mahasiswa
                <div class="space"></div>
                <strong>{{ $anamnesa->user->name ?? '.........................' }}</strong>
            </td>
            <td>
                Petugas
                <div class="space"></div>
                <strong>.........................</strong>
            </td>
        </tr>
    </table>
</body>

</html>
