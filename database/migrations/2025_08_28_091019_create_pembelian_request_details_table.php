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
        Schema::create('um_pembelian_requests_det', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pembelian_req_id')->index();
            $table->unsignedBigInteger('barang_id')->index();
            $table->integer('jml_req');
            $table->integer('jml_disetujui')->default(0);
            $table->decimal('harga_est', 10, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->json('specs')->nullable();
            $table->unsignedBigInteger('pembelian_id')->index()->nullable()
                ->comment('Pembelian id jika request accepted dan diteruskan untuk pembelian');
            $table->timestamps();


            // relations
            $table->foreign('pembelian_req_id')->references('id')->on('um_pembelian_requests')->onDelete('cascade');

            $table->foreign('barang_id')->references('id')->on('um_barang')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('um_pembelian_requests_det');
    }
};
