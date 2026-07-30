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
        Schema::create('jm_rincian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jm_pasien_id')->unique();
            $table->integer('chosaring')->default(0);
            $table->integer('prosedur_non_bedah')->default(0);
            $table->integer('prosedur_bedah')->default(0);
            $table->integer('konsultasi')->default(0);
            $table->integer('tenaga_ahli')->default(0);
            $table->integer('keperawatan')->default(0);
            $table->integer('penunjang')->default(0);
            $table->integer('radiologi')->default(0);
            $table->integer('laboratorium')->default(0);
            $table->integer('pelayanan_darah')->default(0);
            $table->integer('rehabilitasi')->default(0);
            $table->integer('kamar_akomodasi')->default(0);
            $table->integer('rawat_intensif')->default(0);
            $table->integer('obat')->default(0);
            $table->integer('alkes')->default(0);
            $table->integer('bmhp')->default(0);
            $table->integer('sewa_alat')->default(0);
            $table->integer('obat_kronis')->default(0);
            $table->integer('obat_kemo')->default(0);
            $table->timestamps();

            $table->foreign('jm_pasien_id')->references('id')->on('jm_pasien')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jm_rincian');
    }
};
