<?php

namespace Database\Seeders;

use App\Models\SoalTes;
use App\Models\PilihanJawaban;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SoalBahasaInggrisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $soalList = [
            [
                'pertanyaan' => 'The Announcement is mostly concerned with...',
                'pilihan' => [
                    'Manager substitution',
                    'Bill\'s experience',
                    'Computer industry',
                    'Bill\'s achievement',
                    'The new Sales Manager'
                ],
                'jawaban' => 'The new Sales Manager',
            ],
            [
                'pertanyaan' => 'What made Mr. William get the promotion?',
                'pilihan' => [
                    'Ms. Brown\'s retirement',
                    'Mr. William\'s experience',
                    'Ms. Brown\'s recommendation',
                    'Mr. William\'s achievement',
                    'Mr. William\'s networking'
                ],
                'jawaban' => 'Mr. William\'s achievement',
            ],
            [
                'pertanyaan' => 'It can be inferred from the text that...',
                'pilihan' => [
                    'Bill William is a skillful IT engineer',
                    'Bill William is a new Sales Manager',
                    'Bill William has 13 years of experience in computer industry',
                    'Bill Wiliam is manager substitution',
                    'Bill William replaces Ms. Brown\'s position'
                ],
                'jawaban' => 'Bill William is a new Sales Manager',
            ],
            [
                'pertanyaan' => 'Rearrange the following jumbled sentences into the correct and meaningful paragraph.',
                'pilihan' => [
                    '3 - 2 - 1 - 4',
                    '1 - 3 - 4 - 2',
                    '1 - 2 - 3 - 4',
                    '1 - 4 - 3 - 2',
                    '4 - 3 - 1 - 2'
                ],
                'jawaban' => '1 - 3 - 4 - 2',
            ],
            [
                'pertanyaan' => 'Arrange the following sentences into a correct and meaningful procedure (Emergency First Aid: External Bleeding).',
                'pilihan' => [
                    '3 - 2 - 4 - 5 - 1',
                    '4 - 3 - 1 - 2 - 5',
                    '5 - 1 - 2 - 4 - 3',
                    '2 - 1 - 3 - 4 - 5',
                    '1 - 5 - 4 - 2 - 3'
                ],
                'jawaban' => '4 - 3 - 1 - 2 - 5',
            ],
            [
                'pertanyaan' => '"I had my house cleaned" means.....',
                'pilihan' => [
                    'I got someone to clean my house',
                    'Somebody cleaned the house for someone',
                    'I cleaned my house by myself',
                    'Nobody clean the house',
                    'The house is clean by itself'
                ],
                'jawaban' => 'I got someone to clean my house',
            ],
            [
                'pertanyaan' => 'One question ... you may not have considered involves ... low physical demands ... computer learning. What... exercise? Computers certainly do not promote ... activity.',
                'pilihan' => [
                    'To - that - of - about - physical',
                    'That - the - of - about - physical',
                    'About - the - of - about - physical',
                    'Of - the - about - that - physical',
                    'That - the - about - of - physical'
                ],
                'jawaban' => 'That - the - of - about - physical',
            ],
            [
                'pertanyaan' => 'The name "squirrel" is commonly used for those forms of the family Sciuridae that live in trees, ... it is equally accurate for ground-dwelling types.',
                'pilihan' => [
                    'Whether',
                    'That',
                    'Although',
                    'In spite of',
                    'It'
                ],
                'jawaban' => 'Although',
            ],
            [
                'pertanyaan' => 'The director is meeting with the investors, as the company would like to ... operations.',
                'pilihan' => [
                    'Expend',
                    'Expanse',
                    'Expand',
                    'Expense',
                    'Expand'
                ],
                'jawaban' => 'Expand',
            ],
            [
                'pertanyaan' => 'Education in Indonesia is the ... of school, government, and society.',
                'pilihan' => [
                    'Responsible',
                    'Responsibility',
                    'Responsive',
                    'Responsibly',
                    'Responded'
                ],
                'jawaban' => 'Responsibility',
            ],
        ];

        foreach ($soalList as $soalData) {
            $soal = SoalTes::create([
                'tes_tulis_id' => 1,
                'kategori_id'  => 5, // Bahasa Inggris
                'pertanyaan'   => $soalData['pertanyaan'],
                'skor'         => 2,
            ]);

            foreach ($soalData['pilihan'] as $index => $teks) {
                PilihanJawaban::create([
                    'soal_tes_id'  => $soal->id,
                    'teks_pilihan' => $teks,
                    'kode_pilihan' => chr(65 + $index), // A, B, C, ...
                    'benar'        => $teks === $soalData['jawaban'],
                    'urutan'       => $index + 1,
                ]);
            }
        }
    }
}
