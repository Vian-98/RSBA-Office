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
        Schema::create('sdm_jabatan', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 35);
            $table->string('kode_surat', 25)->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('bagian_id')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('sdm_jabatan')->onDelete('cascade');
            $table->foreign('bagian_id')->references('id')->on('bagian')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_jabatan');
    }
};
