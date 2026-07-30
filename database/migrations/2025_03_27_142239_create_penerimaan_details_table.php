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
        Schema::create('um_penerimaan_beli_det', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penerimaan_id');
            $table->unsignedBigInteger('pembelian_det_id');
            $table->integer('jumlah');
            $table->timestamps();

            // Rerefensi
            $table->foreign('penerimaan_id')->references('id')->on('um_penerimaan_beli')->cascadeOnDelete();
            $table->foreign('pembelian_det_id')->references('id')->on('um_pembelian_det')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('um_penerimaan_beli_det');
    }
};
