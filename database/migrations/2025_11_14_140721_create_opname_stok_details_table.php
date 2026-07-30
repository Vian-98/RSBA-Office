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
        Schema::create('um_opname_stok_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('opname_id');
            $table->unsignedBigInteger('barang_id');
            $table->unsignedBigInteger('stok_id');
            $table->integer('stok_sistem_opname');
            $table->decimal('harga_satuan', 12, 2);
            $table->integer('stok_fisik')->default(0);
            $table->integer('selisih')->default(0);
            $table->text('ket')->nullable();
            $table->unsignedBigInteger('opname_by')->nullable();
            $table->timestamps();

            // relations
            $table->foreign('opname_id')
                ->references('id')
                ->on('um_opname_stok')
                ->onDelete('cascade');

            $table->foreign('barang_id')
                ->references('id')
                ->on('um_barang')
                ->onDelete('cascade');

            $table->foreign('stok_id')
                ->references('id')
                ->on('um_stok')
                ->onDelete('cascade');

            $table->foreign('opname_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index(['opname_id', 'barang_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('um_opname_stok_details');
    }
};
