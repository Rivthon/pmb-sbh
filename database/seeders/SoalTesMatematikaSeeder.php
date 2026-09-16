<?php

namespace Database\Seeders;

use App\Models\SoalTes;
use App\Models\PilihanJawaban;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SoalTesMatematikaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $soalList = [
            [
                'pertanyaan' => '(25 + 12) × 3 − 40 =',
                'pilihan' => ['81', '80', '71', '68', '70'],
                'jawaban' => '81',
            ],
            [
                'pertanyaan' => '44 + 19 = 23 + …',
                'pilihan' => ['38', '39', '41', '40', '42'],
                'jawaban' => '40',
            ],
            [
                'pertanyaan' => 'Suatu seri: 1 - 2 - 4 - 8 - 16 - 32, maka seri selanjutnya adalah...',
                'pilihan' => ['32-16-8', '16-32-8', '64-128-256', '64-32-128', '256-128-64'],
                'jawaban' => '64-128-256',
            ],
            [
                'pertanyaan' => '4, 8, (...), (...), 64, 128',
                'pilihan' => ['28, 24', '16, 32', '26, 14', '13, 29', '22, 14'],
                'jawaban' => '16, 32',
            ],
            [
                'pertanyaan' => 'N, A, O, B, P, C, (...), (...)',
                'pilihan' => ['Q, D', 'Q, F', 'N, E', 'O, E', 'M, E'],
                'jawaban' => 'Q, D',
            ],
            [
                'pertanyaan' => '3, 6, 4, 8, 6, ..., ...',
                'pilihan' => ['24, 12', '24, 36', '12, 10', '12, 18', '12, 24'],
                'jawaban' => '12, 24',
            ],
            [
                'pertanyaan' => '6, 3, 4, 1, 2, -1, (...)',
                'pilihan' => ['-6', '-4', '-2', '-1', '0'],
                'jawaban' => '-2',
            ],
            [
                'pertanyaan' => '4, 0, 8, 4, 12, 8, ...',
                'pilihan' => ['24', '20', '16', '12', '8'],
                'jawaban' => '16',
            ],
            [
                'pertanyaan' => 'Jika x = 2y, y = 3z, dan xyz = 3888 maka ....',
                'pilihan' => ['x < y', 'y < z', 'z < y', 'x = y', 'y < x'],
                'jawaban' => 'y < x',
            ],
            [
                'pertanyaan' => 'Suatu kebun digambarkan pada denah dengan ukuran panjang 9 cm dan lebar 5 cm. Jika luas kebun sebenarnya 405 m², berapakah skala yang digunakan?',
                'pilihan' => ['1 : 200', '1 : 600', '1 : 300', '1 : 900', '1 : 405'],
                'jawaban' => '1 : 300',
            ],
        ];

        foreach ($soalList as $soalData) {
            $soal = SoalTes::create([
                'tes_tulis_id' => 1,
                'kategori_id'  => 4, // Matematika
                'pertanyaan'   => $soalData['pertanyaan'],
                'skor'         => 2,
            ]);

            foreach ($soalData['pilihan'] as $index => $teks) {
                PilihanJawaban::create([
                    'soal_tes_id'  => $soal->id,
                    'teks_pilihan' => $teks,
                    'kode_pilihan' => chr(65 + $index), // A, B, C...
                    'benar'        => $teks === $soalData['jawaban'],
                    'urutan'       => $index + 1,
                ]);
            }
        }
    }
}
