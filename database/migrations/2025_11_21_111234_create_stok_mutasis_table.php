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
        Schema::create('um_stok_mutasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stok_id');
            $table->unsignedBigInteger('barang_id');
            $table->enum('jenis_mutasi', [
                'PEMBELIAN',
                'DISTRIBUSI',
                'RETUR_BELI',
                'RETUR_DISTRIBUSI',
                'RUSAK',
                'HILANG',
                'TRANSFER_MASUK',
                'TRANSFER_KELUAR',
                'ADJUSTMENT_PLUS',
                'ADJUSTMENT_MINUS',
                'OPNAME',
                'OPNAME_MISSING',
                'OPNAME_FOUND',
                'OPNAME_WRITE_OFF',
                'OPNAME_CORRECTION'
            ])->nullable();
            $table->integer('jumlah');
            $table->integer('multiplier')->default(1);
            $table->integer('jumlah_bersih')->storedAs('jumlah * multiplier');
            $table->integer('stok_sebelum');
            $table->integer('stok_sesudah');
            $table->string('keterangan')->nullable();
            $table->morphs('referensi');
            $table->unsignedBigInteger('created_by');
            $table->boolean('is_posted')->default(false);
            $table->boolean('is_reversed')->default(false);
            $table->unsignedBigInteger('reversed_of_id')->nullable();
            $table->timestamps();

            // relations
            $table->foreign('stok_id')
                ->references('id')
                ->on('um_stok')
                ->onDelete('cascade');

            $table->foreign('barang_id')
                ->references('id')
                ->on('um_barang')
                ->onDelete('cascade');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('reversed_of_id')
                ->references('id')
                ->on('um_stok_mutasi')
                ->onDelete('cascade');

            $table->index('stok_id');
            $table->index('jenis_mutasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('um_stok_mutasi');
    }
};
