<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'password_plaintext')) {
            Schema::table('users', function (Blueprint $table) {
                // Harus TEXT karena ciphertext Laravel lebih panjang dari password asli.
                $table->text('password_plaintext')->nullable()->after('password');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'password_plaintext')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('password_plaintext');
            });
        }
    }
};
