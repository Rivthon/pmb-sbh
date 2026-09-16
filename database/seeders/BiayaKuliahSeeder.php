<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BiayaKuliah;

class BiayaKuliahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Nilai biaya dalam Rupiah (bukan jutaan).
     * Contoh: 12000000 = Rp 12.000.000
     */
    public function run(): void
    {
        $prodiData = [
            [
                'key'       => 'd3',
                'nama'      => 'D3 Kebidanan',
                'semesters' => 6,
                'gel' => [
                    1 => [12000000, 12000000, 12000000, 12000000, 11500000, 11000000],
                    2 => [13000000, 12000000, 12000000, 12000000, 11500000, 11000000],
                    3 => [14000000, 12000000, 12000000, 12000000, 11500000, 11000000],
                ],
            ],
            [
                'key'       => 'farmasi',
                'nama'      => 'S1 Farmasi Reguler',
                'semesters' => 8,
                'gel' => [
                    1 => [13600000, 13100000, 12500000, 11500000, 11200000, 11000000, 10500000, 10000000],
                    2 => [14600000, 13100000, 12500000, 11500000, 11200000, 11000000, 10500000, 10000000],
                    3 => [15600000, 13100000, 12500000, 11500000, 11200000, 11000000, 10500000, 10000000],
                ],
            ],
            [
                'key'       => 'karyawan',
                'nama'      => 'S1 Farmasi Karyawan',
                'semesters' => 8,
                'gel' => [
                    1 => [14600000, 14100000, 13500000, 12500000, 12200000, 12000000, 11500000, 11000000],
                    2 => [15600000, 14100000, 13500000, 12500000, 12200000, 12000000, 11500000, 11000000],
                    3 => [16600000, 14100000, 13500000, 12500000, 12200000, 12000000, 11500000, 11000000],
                ],
            ],
            [
                'key'       => 'gizi',
                'nama'      => 'S1 Gizi',
                'semesters' => 8,
                'gel' => [
                    1 => [13500000, 12500000, 11000000, 10500000, 10200000, 10000000, 10000000, 9500000],
                    2 => [14500000, 12500000, 11000000, 10500000, 10200000, 10000000, 10000000, 9500000],
                    3 => [15500000, 12500000, 11000000, 10500000, 10200000, 10000000, 10000000, 9500000],
                ],
            ],
        ];

        foreach ($prodiData as $prodi) {
            foreach ($prodi['gel'] as $gelombang => $biayaList) {
                foreach ($biayaList as $index => $biaya) {
                    BiayaKuliah::updateOrCreate(
                        [
                            'prodi_key'  => $prodi['key'],
                            'gelombang'  => $gelombang,
                            'semester'   => $index + 1,
                        ],
                        [
                            'prodi_nama'      => $prodi['nama'],
                            'jumlah_semester'  => $prodi['semesters'],
                            'biaya'            => $biaya,
                        ]
                    );
                }
            }
        }

        $this->command->info('✅ BiayaKuliah seeder: 90 rows seeded successfully.');
    }
}
