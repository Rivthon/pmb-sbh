<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_info_cards', function (Blueprint $table) {
            $table->id();
            $table->string('section', 40);
            $table->string('title', 120);
            $table->text('description')->nullable();
            $table->json('items')->nullable();
            $table->string('icon', 100);
            $table->string('color', 20);
            $table->string('variant', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['section', 'is_active', 'sort_order']);
        });

        $now = now();
        DB::table('landing_info_cards')->insert([
            ['section' => 'advantages', 'title' => 'Jaringan Karier Luas', 'description' => 'Akses prioritas karier melalui kerja sama eksklusif dengan RS Azra dan jaringan industri kesehatan terkemuka.', 'items' => null, 'icon' => 'bxs bx-network-chart', 'color' => 'warning', 'variant' => null, 'is_active' => true, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['section' => 'advantages', 'title' => 'Terakreditasi Resmi', 'description' => 'Mutu pendidikan terjamin. Program studi D3 Kebidanan, S1 Farmasi, dan S1 Gizi telah terakreditasi oleh LAM-PTKes.', 'items' => null, 'icon' => 'bxs bx-badge-check', 'color' => 'success', 'variant' => null, 'is_active' => true, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['section' => 'advantages', 'title' => 'Fasilitas Modern', 'description' => 'Pembelajaran didukung laboratorium lengkap dan fasilitas praktik langsung berstandar klinis di lingkungan RS Azra.', 'items' => null, 'icon' => 'bxs bx-school', 'color' => 'primary', 'variant' => null, 'is_active' => true, 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['section' => 'advantages', 'title' => 'Dosen Praktisi', 'description' => 'Dibimbing langsung oleh para dokter, praktisi medis, dan akademisi berpengalaman di bidangnya.', 'items' => null, 'icon' => 'bxs bx-user-detail', 'color' => 'info', 'variant' => null, 'is_active' => true, 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['section' => 'advantages', 'title' => 'Peluang Magang', 'description' => 'Pengalaman kerja nyata melalui program magang terstruktur di Rumah Sakit dan instansi kesehatan mitra.', 'items' => null, 'icon' => 'bxs bx-briefcase', 'color' => 'danger', 'variant' => null, 'is_active' => true, 'sort_order' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['section' => 'advantages', 'title' => 'Kurikulum Adaptif', 'description' => 'Materi pembelajaran yang terus diperbarui mengikuti perkembangan teknologi medis terkini.', 'items' => null, 'icon' => 'bxs bx-book-content', 'color' => 'dark', 'variant' => null, 'is_active' => true, 'sort_order' => 6, 'created_at' => $now, 'updated_at' => $now],
            ['section' => 'cost_info', 'title' => 'Periode Pendaftaran', 'description' => null, 'items' => json_encode(['Gel I: Oktober - Januari 2026', 'Gel II: Februari - Mei 2026', 'Gel III: Juni - Agustus 2026']), 'icon' => 'bx bx-calendar', 'color' => 'warning', 'variant' => 'periode', 'is_active' => true, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['section' => 'cost_info', 'title' => 'Biaya Sudah Termasuk', 'description' => null, 'items' => json_encode(['Biaya operasional pendidikan (SKS, Lab, UTS/UAS)', 'SPP, Uang Pendaftaran, dan Jas Almamater', 'Khusus D3 Kebidanan: 3 stel seragam', 'Jas Lab untuk Prodi Farmasi & Gizi', 'Laptop Gratis untuk Mahasiswa Baru']), 'icon' => 'bx bx-check-shield', 'color' => 'success', 'variant' => 'termasuk', 'is_active' => true, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['section' => 'cost_info', 'title' => 'Biaya Belum Termasuk', 'description' => null, 'items' => json_encode(['Biaya Wisuda dan Ujian Praktek Klinis/Lapangan', 'Khusus D3 Kebidanan: belum termasuk Ujian Kompetensi, UAP, dan kegiatan PKMD']), 'icon' => 'bx bx-info-circle', 'color' => 'danger', 'variant' => 'belum', 'is_active' => true, 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_info_cards');
    }
};
