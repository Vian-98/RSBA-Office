<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jm_jasa', function (Blueprint $table) {
            $table->index(['jm_prosentase_id', 'dokter']);
        });

        Schema::table('jm_prosentase', function (Blueprint $table) {
            $table->index(['id', 'jm_pasien_id']);
        });

        Schema::table('jm_dokter', function (Blueprint $table) {
            $table->index(['jm_pasien_id', 'dokter']);
            $table->index(['status']); // optional but helpful
        });

        DB::unprepared("DROP VIEW IF EXISTS view_jm_dokter_jasa");
        DB::unprepared("
        CREATE VIEW view_jm_dokter_jasa AS
        SELECT 
            j.jm_prosentase_id,
            j.dokter,
            j.status AS status_jasa,
            j.jasa,
            COALESCE(d.jumlah, 0) AS jumlah,
            CASE d.status
                WHEN 'sp'      THEN 'Spesialis'
                WHEN 'um'      THEN 'Umum'
                WHEN 'an'      THEN 'Anastesi'
                WHEN 'dpjp'    THEN 'DPJP'
                WHEN 'um_s'    THEN 'Umum Sertifikat'
                WHEN 'sppdkgh' THEN 'Sp.PD, KGH'
                WHEN 'dpjp_hd' THEN 'DPJP HD'
                ELSE d.status
            END AS status_label,
            d.status AS status_code
        FROM jm_jasa j
        LEFT JOIN jm_prosentase p
            ON j.jm_prosentase_id = p.id
        LEFT JOIN (
            SELECT 
                jm_pasien_id,
                dokter,
                SUM(jumlah) AS jumlah,
                MAX(status) AS status
            FROM jm_dokter
            GROUP BY jm_pasien_id, dokter
        ) d
            ON p.jm_pasien_id = d.jm_pasien_id
            AND j.dokter = d.dokter;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jm_jasa', function (Blueprint $table) {
            // kalau jm_prosentase_id adalah FK
            $table->dropForeign(['jm_prosentase_id']); // sesuaikan kalau nama FK custom

            // drop index composite
            $table->dropIndex(['jm_prosentase_id', 'dokter']);

            // recreate foreign
            $table->foreign('jm_prosentase_id')
                ->references('id')
                ->on('jm_prosentase')
                ->onDelete('cascade');
        });

        Schema::table('jm_prosentase', function (Blueprint $table) {
            // kalau jm_pasien_id adalah FK
            $table->dropForeign(['jm_pasien_id']);

            $table->dropIndex(['id', 'jm_pasien_id']);

            // recreat foreign
            $table->foreign('jm_pasien_id')
                ->references('id')
                ->on('jm_pasien')
                ->onDelete('cascade');
        });

        Schema::table('jm_dokter', function (Blueprint $table) {
            // kalau jm_pasien_id adalah FK
            $table->dropForeign(['jm_pasien_id']);

            $table->dropIndex(['jm_pasien_id', 'dokter']);
            $table->dropIndex(['status']);

            // Recreate
            $table->foreign('jm_pasien_id')
                ->references('id')
                ->on('jm_pasien')
                ->onDelete('cascade');
        });

        DB::unprepared("DROP VIEW IF EXISTS view_jm_dokter_jasa");
    }
};
