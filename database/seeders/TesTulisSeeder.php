<?php

namespace Database\Seeders;

use App\Models\TesTulis;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TesTulisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TesTulis::create([
            'nama_tes' => 'Tes Akademik Gelombang 1',
            'deskripsi' => 'Tes tulis berbasis pilihan ganda untuk seleksi penerimaan mahasiswa baru gelombang 1.',
            'durasi_menit' => 60,
            'skor_lulus' => 70,
            'acak_soal' => true,
            'acak_pilihan' => true,
            'status_aktif' => true,
        ]);

        TesTulis::create([
            'nama_tes' => 'Tes Akademik Gelombang 2',
            'deskripsi' => 'Tes tulis tahap kedua untuk calon mahasiswa yang mendaftar di gelombang 2.',
            'durasi_menit' => 75,
            'skor_lulus' => 70,
            'acak_soal' => true,
            'acak_pilihan' => false,
            'status_aktif' => false,
        ]);

        TesTulis::create([
            'nama_tes' => 'Tes Tulis Uji Coba',
            'deskripsi' => 'Tes simulasi untuk pengujian sistem dan latihan peserta.',
            'durasi_menit' => 30,
            'skor_lulus' => 60,
            'acak_soal' => false,
            'acak_pilihan' => false,
            'status_aktif' => true,
        ]);
    }
}
