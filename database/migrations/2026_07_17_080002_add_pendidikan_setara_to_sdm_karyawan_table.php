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
            $table->string('pendidikan_setara', 30)->nullable()->after('kategori_kerja');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sdm_karyawan', function (Blueprint $table) {
            $table->dropColumn('pendidikan_setara');
        });
    }
};
