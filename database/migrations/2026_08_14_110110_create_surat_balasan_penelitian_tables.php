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
        Schema::create('surat_balasan_penelitian', function (Blueprint $table) {
            $table->id();
            $table->string('no', 100);
            $table->year('tahun');
            $table->date('tgl');
            $table->string('tujuan_nama', 150)->nullable();
            $table->string('tujuan_fakultas', 150);
            $table->string('tujuan_universitas', 150);
            $table->text('tujuan_alamat')->nullable();
            $table->string('nomor_surat_masuk', 100)->nullable();
            $table->date('tgl_surat_masuk')->nullable();
            $table->string('perihal_surat_masuk', 255)->default('Izin Penelitian / Presurvey');

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

        // Tabel detail identitas mahasiswa penelitian
        Schema::create('surat_balasan_penelitian_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surat_balasan_penelitian_id');
            $table->string('nama', 150);
            $table->string('npm', 50)->nullable();
            $table->string('fakultas_pt', 150)->nullable();
            $table->text('judul_penelitian')->nullable();
            $table->timestamps();

            $table->foreign('surat_balasan_penelitian_id', 'fk_sbp_mahasiswa')
                ->references('id')->on('surat_balasan_penelitian')
                ->onDelete('cascade');
        });

        // Tabel detail rincian biaya penelitian
        Schema::create('surat_balasan_penelitian_biaya', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surat_balasan_penelitian_id');
            $table->string('keterangan', 150);
            $table->integer('jumlah_orang')->default(1);
            $table->double('jasa_sarana')->default(0);
            $table->double('jasa_pelayanan')->default(0);
            $table->timestamps();

            $table->foreign('surat_balasan_penelitian_id', 'fk_sbp_biaya')
                ->references('id')->on('surat_balasan_penelitian')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_balasan_penelitian_biaya');
        Schema::dropIfExists('surat_balasan_penelitian_mahasiswa');
        Schema::dropIfExists('surat_balasan_penelitian');
    }
};
