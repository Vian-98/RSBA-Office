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
        Schema::create('sdm_jadwal_aturan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bagian_id');
            $table->string('kode', 40);   // lihat enum KodeAturanJadwal
            $table->string('nilai', 100); // disimpan string, di-cast sesuai tipe kode saat dipakai
            $table->text('keterangan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('bagian_id')->references('id')->on('bagian')->onDelete('cascade');
            $table->unique(['bagian_id', 'kode'], 'uniq_aturan_bagian_kode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_jadwal_aturan');
    }
};
