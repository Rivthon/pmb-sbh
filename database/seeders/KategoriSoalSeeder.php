<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KategoriSoalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('kategori_soal')->insert([
            ['nama_kategori' => 'Wawasan Kebangsaan'],
            ['nama_kategori' => 'Intelegensi Umum'],
            ['nama_kategori' => 'Logika'],
            ['nama_kategori' => 'Matematika'],
            ['nama_kategori' => 'Bahasa Inggris'],
        ]);
    }
}
