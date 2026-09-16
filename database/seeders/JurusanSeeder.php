<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Jurusan;
class JurusanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Jurusan::insert([
            ['nama_jurusan' => 'FARMASI', 'deskripsi' => 'S1'],
            ['nama_jurusan' => 'GIZI', 'deskripsi' => 'S1'],
            ['nama_jurusan' => 'KEBIDANAN', 'deskripsi' => 'D3'],
        ]);
    }
}