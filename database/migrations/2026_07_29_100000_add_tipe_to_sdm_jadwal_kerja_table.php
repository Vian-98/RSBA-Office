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
        Schema::table('sdm_jadwal_kerja', function (Blueprint $table) {
            $table->enum('tipe', ['karyawan', 'dokter'])->default('karyawan')->after('tahun');
        });

        // Update tipe = 'dokter' untuk jadwal yang dibuat oleh dokter atau ruangan koordinasi dokter
        try {
            DB::statement("
                UPDATE sdm_jadwal_kerja j
                JOIN sdm_karyawan k ON j.dibuat_oleh = k.id
                JOIN dokter d ON d.karyawan_id = k.id
                SET j.tipe = 'dokter'
            ");
        } catch (\Throwable $e) {
            // Ignore if empty
        }

        // Re-create unique index untuk menyertakan tipe
        try {
            Schema::table('sdm_jadwal_kerja', function (Blueprint $table) {
                $table->dropUnique('uniq_jadwalkerja_ruangan_periode');
            });
        } catch (\Throwable $e) {
            // Index name might vary
        }

        try {
            Schema::table('sdm_jadwal_kerja', function (Blueprint $table) {
                $table->unique(['ruangan_id', 'bulan', 'tahun', 'tipe'], 'uniq_jadwalkerja_ruangan_periode_tipe');
            });
        } catch (\Throwable $e) {
            // Ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sdm_jadwal_kerja', function (Blueprint $table) {
            try {
                $table->dropUnique('uniq_jadwalkerja_ruangan_periode_tipe');
            } catch (\Throwable $e) {}
            $table->dropColumn('tipe');
        });
    }
};
