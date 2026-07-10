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
        Schema::create('sdm_ruangan_shift', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ruangan_id');
            $table->unsignedBigInteger('shift_id');

            // Override jam khusus ruangan ini (null = pakai jam dari master shift)
            $table->time('jam_masuk_override')->nullable();
            $table->time('jam_keluar_override')->nullable();
            $table->unsignedSmallInteger('toleransi_telat_menit_override')->nullable();

            $table->timestamps();

            $table->foreign('ruangan_id')->references('id')->on('ruangan')->onDelete('cascade');
            $table->foreign('shift_id')->references('id')->on('sdm_jadwal_shift')->onDelete('cascade');
            $table->unique(['ruangan_id', 'shift_id'], 'uniq_ruangan_shift');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_ruangan_shift');
    }
};
