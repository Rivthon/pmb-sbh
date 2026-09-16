<table border="1" width="100%" cellpadding="5">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Program Studi</th>
            <th>Tempat Lahir</th>
            <th>Tanggal Lahir</th>
            <th>Alamat</th>
            <th>Provinsi</th>
            <th>Kabupaten</th>
            <th>Kecamatan</th>
            <th>Kelurahan</th>
            <th>Agama</th>
            <th>NISN</th>
            <th>NIK</th>
            <th>Asal Sekolah</th>
            <th>Periode</th>
            <th>Gelombang</th>
            <th>Status</th>
            <th>Kuesioner</th>
            <th>Nama Ayah</th>
            <th>Pekerjaan Ayah</th>
            <th>Nama Ibu</th>
            <th>Pekerjaan Ibu</th>
            <th>Nama Wali</th>
            <th>No Telp Ortu</th>
            <th>Penghasilan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $index => $mhs)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $mhs->name }}</td>
            <td>{{ $mhs->jurusan->nama_jurusan ?? '-' }}</td>
            <td>{{ $mhs->tempat_lahir ?? '-' }}</td>
            <td>{{ $mhs->tgl_lahir ?? '-' }}</td>
            <td>{{ $mhs->address ?? '-' }}</td>
            <td>{{ $mhs->provinsi->nama ?? '-' }}</td>
            <td>{{ $mhs->kabupaten->nama_kab ?? '-' }}</td>
            <td>{{ $mhs->kecamatan->nama_kec ?? '-' }}</td>
            <td>{{ $mhs->kelurahan->nama_kel ?? '-' }}</td>
            <td>{{ $mhs->agama->nama_agama ?? '-' }}</td>
            <td>{{ $mhs->nisn ?? '-' }}</td>
            <td>{{ $mhs->nik ?? '-' }}</td>
            <td>{{ $mhs->asal_sekolah ?? '-' }}</td>
            <td>{{ $mhs->periode->deskripsi ?? '-' }}</td>
            <td>{{ $mhs->gelombang->nama_gelombang ?? '-' }}</td>
            <td>
                {{ \App\Enums\PmbStatus::fromValue($mhs->status_pemb)->label() }}
            </td>
            <td>{{ $mhs->kuesioner->nama_kusioner ?? '-' }}</td>
            <td>{{ $mhs->nama_ayah ?? '-' }}</td>
            <td>{{ $mhs->pekerjaanAyah->nama_pek_ayah ?? '-' }}</td>
            <td>{{ $mhs->nama_ibu ?? '-' }}</td>
            <td>{{ $mhs->pekerjaanIbu->nama_pek_ibu ?? '-' }}</td>
            <td>{{ $mhs->nama_wali ?? '-' }}</td>
            <td>{{ $mhs->no_telp_ortu ?? '-' }}</td>
            <td>{{ $mhs->penghasilan->nama_peng ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
