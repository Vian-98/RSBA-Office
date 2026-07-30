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
        Schema::create('um_stok', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penerimaan_det_id');
            $table->unsignedBigInteger('barang_id');
            $table->integer('stok');
            $table->string('batch')->nullable();
            $table->decimal('harga_satuan', 16, 2);
            $table->unsignedBigInteger('penyimpanan_id')->nullable();
            $table->softDeletes('deleted_at');
            $table->timestamps();


            // Referensi
            $table->foreign('penerimaan_det_id')->references('id')->on('um_penerimaan_beli_det')->cascadeOnDelete();
            $table->foreign('barang_id')->references('id')->on('um_barang')->cascadeOnDelete();
            $table->foreign('penyimpanan_id')->references('id')->on('um_penyimpanan')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('um_stok');
    }
};
