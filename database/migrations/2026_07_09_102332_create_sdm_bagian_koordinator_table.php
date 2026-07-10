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
        Schema::create('sdm_bagian_koordinator', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bagian_id');
            $table->unsignedBigInteger('karyawan_id');
            $table->boolean('aktif')->default(true); // soft-disable tanpa hapus histori
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('bagian_id')->references('id')->on('bagian')->onDelete('cascade');
            $table->foreign('karyawan_id')->references('id')->on('sdm_karyawan')->onDelete('cascade');
            $table->unique(['bagian_id', 'karyawan_id'], 'uniq_bagian_koordinator');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_bagian_koordinator');
    }
};
