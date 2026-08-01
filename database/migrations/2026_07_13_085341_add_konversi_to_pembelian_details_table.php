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
        Schema::table('um_pembelian_det', function (Blueprint $table) {
            $table->unsignedBigInteger('satuan_beli_id')->nullable()->after('barang_id');
            $table->integer('qty_beli')->nullable()->after('satuan_beli_id');
            $table->integer('rasio')->default(1)->after('qty_beli');
            
            $table->foreign('satuan_beli_id')->references('id')->on('um_satuan')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('um_pembelian_det', function (Blueprint $table) {
            $table->dropForeign(['satuan_beli_id']);
            $table->dropColumn(['satuan_beli_id', 'qty_beli', 'rasio']);
        });
    }
};
