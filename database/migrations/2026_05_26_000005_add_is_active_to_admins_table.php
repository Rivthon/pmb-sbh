<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('admins') && !Schema::hasColumn('admins', 'is_active')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('password');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('admins') && Schema::hasColumn('admins', 'is_active')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
