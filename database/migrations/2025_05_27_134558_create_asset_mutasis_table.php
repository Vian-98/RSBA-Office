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
        Schema::create('asset_mutasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id')->index();
            $table->unsignedBigInteger('ruangan_asal_id')->index();
            $table->unsignedBigInteger('ruangan_tujuan_id')->index();
            $table->date('tanggal');
            $table->string('keterangan')->nullable();
            $table->unsignedBigInteger('user_id')->index();
            $table->timestamps();

            $table->foreign('asset_id')->references('id')->on('asset_barang')->onDelete('cascade');
            $table->foreign('ruangan_asal_id')->references('id')->on('ruangan')->onDelete('cascade');
            $table->foreign('ruangan_tujuan_id')->references('id')->on('ruangan')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_mutasi');
    }
};
