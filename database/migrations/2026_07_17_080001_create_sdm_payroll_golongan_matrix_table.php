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
        Schema::create('sdm_payroll_golongan_matrix', function (Blueprint $table) {
            $table->id();
            $table->string('kelompok_pendidikan', 30);
            $table->unsignedTinyInteger('masa_kerja_min');
            $table->unsignedTinyInteger('golongan');
            $table->unsignedInteger('urutan_kelompok')->default(0);
            $table->timestamps();

            $table->unique(['kelompok_pendidikan', 'masa_kerja_min'], 'idx_kelompok_masa_kerja_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_payroll_golongan_matrix');
    }
};
