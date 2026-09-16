<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LandingVideo;

class LandingVideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $videos = [
            [
                'youtube_video_id' => 'uuNixnANYwY',
                'title'            => 'Dari SMK lintas jurusan, sekarang belajar jadi bidan 💙',
                'sort_order'       => 1,
                'is_active'        => true,
            ],
            [
                'youtube_video_id' => 'izureMg2gLI',
                'title'            => 'Laptop GRATIS untuk Mahasiswa Baru! 🎓💻',
                'sort_order'       => 2,
                'is_active'        => true,
            ],
            [
                'youtube_video_id' => 'uuNixnANYwY',
                'title'            => 'Kisah Inspiratif Mahasiswa Kebidanan 💙',
                'sort_order'       => 3,
                'is_active'        => true,
            ],
            [
                'youtube_video_id' => 'izureMg2gLI',
                'title'            => 'Kuliah di STIKes, Dapat Laptop Gratis! 🎁',
                'sort_order'       => 4,
                'is_active'        => true,
            ],
        ];

        foreach ($videos as $video) {
            LandingVideo::firstOrCreate([
                'youtube_video_id' => $video['youtube_video_id'],
                'title'            => $video['title'],
            ], $video);
        }

        $this->command->info('✅ Landing videos seeder berhasil dijalankan!');
    }
}
