<?php

namespace Database\Seeders;

use App\Models\SoalTes;
use App\Models\PilihanJawaban;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SoalLogikaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $soalList = [
            [
                'pertanyaan' => 'Suatu hari, harga beras melonjak jadi 20%. Bila harga beras awalnya hanya Rp10.000 per kilogram, maka harga beras sekarang adalah ...',
                'pilihan' => [
                    'Rp12.200 per kilogram',
                    'Rp12.000 per kilogram',
                    'Rp12.400 per kilogram',
                    'Rp12.600 per kilogram',
                    'Rp12.300 per kilogram',
                ],
                'jawaban' => 'Rp12.000 per kilogram',
            ],
            [
                'pertanyaan' => 'Jika A = 1, B = 2, C = 3, ..., Z = 26, maka jumlah nilai huruf dalam kata "LOGIKA" adalah berapa?',
                'pilihan' => ['45', '61', '54', '69', '32'],
                'jawaban' => '45',
            ],
            [
                'pertanyaan' => 'Jika semua siswa yang datang tepat waktu mendapat pujian, dan Andi mendapat pujian, apakah Andi datang tepat waktu?',
                'pilihan' => ['Ya', 'Tidak'],
                'jawaban' => 'Ya',
            ],
            [
                'pertanyaan' => 'Jika semua manusia adalah makhluk hidup, dan Tom adalah makhluk hidup, apakah Tom manusia?',
                'pilihan' => ['Ya', 'Tidak'],
                'jawaban' => 'Tidak',
            ],
            [
                'pertanyaan' => 'Jika semua pegawai yang bekerja lembur mendapatkan bonus, dan Tono mendapatkan bonus, apakah Tono bekerja lembur?',
                'pilihan' => ['Ya', 'Tidak'],
                'jawaban' => 'Tidak',
            ],
            [
                'pertanyaan' => 'Jika semua murid di kelas A suka matematika, dan Rudi adalah murid di kelas A, maka Rudi...',
                'pilihan' => [
                    'Suka matematika.',
                    'Tidak suka matematika.',
                    'Suka pelajaran lain selain matematika.',
                    'Suka semua pelajaran.',
                    'Tidak belajar'
                ],
                'jawaban' => 'Suka matematika.',
            ],
            [
                'pertanyaan' => 'Semua kucing adalah hewan. Beberapa hewan adalah burung. Oleh karena itu, ....',
                'pilihan' => [
                    'Semua burung adalah kucing.',
                    'Beberapa kucing adalah burung.',
                    'Semua kucing adalah hewan.',
                    'Semua hewan adalah kucing.',
                    'Semua kucing adalah burung',
                ],
                'jawaban' => 'Semua kucing adalah hewan.',
            ],
            [
                'pertanyaan' => 'Sebagian atlet sepak bola mengeluhkan masa depannya setelah pensiun dari bermain bola.',
                'pilihan' => [
                    'Bambang adalah atlet sepak bola. Dia pasti mengeluh soal masa depannya.',
                    'Ranti bukanlah atlet sepak bola, jadi dia pasti tidak pernah mengeluhkan masa depannya',
                    'Kalau ada yang mengeluh soal masa depan, pastilah itu atlet sepakbola.',
                    'Meskipun Budi seorang atlet sepakbola, belum tentu dia mengeluh soal masa depannya.',
                    'Masa depan seorang atlet sepak bola memang tidak pernah bagus.',
                ],
                'jawaban' => 'Meskipun Budi seorang atlet sepakbola, belum tentu dia mengeluh soal masa depannya.',
            ],
            [
                'pertanyaan' => 'Semua mahasiswa Perguruan Tinggi memiliki Nomor Induk Mahasiswa. Budi seorang mahasiswa. Jadi,',
                'pilihan' => [
                    'Budi mungkin memiliki nomor induk mahasiswa',
                    'Belum tentu Budi memiliki nomor induk mahasiswa',
                    'Budi memiliki nomor induk mahasiswa',
                    'Budi tidak memiliki nomor induk mahasiswa',
                    'Tidak dapat ditarik Kesimpulan'
                ],
                'jawaban' => 'Budi memiliki nomor induk mahasiswa',
            ],
            [
                'pertanyaan' => 'Sebagian pedagang pecel lele mengeluhkan harga cabe naik. Pak Rudi seorang pedagang pecel lele.',
                'pilihan' => [
                    'Pak Rudi pasti mengeluhkan harga cabe naik.',
                    'Pak Rudi tidak mengeluhkan harga cabe naik.',
                    'Harga cabe bukanlah keluhan Pak Rudi',
                    'Pak Rudi mungkin ikut mengeluhkan harga cabe naik',
                    'Harga cabe naik atau tidak, pak Rudi tetap mengeluh'
                ],
                'jawaban' => 'Pak Rudi mungkin ikut mengeluhkan harga cabe naik',
            ],
        ];

        foreach ($soalList as $soalData) {
            $soal = SoalTes::create([
                'tes_tulis_id' => 1,
                'kategori_id'  => 3, // Tes Logika
                'pertanyaan'   => $soalData['pertanyaan'],
                'skor'         => 2,
            ]);

            foreach ($soalData['pilihan'] as $index => $teks) {
                PilihanJawaban::create([
                    'soal_tes_id'  => $soal->id,
                    'teks_pilihan' => $teks,
                    'kode_pilihan' => chr(65 + $index),
                    'benar'        => $teks === $soalData['jawaban'],
                    'urutan'       => $index + 1,
                ]);
            }
        }
    }
}
