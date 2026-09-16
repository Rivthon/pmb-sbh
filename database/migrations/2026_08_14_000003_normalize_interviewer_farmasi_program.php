<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $programs = DB::table('jurusan')
            ->select(['id', 'nama_jurusan'])
            ->get();

        $farmasi = $programs->first(function ($program): bool {
            $name = strtoupper((string) $program->nama_jurusan);

            return str_contains($name, 'S1')
                && str_contains($name, 'FARMASI')
                && ! str_contains($name, 'KARYAWAN');
        });

        $farmasiKaryawanIds = $programs
            ->filter(function ($program): bool {
                $name = strtoupper((string) $program->nama_jurusan);

                return str_contains($name, 'S1')
                    && str_contains($name, 'FARMASI')
                    && str_contains($name, 'KARYAWAN');
            })
            ->pluck('id');

        if ($farmasi && $farmasiKaryawanIds->isNotEmpty()) {
            DB::table('admins')
                ->whereIn('jurusan_id', $farmasiKaryawanIds)
                ->update(['jurusan_id' => $farmasi->id]);
        }
    }

    public function down(): void
    {
        // The previous account assignment cannot be inferred safely.
    }
};
