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
        Schema::table('sdm_jadwal_kerja_detail', function (Blueprint $table) {
            if (!Schema::hasColumn('sdm_jadwal_kerja_detail', 'menit_terlambat')) {
                $table->integer('menit_terlambat')->default(0)->after('status_kehadiran');
            }
            if (!Schema::hasColumn('sdm_jadwal_kerja_detail', 'menit_pulang_cepat')) {
                $table->integer('menit_pulang_cepat')->default(0)->after('menit_terlambat');
            }
            if (!Schema::hasColumn('sdm_jadwal_kerja_detail', 'menit_overtime')) {
                $table->integer('menit_overtime')->default(0)->after('menit_pulang_cepat');
            }

            $table->index(['tanggal', 'status_kehadiran', 'karyawan_id'], 'idx_detail_tgl_status_karyawan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sdm_jadwal_kerja_detail', function (Blueprint $table) {
            $table->dropIndex('idx_detail_tgl_status_karyawan');
            $table->dropColumn(['menit_terlambat', 'menit_pulang_cepat', 'menit_overtime']);
        });
    }
};
