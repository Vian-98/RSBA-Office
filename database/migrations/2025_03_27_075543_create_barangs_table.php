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
        Schema::create('um_barang', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 14)->unique();
            $table->string('nama', 25);
            $table->unsignedBigInteger('satuan_id');
            $table->unsignedBigInteger('kategori_id');
            $table->enum('tipe', ['umum', 'asset'])->default('umum');
            $table->integer('min_stok')->default(0);
            $table->boolean('bhp')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('um_barang');
    }
};
