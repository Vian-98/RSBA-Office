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
        Schema::create('sdm_tukar_jadwal_dokter', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dokter_pengaju_id'); // Dokter A
            $table->unsignedBigInteger('jadwal_detail_pengaju_id'); // Shift A
            $table->unsignedBigInteger('dokter_pengganti_id'); // Dokter B
            $table->unsignedBigInteger('jadwal_detail_pengganti_id'); // Shift B
            $table->text('alasan')->nullable();
            $table->string('status', 40)->default('MENUNGGU_KONFIRMASI_DOKTER');
            $table->text('catatan_wadir')->nullable();
            $table->timestamp('konfirmasi_dokter_at')->nullable();
            $table->timestamp('disetujui_wadir_at')->nullable();
            $table->unsignedBigInteger('disetujui_wadir_oleh')->nullable();
            $table->timestamps();

            $table->foreign('dokter_pengaju_id')->references('id')->on('sdm_karyawan')->onDelete('cascade');
            $table->foreign('dokter_pengganti_id')->references('id')->on('sdm_karyawan')->onDelete('cascade');
            $table->foreign('jadwal_detail_pengaju_id')->references('id')->on('sdm_jadwal_kerja_detail')->onDelete('cascade');
            $table->foreign('jadwal_detail_pengganti_id')->references('id')->on('sdm_jadwal_kerja_detail')->onDelete('cascade');
            $table->foreign('disetujui_wadir_oleh')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_tukar_jadwal_dokter');
    }
};
