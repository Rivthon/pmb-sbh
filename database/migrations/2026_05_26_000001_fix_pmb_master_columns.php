<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurusan', function (Blueprint $table) {
            if (!Schema::hasColumn('jurusan', 'kd_jurusan')) {
                $table->string('kd_jurusan', 50)->nullable()->unique()->after('id');
            }
        });

        Schema::table('periodes', function (Blueprint $table) {
            if (!Schema::hasColumn('periodes', 'tanggal_tes')) {
                $table->date('tanggal_tes')->nullable()->after('status_periode');
            }

            if (!Schema::hasColumn('periodes', 'linked')) {
                $table->string('linked')->nullable()->after('tanggal_tes');
            }
        });

        $userColumns = Schema::getColumnListing('users');

        Schema::table('users', function (Blueprint $table) use ($userColumns) {
            if (in_array('NISN', $userColumns, true) && !in_array('nisn', $userColumns, true)) {
                $table->renameColumn('NISN', 'nisn');
            }

            if (in_array('NIK', $userColumns, true) && !in_array('nik', $userColumns, true)) {
                $table->renameColumn('NIK', 'nik');
            }

            if (in_array('img_izajsah', $userColumns, true) && !in_array('img_ijazah', $userColumns, true)) {
                $table->renameColumn('img_izajsah', 'img_ijazah');
            }
        });

        $userColumns = Schema::getColumnListing('users');

        Schema::table('users', function (Blueprint $table) use ($userColumns) {
            if (!Schema::hasColumn('users', 'img_ijazah')) {
                $table->string('img_ijazah')->nullable()->after('img_bukti');
            }

            if (!Schema::hasColumn('users', 'password_plaintext')) {
                $table->string('password_plaintext')->nullable()->after('password');
            }

            if (!Schema::hasColumn('users', 'verification_code')) {
                $table->string('verification_code')->nullable()->after('remember_token');
            }

            $table->index(['role', 'periode_id', 'gelombang_id', 'status_pemb'], 'users_pmb_filter_idx');
            $table->index(['role', 'created_at'], 'users_role_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_created_idx');
            $table->dropIndex('users_pmb_filter_idx');

            if (Schema::hasColumn('users', 'verification_code')) {
                $table->dropColumn('verification_code');
            }

            if (Schema::hasColumn('users', 'password_plaintext')) {
                $table->dropColumn('password_plaintext');
            }

            if (Schema::hasColumn('users', 'img_ijazah')) {
                $table->dropColumn('img_ijazah');
            }
        });

        Schema::table('periodes', function (Blueprint $table) {
            if (Schema::hasColumn('periodes', 'linked')) {
                $table->dropColumn('linked');
            }

            if (Schema::hasColumn('periodes', 'tanggal_tes')) {
                $table->dropColumn('tanggal_tes');
            }
        });

        Schema::table('jurusan', function (Blueprint $table) {
            if (Schema::hasColumn('jurusan', 'kd_jurusan')) {
                $table->dropUnique(['kd_jurusan']);
                $table->dropColumn('kd_jurusan');
            }
        });
    }
};
