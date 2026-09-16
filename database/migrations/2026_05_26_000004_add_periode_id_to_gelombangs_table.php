<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add nullable column first
        Schema::table('gelombangs', function (Blueprint $table) {
            $table->unsignedBigInteger('periode_id')->nullable()->after('id');
        });

        // 2. Map existing gelombangs to first active (or first available) period
        $activePeriode = DB::table('periodes')->where('status_periode', 'aktif')->first()
            ?? DB::table('periodes')->first();

        if ($activePeriode) {
            DB::table('gelombangs')->whereNull('periode_id')->update([
                'periode_id' => $activePeriode->id
            ]);
        }

        // 3. Clean up duplicates defensively to prevent unique constraint failures
        $duplicates = DB::table('gelombangs')
            ->select('periode_id', 'nama_gelombang', DB::raw('COUNT(*) as count'))
            ->groupBy('periode_id', 'nama_gelombang')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            $ids = DB::table('gelombangs')
                ->where('periode_id', $duplicate->periode_id)
                ->where('nama_gelombang', $duplicate->nama_gelombang)
                ->orderBy('id')
                ->pluck('id')
                ->toArray();
            
            array_shift($ids); // Keep the first record
            
            // Rename duplicates cleanly
            foreach ($ids as $index => $id) {
                DB::table('gelombangs')->where('id', $id)->update([
                    'nama_gelombang' => $duplicate->nama_gelombang . ' - Duplikat ' . ($index + 1)
                ]);
            }
        }

        // 4. Enforce constraints
        Schema::table('gelombangs', function (Blueprint $table) {
            $table->unsignedBigInteger('periode_id')->nullable(false)->change();
            $table->foreign('periode_id')->references('id')->on('periodes')->onDelete('cascade');
            $table->unique(['periode_id', 'nama_gelombang'], 'gelombangs_periode_nama_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gelombangs', function (Blueprint $table) {
            $table->dropUnique('gelombangs_periode_nama_unique');
            $table->dropForeign(['periode_id']);
            $table->dropColumn('periode_id');
        });
    }
};
