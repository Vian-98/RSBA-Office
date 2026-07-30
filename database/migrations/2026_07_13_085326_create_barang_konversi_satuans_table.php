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
        Schema::create('um_barang_konversi_satuans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('barang_id');
            $table->unsignedBigInteger('satuan_id');
            $table->integer('rasio')->default(1);
            $table->timestamps();
            
            $table->foreign('barang_id')->references('id')->on('um_barang')->cascadeOnDelete();
            $table->foreign('satuan_id')->references('id')->on('um_satuan')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('um_barang_konversi_satuans');
    }
};
