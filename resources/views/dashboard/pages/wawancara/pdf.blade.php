<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Form Wawancara - {{ $wawancara->user->name ?? '-' }}</title>
    <style>
        @page { size: A4; margin: 1.5cm; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; line-height: 1.5; color: #111; }
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
        .question { font-weight: bold; margin: 10px 0 4px; }
        .answer { border: 1px solid #cfcfcf; padding: 7px; min-height: 36px; }
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
            <h2>Form Wawancara PMB</h2>
            <div class="muted">Jl. Sholeh Iskandar No.4, RT.02/RW.03, Kedungbadak, Tanah Sareal, Kota Bogor, Jawa Barat 16164</div>
        </div>
    </div>

    <div class="section">Data Peserta</div>
    <table>
        <tr><th>Nama</th><td>{{ $wawancara->user->name ?? '-' }}</td></tr>
        <tr><th>Email</th><td>{{ $wawancara->user->email ?? '-' }}</td></tr>
        <tr><th>Nomor HP</th><td>{{ $wawancara->user->phone ?? '-' }}</td></tr>
        <tr><th>Program Studi</th><td>{{ $wawancara->user->jurusan->nama_jurusan ?? '-' }}</td></tr>
        <tr><th>Status Wawancara</th><td>{{ ucfirst($wawancara->status ?? '-') }}</td></tr>
        <tr><th>Tanggal Kirim</th><td>{{ $wawancara->updated_at?->translatedFormat('d F Y H:i') ?? '-' }}</td></tr>
    </table>

    <div class="section">Jawaban Wawancara</div>

    @foreach([
        '1. Bagaimana Anda menceritakan diri Anda?' => $wawancara->jawaban_1,
        '2. Dapatkah Anda menceritakan tentang pekerjaan orang tua / wali?' => $wawancara->jawaban_2,
        '3. Kelebihan yang Anda miliki' => $wawancara->kelebihan,
        'Kekurangan yang Anda miliki' => $wawancara->kekurangan,
        '4. Anda memilih jurusan apa dan berikan alasannya!' => $wawancara->jawaban_4,
        '5. Apa yang membuat Anda memilih STIKes Bogor Husada?' => $wawancara->jawaban_5,
        'Dari mana Anda mendapatkan informasi STIKes Bogor Husada?' => $wawancara->sumber_informasi,
        '6. Bagaimana Anda melihat diri Anda di masa depan?' => $wawancara->jawaban_6,
    ] as $question => $answer)
        <div class="question">{{ $question }}</div>
        <div class="answer">{{ $answer ?: '-' }}</div>
    @endforeach

    @if(in_array($wawancara->status, ['reviewed', 'locked'], true))
        <div class="section">Hasil Review Kaprodi</div>
        <table>
            <tr><th>Reviewer</th><td>{{ $wawancara->pewawancara->name ?? '-' }}</td></tr>
            <tr><th>Tanggal Review</th><td>{{ $wawancara->tanggal_review?->translatedFormat('d F Y H:i') ?? '-' }}</td></tr>
            <tr><th>Kesimpulan</th><td>{{ $wawancara->kesimpulan_kaprodi ?: '-' }}</td></tr>
            <tr><th>Rekomendasi</th><td>{{ ucfirst(str_replace('_', ' ', $wawancara->rekomendasi ?? '-')) }}</td></tr>
        </table>
    @endif

    <table class="signatures">
        <tr>
            <td>
                Bogor, {{ now()->translatedFormat('d F Y') }}<br>
                Calon Mahasiswa
                <div class="space"></div>
                <strong>{{ $wawancara->user->name ?? '.........................' }}</strong>
            </td>
            <td>
                Pewawancara
                <div class="space"></div>
                <strong>.........................</strong>
            </td>
        </tr>
    </table>
</body>

</html>
