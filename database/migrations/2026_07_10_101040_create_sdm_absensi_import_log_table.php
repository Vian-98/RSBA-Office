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
        Schema::create('sdm_absensi_import_log', function (Blueprint $table) {
            $table->id();
            $table->string('nama_file', 150);
            $table->date('periode_awal');
            $table->date('periode_akhir');
            $table->unsignedSmallInteger('total_baris')->default(0);
            $table->unsignedSmallInteger('baris_matched')->default(0);
            $table->unsignedSmallInteger('baris_unmatched')->default(0);
            $table->unsignedSmallInteger('baris_anomali')->default(0);
            $table->enum('status', ['diunggah', 'direkonsiliasi', 'dikunci'])->default('diunggah');
            $table->unsignedBigInteger('diunggah_oleh');
            $table->timestamp('dikunci_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_absensi_import_log');
    }
};
