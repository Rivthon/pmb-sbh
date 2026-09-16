<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_features', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->string('description', 160);
            $table->string('icon', 100);
            $table->string('color', 20);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        $now = now();
        DB::table('hero_features')->insert([
            ['title' => 'Free Laptop', 'description' => 'Untuk semua Mahasiswa', 'icon' => 'bx bx-laptop', 'color' => 'warning', 'is_active' => true, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'Gratis Daftar', 'description' => 'Biaya formulir Rp 0', 'icon' => 'bxs bx-wallet-alt', 'color' => 'success', 'is_active' => true, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'Beasiswa', 'description' => 'Akademik & Non-Akad', 'icon' => 'bxs bx-graduation', 'color' => 'info', 'is_active' => true, 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'Bisa Dicicil', 'description' => 'Biaya terjangkau', 'icon' => 'bxs bx-credit-card', 'color' => 'danger', 'is_active' => true, 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_features');
    }
};
