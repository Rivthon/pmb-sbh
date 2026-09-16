<?php

namespace Database\Seeders;

use App\Models\SoalTes;
use App\Models\TesTulis;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SoalTesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $soalList = [
            [
                'pertanyaan' => 'Negara Kesatuan Republik Indonesia merupakan negara majemuk. Kemajemukan tersebut diwujudkan dalam....',
            ],
            [
                'pertanyaan' => 'Pak Rudi adalah kepala sekolah di suatu SMA. Sudah sepuluh tahun sekolah yang dipimpin Pak Rudi memiliki status akreditasi B. Pekan depan akan dilakukan proses akreditasi terbaru yang memungkinkan sekolah yang dipimpin Pak Rudi berubah status akreditasi menjadi A. Jika Anda menjadi Pak Rudi apa yang sebaiknya dilakukan.....',
            ],
            [
                'pertanyaan' => 'Dalam Negara Kesatuan Republik Indonesia terkandung makna persatuan dan kesatuan Indonesia. Landasan persatuan dan kesatuan Indonesia ditunjukan oleh...',
            ],
            [
                'pertanyaan' => 'Paham yang menetapkan agama sebagai hukum politik dalam dunia modern merupakan jenis ideologi.....',
            ],
            [
                'pertanyaan' => 'Indonesia dapat meniru dan belajar hal-hal positif dari negara-negara lain. Contohnya seperti kebersihan dari Singapura atau kedisiplinan dari Jepang. Hal tersebut merupakan manfaat kemajuan iptek dalam bidang.....',
            ],
            [
                'pertanyaan' => 'Salah satu jenis perbuatan yang tidak menunjukkan adanya integritas adalah dengan menyalahgunakan kepercayaan yang telah dimiliki untuk mendapatkan keuntungan pribadi. Perbuatan ini disebut dengan....',
            ],
            [
                'pertanyaan' => 'Sila pertama Pancasila berbunyi "Ketuhanan Yang Maha Esa". Prinsip dasar dari sila ini adalah...',
            ],
            [
                'pertanyaan' => 'Indonesia merupakan negara kesatuan dengan sistem desentralisasi, dengan adanya pemberian hak otonomi kepada daerah. Pemberian hak otonomi kepada daerah diikuti dengan batasan-batasan agar....',
            ],
            [
                'pertanyaan' => 'Prinsip demokrasi yang paling mendasar adalah...',
            ],
            [
                'pertanyaan' => 'Fungsi utama lembaga legislatif adalah...',
            ],
        ];

        foreach ($soalList as $soal) {
            SoalTes::create([
                'tes_tulis_id' => 1,
                'kategori_id' => 1,
                'pertanyaan' => $soal['pertanyaan'],
                'skor' => 2,
            ]);
        }
    }
}
