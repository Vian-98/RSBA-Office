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
        Schema::table('sdm_payroll_slips', function (Blueprint $table) {
            $table->unsignedTinyInteger('golongan')->nullable()->after('periode');
            $table->decimal('masa_kerja_tahun', 5, 2)->nullable()->after('golongan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sdm_payroll_slips', function (Blueprint $table) {
            $table->dropColumn(['golongan', 'masa_kerja_tahun']);
        });
    }
};
