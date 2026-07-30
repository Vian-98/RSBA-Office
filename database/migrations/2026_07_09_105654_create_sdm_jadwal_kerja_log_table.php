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
        Schema::create('sdm_jadwal_kerja_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jadwal_kerja_id');
            $table->unsignedBigInteger('detail_id'); // Hari apa yang diubah
            $table->unsignedBigInteger('karyawan_id'); // Karyawan yang jadwalnya diubah
            
            $table->unsignedBigInteger('shift_lama_id')->nullable();
            $table->unsignedBigInteger('shift_baru_id')->nullable();
            
            $table->unsignedBigInteger('diubah_oleh'); // Koordinator yang mengubah
            $table->timestamps();

            $table->foreign('jadwal_kerja_id')->references('id')->on('sdm_jadwal_kerja')->onDelete('cascade');
            $table->foreign('detail_id')->references('id')->on('sdm_jadwal_kerja_detail')->onDelete('cascade');
            $table->foreign('karyawan_id')->references('id')->on('sdm_karyawan')->onDelete('cascade');
            $table->foreign('diubah_oleh')->references('id')->on('sdm_karyawan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_jadwal_kerja_log');
    }
};
