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
        Schema::table('sdm_karyawan', function (Blueprint $table) {
            $table->enum('kategori_kerja', ['shift', 'reguler'])->default('reguler')->after('status');
            $table->unsignedBigInteger('ruangan_id')->nullable()->after('kategori_kerja');
            $table->foreign('ruangan_id')->references('id')->on('ruangan')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sdm_karyawan', function (Blueprint $table) {
            $table->dropForeign(['ruangan_id']);
            $table->dropColumn('ruangan_id');
            $table->dropColumn('kategori_kerja');
        });
    }
};
