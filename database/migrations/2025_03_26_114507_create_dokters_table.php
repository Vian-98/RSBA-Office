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
        Schema::create('dokter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained(table: 'sdm_karyawan', indexName: 'id');
            $table->unsignedBigInteger('spesialis_id');
            $table->timestamps();

            $table->foreign('spesialis_id')->references('id')->on('dokter_spesialisasi')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokter');
    }
};
