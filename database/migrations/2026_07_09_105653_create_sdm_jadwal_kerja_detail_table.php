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
        Schema::create('sdm_jadwal_kerja_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jadwal_kerja_id');
            $table->unsignedBigInteger('karyawan_id');
            $table->unsignedBigInteger('shift_id')->nullable(); // null = LIBUR tanggal itu
            $table->date('tanggal');
            $table->text('catatan')->nullable();

            // Placeholder untuk fase Absensi nanti — dibuat sekarang supaya tidak migration ulang
            $table->enum('status_kehadiran', [
                'belum_dicek', 'hadir', 'terlambat', 'pulang_cepat', 'tidak_hadir', 'cuti', 'izin',
            ])->default('belum_dicek');
            $table->timestamp('absen_masuk_at')->nullable();
            $table->timestamp('absen_keluar_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('jadwal_kerja_id')->references('id')->on('sdm_jadwal_kerja')->onDelete('cascade');
            $table->foreign('karyawan_id')->references('id')->on('sdm_karyawan')->onDelete('cascade');
            $table->foreign('shift_id')->references('id')->on('sdm_jadwal_shift')->onDelete('restrict');

            $table->unique(['jadwal_kerja_id', 'karyawan_id', 'tanggal'], 'uniq_detail_pegawai_tanggal');
            $table->index(['karyawan_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_jadwal_kerja_detail');
    }
};
