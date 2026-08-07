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
        Schema::create('sdm_cuti_bersama_karyawan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cuti_bersama_id');
            $table->unsignedBigInteger('karyawan_id');
            $table->boolean('is_ikut')->default(true); // true = Ikut, false = Dikecualikan
            $table->string('catatan')->nullable();
            $table->timestamps();

            $table->foreign('cuti_bersama_id')->references('id')->on('sdm_cuti_bersama')->onDelete('cascade');
            $table->foreign('karyawan_id')->references('id')->on('sdm_karyawan')->onDelete('cascade');
            $table->unique(['cuti_bersama_id', 'karyawan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_cuti_bersama_karyawan');
    }
};
