<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kuitansi_approval', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kuitansi_id');
            $table->unsignedBigInteger('disetujui_oleh');
            $table->enum('status', ['waiting', 'approved', 'rejected', 'manual'])->default('waiting');
            $table->string('keterangan', 255)->nullable();
            $table->longText('signature_hash')->nullable();
            $table->string('approved_at', 50)->nullable();
            $table->timestamps();

            $table->foreign('kuitansi_id')->references('id')->on('kuitansi')->onDelete('cascade');
            $table->foreign('disetujui_oleh')->references('id')->on('sdm_karyawan')->onDelete('cascade');

            $table->index(['kuitansi_id', 'disetujui_oleh', 'status'], 'idx_kuitansi_approval_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuitansi_approval');
    }
};
