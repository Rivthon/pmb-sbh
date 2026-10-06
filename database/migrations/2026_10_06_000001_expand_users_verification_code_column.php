<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // OTP disimpan sebagai hash bcrypt (60 karakter), bukan angka mentah.
            $table->string('verification_code', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Hash yang sudah tersimpan tidak muat jika kolom dikembalikan menjadi 6 karakter.
        DB::table('users')->update(['verification_code' => null]);

        Schema::table('users', function (Blueprint $table) {
            $table->string('verification_code', 6)->nullable()->change();
        });
    }
};
