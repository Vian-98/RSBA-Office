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
        Schema::create('surat_perintah_tugas', function (Blueprint $table) {
            $table->id();
            $table->string('no', 100);
            $table->year('tahun');
            $table->date('tgl');
            $table->text('perihal');
            $table->string('hari_tanggal', 150);
            $table->string('waktu', 100);
            $table->string('tempat', 255);

            $table->unsignedBigInteger('jabatan_id')->nullable(); // Direktur
            $table->unsignedBigInteger('disetujui_oleh')->nullable(); // Karyawan Direktur
            $table->string('status', 30)->default('pending');
            $table->text('catatan_approval')->nullable();
            $table->unsignedBigInteger('created_by');

            // Integrasi Docstore & TTD Digital
            $table->string('docstore_key', 100)->nullable();
            $table->timestamp('docstore_synced_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->string('qr_verification_hash', 100)->nullable();

            $table->timestamps();

            $table->foreign('jabatan_id')->references('id')->on('sdm_jabatan')->nullOnDelete();
            $table->foreign('disetujui_oleh')->references('id')->on('sdm_karyawan')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        // Tabel karyawan yang ditugaskan (multi-select dari sdm_karyawan)
        Schema::create('surat_perintah_tugas_karyawan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surat_perintah_tugas_id');
            $table->unsignedBigInteger('karyawan_id');
            $table->timestamps();

            $table->foreign('surat_perintah_tugas_id', 'fk_spt_tugas')
                ->references('id')->on('surat_perintah_tugas')
                ->onDelete('cascade');
            $table->foreign('karyawan_id', 'fk_spt_karyawan')
                ->references('id')->on('sdm_karyawan')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_perintah_tugas_karyawan');
        Schema::dropIfExists('surat_perintah_tugas');
    }
};
