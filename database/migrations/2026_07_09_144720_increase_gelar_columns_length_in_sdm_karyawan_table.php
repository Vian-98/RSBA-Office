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
            $table->string('gelar_depan', 50)->nullable()->change();
            $table->string('gelar_belakang', 150)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sdm_karyawan', function (Blueprint $table) {
            $table->string('gelar_depan', 10)->nullable()->change();
            $table->string('gelar_belakang', 10)->nullable()->change();
        });
    }
};
