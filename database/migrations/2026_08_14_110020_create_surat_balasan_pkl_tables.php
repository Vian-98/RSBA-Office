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
        Schema::create('surat_balasan_pkl', function (Blueprint $table) {
            $table->id();
            $table->string('no', 100);
            $table->year('tahun');
            $table->date('tgl');
            $table->string('tujuan_nama', 150)->nullable();
            $table->string('tujuan_universitas', 150);
            $table->text('tujuan_alamat')->nullable();
            $table->string('nomor_surat_masuk', 100)->nullable();
            $table->date('tgl_surat_masuk')->nullable();
            $table->string('prodi', 100);
            $table->integer('jumlah_mahasiswa')->default(1);
            $table->integer('lama_praktik_bulan')->default(1);
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');

            // Snapshot Tarif pada saat surat dibuat
            $table->double('snap_biaya_praktik')->default(0);
            $table->double('snap_biaya_orientasi')->default(0);
            $table->string('snap_nomor_sk', 100)->nullable();

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

        Schema::create('surat_balasan_pkl_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surat_balasan_pkl_id');
            $table->string('nama', 150);
            $table->string('npm', 50)->nullable();
            $table->timestamps();

            $table->foreign('surat_balasan_pkl_id')->references('id')->on('surat_balasan_pkl')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_balasan_pkl_mahasiswa');
        Schema::dropIfExists('surat_balasan_pkl');
    }
};
