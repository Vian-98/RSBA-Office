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
        Schema::table('sdm_absensi_staging', function (Blueprint $table) {
            $table->text('catatan_mesin')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sdm_absensi_staging', function (Blueprint $table) {
            $table->string('catatan_mesin', 100)->nullable()->change();
        });
    }
};
