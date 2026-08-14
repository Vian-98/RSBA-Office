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
        Schema::create('surat_sp3_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surat_sp3_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->string('nama_pelaku');
            $table->string('jabatan_pelaku')->nullable();
            $table->string('aksi');
            $table->string('status', 30);
            $table->text('catatan')->nullable();
            $table->string('signature_hash')->nullable();
            $table->timestamps();

            $table->foreign('surat_sp3_id')->references('id')->on('surat_sp3')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_sp3_logs');
    }
};
