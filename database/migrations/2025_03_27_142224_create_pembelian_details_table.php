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
        Schema::create('um_pembelian_det', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pembelian_id');
            $table->unsignedBigInteger('barang_id');
            $table->integer('jumlah');
            $table->text('batch')->nullable();
            $table->decimal('harga_satuan', 16, 2)->default(0);
            $table->softDeletes('deleted_at');
            $table->timestamps();

            // referensi
            $table->foreign('pembelian_id')->references('id')->on('um_pembelian')->cascadeOnDelete();
            $table->foreign('barang_id')->references('id')->on('um_barang')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('um_pembelian_det');
    }
};
