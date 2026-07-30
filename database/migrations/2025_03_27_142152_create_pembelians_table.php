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
        Schema::create('um_pembelian', function (Blueprint $table) {
            $table->id();
            $table->string('no', 10)->unique();
            $table->date('tgl');
            $table->unsignedBigInteger('supplier_id');
            $table->enum('jenis', ['langsung', 'pre_order'])->default('langsung');
            $table->enum('status_pembayaran', ['lunas', 'tempo'])->nullable();
            $table->date('tgl_pembayaran')->nullable();
            $table->enum('status', ['waiting', 'selesai', 'dibatalkan'])->default('waiting');
            $table->decimal('total', 16, 2)->default(0);
            $table->timestamps();

            //Referensi
            $table->foreign('supplier_id')->references('id')->on('um_supplier')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('um_pembelian');
    }
};
