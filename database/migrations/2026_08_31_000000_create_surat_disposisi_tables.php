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
        Schema::create('surat_disposisi', function (Blueprint $table) {
            $table->id();
            $table->string('no_agenda', 100)->unique();
            $table->string('surat_masuk_id', 100)->nullable()->comment('ID/Key dari surat terarsip docstore jika mention');
            $table->date('tgl_surat');
            $table->string('no_surat', 150);
            $table->string('perihal', 255);
            $table->string('asal_surat', 255);
            $table->text('catatan')->nullable();
            
            $table->string('diterima_oleh', 150)->nullable();
            $table->date('tgl_diterima')->nullable();
            $table->time('jam_diterima')->nullable();
            
            $table->unsignedBigInteger('direktur_id')->nullable();
            $table->string('signature_hash', 64)->nullable()->unique();
            $table->unsignedBigInteger('signature_cert_id')->nullable();
            $table->timestamp('signed_at')->nullable();
            
            $table->enum('status', ['draft', 'signed', 'dispatched', 'archived'])->default('draft');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('surat_disposisi_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surat_disposisi_id');
            $table->unsignedBigInteger('jabatan_id')->nullable();
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('nama_tujuan', 150);
            
            $table->boolean('is_info')->default(false);
            $table->boolean('is_action')->default(false);
            $table->boolean('is_arsip')->default(false);
            
            $table->enum('status_tindak_lanjut', ['pending', 'read', 'done'])->default('pending');
            $table->text('catatan_penerima')->nullable();
            $table->string('paraf', 255)->nullable();
            $table->timestamp('tgl_paraf')->nullable();
            $table->timestamps();

            $table->foreign('surat_disposisi_id')->references('id')->on('surat_disposisi')->onDelete('cascade');
        });

        // Tabel template penomoran agenda disposisi dinamis jika belum ada
        if (!Schema::hasTable('surat_disposisi_setting')) {
            Schema::create('surat_disposisi_setting', function (Blueprint $table) {
                $table->id();
                $table->string('key', 50)->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_disposisi_detail');
        Schema::dropIfExists('surat_disposisi');
        Schema::dropIfExists('surat_disposisi_setting');
    }
};
