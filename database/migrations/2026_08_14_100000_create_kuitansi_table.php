<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kuitansi', function (Blueprint $table) {
            $table->id();
            $table->string('nomor', 30)->unique();
            $table->date('tanggal');
            $table->string('diterima_dari', 150)->nullable();
            $table->decimal('jumlah', 16, 2);
            $table->text('keterangan');
            $table->unsignedBigInteger('penerima_id')->nullable();
            $table->string('penerima_nama', 100);
            $table->string('metode_bayar', 50)->default('Tunai');
            $table->enum('status', ['pending', 'waiting', 'approved', 'rejected', 'manual', 'dibatalkan'])->default('waiting');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('dibatalkan_by')->nullable();
            $table->timestamp('dibatalkan_at')->nullable();
            $table->string('alasan_batal', 255)->nullable();
            $table->string('qr_hash', 255)->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->boolean('is_valid')->default(false);
            $table->string('docstore_key', 255)->nullable();
            $table->timestamp('docstore_synced_at')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('dibatalkan_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('penerima_id')->references('id')->on('sdm_karyawan')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuitansi');
    }
};
