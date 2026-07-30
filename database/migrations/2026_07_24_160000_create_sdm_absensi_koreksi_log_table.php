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
        Schema::create('sdm_absensi_koreksi_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('detail_id');
            $table->unsignedBigInteger('karyawan_id');
            $table->date('tanggal');
            
            $table->string('status_lama', 50)->nullable();
            $table->string('status_baru', 50)->nullable();
            
            $table->timestamp('absen_masuk_lama')->nullable();
            $table->timestamp('absen_masuk_baru')->nullable();
            
            $table->timestamp('absen_keluar_lama')->nullable();
            $table->timestamp('absen_keluar_baru')->nullable();
            
            $table->text('catatan_lama')->nullable();
            $table->text('catatan_baru')->nullable();
            
            $table->unsignedBigInteger('user_id'); // Who made the edit
            $table->timestamps();

            $table->foreign('detail_id')->references('id')->on('sdm_jadwal_kerja_detail')->onDelete('cascade');
            $table->foreign('karyawan_id')->references('id')->on('sdm_karyawan')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['detail_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_absensi_koreksi_log');
    }
};
