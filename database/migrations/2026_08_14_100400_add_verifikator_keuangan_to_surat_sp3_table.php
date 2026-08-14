<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_sp3', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_sp3', 'verifikator_keuangan_id')) {
                $table->unsignedBigInteger('verifikator_keuangan_id')->nullable()->after('jabatan_id');
                $table->foreign('verifikator_keuangan_id')->references('id')->on('sdm_karyawan')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('surat_sp3', function (Blueprint $table) {
            $table->dropForeign(['verifikator_keuangan_id']);
            $table->dropColumn('verifikator_keuangan_id');
        });
    }
};
