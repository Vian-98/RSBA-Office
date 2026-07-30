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
            $table->string('bpjs_kesehatan', 50)->nullable()->after('npwp');
            $table->string('bpjs_tk', 50)->nullable()->after('bpjs_kesehatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sdm_karyawan', function (Blueprint $table) {
            $table->dropColumn(['bpjs_kesehatan', 'bpjs_tk']);
        });
    }
};
