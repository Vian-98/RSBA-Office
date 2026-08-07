<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sdm_kary_jabatan', 'bagian_id')) {
            Schema::table('sdm_kary_jabatan', function (Blueprint $table) {
                $table->unsignedBigInteger('bagian_id')->nullable()->after('jabatan_id');
                $table->foreign('bagian_id')
                    ->references('id')
                    ->on('bagian')
                    ->nullOnDelete();
                $table->index('bagian_id', 'idx_kary_jabatan_bagian');
            });
        }

        if (! Schema::hasColumn('sdm_jadwal_kerja', 'bagian_id')) {
            Schema::table('sdm_jadwal_kerja', function (Blueprint $table) {
                $table->unsignedBigInteger('bagian_id')->nullable()->after('ruangan_id');
                $table->foreign('bagian_id')
                    ->references('id')
                    ->on('bagian')
                    ->nullOnDelete();
                $table->index('bagian_id', 'idx_jadwal_kerja_bagian');
            });
        }

        // Backfill assignment departments from the department configured on the job master.
        if (Schema::hasTable('sdm_kary_jabatan') && Schema::hasTable('sdm_jabatan')) {
            $assignments = DB::table('sdm_kary_jabatan as kj')
                ->join('sdm_jabatan as j', 'j.id', '=', 'kj.jabatan_id')
                ->whereNull('kj.bagian_id')
                ->whereNotNull('j.bagian_id')
                ->get(['kj.id', 'j.bagian_id']);

            foreach ($assignments as $assignment) {
                DB::table('sdm_kary_jabatan')
                    ->where('id', $assignment->id)
                    ->update(['bagian_id' => $assignment->bagian_id]);
            }
        }

        // Backfill schedule snapshots from the legacy room mapping first.
        if (Schema::hasTable('sdm_jadwal_kerja') && Schema::hasColumn('ruangan', 'bagian_id')) {
            $schedules = DB::table('sdm_jadwal_kerja as jk')
                ->join('ruangan as r', 'r.id', '=', 'jk.ruangan_id')
                ->whereNull('jk.bagian_id')
                ->whereNotNull('r.bagian_id')
                ->get(['jk.id', 'r.bagian_id']);

            foreach ($schedules as $schedule) {
                DB::table('sdm_jadwal_kerja')
                    ->where('id', $schedule->id)
                    ->update(['bagian_id' => $schedule->bagian_id]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sdm_jadwal_kerja', 'bagian_id')) {
            Schema::table('sdm_jadwal_kerja', function (Blueprint $table) {
                $table->dropForeign(['bagian_id']);
                $table->dropIndex('idx_jadwal_kerja_bagian');
                $table->dropColumn('bagian_id');
            });
        }

        if (Schema::hasColumn('sdm_kary_jabatan', 'bagian_id')) {
            Schema::table('sdm_kary_jabatan', function (Blueprint $table) {
                $table->dropForeign(['bagian_id']);
                $table->dropIndex('idx_kary_jabatan_bagian');
                $table->dropColumn('bagian_id');
            });
        }
    }
};
