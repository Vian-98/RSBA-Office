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
        Schema::table('sdm_cuti_bersama_karyawan', function (Blueprint $table) {
            if (!Schema::hasColumn('sdm_cuti_bersama_karyawan', 'override_status')) {
                $table->string('override_status', 30)->default('auto')->after('is_ikut');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sdm_cuti_bersama_karyawan', function (Blueprint $table) {
            if (Schema::hasColumn('sdm_cuti_bersama_karyawan', 'override_status')) {
                $table->dropColumn('override_status');
            }
        });
    }
};
