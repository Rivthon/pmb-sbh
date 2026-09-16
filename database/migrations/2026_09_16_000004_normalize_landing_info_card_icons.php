<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $icons = [
            'bxs bx-network-chart' => 'bx bxs-network-chart',
            'bxs bx-badge-check' => 'bx bxs-badge-check',
            'bxs bx-school' => 'bx bxs-school',
            'bxs bx-user-detail' => 'bx bxs-user-detail',
            'bxs bx-briefcase' => 'bx bxs-briefcase',
            'bxs bx-book-content' => 'bx bxs-book-content',
        ];

        foreach ($icons as $old => $new) {
            DB::table('landing_info_cards')->where('icon', $old)->update(['icon' => $new]);
        }
    }

    public function down(): void
    {
        // Normalisasi format ikon tidak perlu dibalik.
    }
};
