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
        Schema::create('um_opname_investigasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('opname_detail_id');

            // Data Investigasi
            $table->integer('stok_sistem_validasi')->default(0);
            $table->integer('total_mutasi_during_investigasi')->default(0);
            $table->integer('stok_adjustment')->default(0);
            $table->enum('hasil_investigasi', [
                'HILANG',
                'RUSAK',
                'SALAH_INPUT',
                'BARANG_DITEMUKAN',
                'KEUNTUNGAN_STOK',
                'LAINNYA'
            ]);
            $table->text('catatan_investigasi');
            $table->unsignedBigInteger('investigated_by');
            $table->dateTime('investigated_at');
            $table->timestamps();


            $table->foreign('opname_detail_id')
                ->references('id')
                ->on('um_opname_stok_details')
                ->onDelete('cascade');

            $table->foreign('investigated_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index('opname_detail_id');
            $table->index('investigated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('um_opname_investigasi');
    }
};
