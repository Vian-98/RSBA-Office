<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_sp3_approval', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_sp3_approval', 'tahap')) {
                $table->enum('tahap', ['verifikasi_keuangan', 'ttd_atasan'])
                    ->default('ttd_atasan')
                    ->after('surat_sp3_id');
            }
        });

        Schema::table('surat_sp3_approval', function (Blueprint $table) {
            $table->unique(['surat_sp3_id', 'tahap'], 'uniq_sp3_approval_tahap');
        });
    }

    public function down(): void
    {
        Schema::table('surat_sp3_approval', function (Blueprint $table) {
            $table->dropUnique('uniq_sp3_approval_tahap');
            $table->dropColumn('tahap');
        });
    }
};
