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
        Schema::create('asset_barang', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distribusi_det_id');
            $table->unsignedBigInteger('barang_id');
            $table->unsignedBigInteger('ruangan_id');
            $table->string('kode', 50)->unique()->nullable();
            $table->date('tanggal_catat');
            $table->decimal('nilai', 16, 2)->nullable()->default(0);
            $table->enum('status', ['baik', 'diperbaiki', 'rusak', 'hilang'])->default('baik');
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('main_asset_id')->nullable();
            $table->timestamps();

            //Referensi
            $table->foreign('distribusi_det_id')->references('id')->on('um_distribusi_det')->onDelete('cascade');
            $table->foreign('barang_id')->references('id')->on('um_barang')->onDelete('cascade');
            $table->foreign('ruangan_id')->references('id')->on('ruangan')->onDelete('cascade');
            $table->foreign('main_asset_id')->references('id')->on('asset_barang')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_barang');
    }
};
