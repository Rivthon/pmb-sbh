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
            $table->timestamp('verification_code_expires_at')->nullable()->after('verification_code');
            $table->unsignedTinyInteger('verification_attempts')->default(0)->after('verification_code_expires_at');
        });

        DB::table('users')->update(['verification_code' => null]);

        if (Schema::hasColumn('users', 'password_plaintext')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('password_plaintext');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password_plaintext')->nullable()->after('password');
            $table->dropColumn(['verification_code_expires_at', 'verification_attempts']);
        });
    }
};
