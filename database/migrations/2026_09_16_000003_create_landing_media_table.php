<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_media', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->string('label', 100);
            $table->string('path');
            $table->timestamps();
        });

        $now = now();
        DB::table('landing_media')->insert([
            ['key' => 'hero_image', 'label' => 'Gambar Hero Utama', 'path' => 'dashboard_assets/assets/img/front-pages/backgrounds/foto_utama.png', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'auth_image', 'label' => 'Gambar Login & Registrasi', 'path' => 'dashboard_assets/assets/img/front-pages/backgrounds/foto_utama.png', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_media');
    }
};
