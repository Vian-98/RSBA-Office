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
            $table->date('warranty')->nullable()->after('batch');
            $table->integer('diskon')->nullable()->after('harga_satuan');
            $table->integer('ppn')->nullable()->after('diskon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('um_pembelian_det', function (Blueprint $table) {
            $table->dropColumn(['warranty', 'diskon', 'ppn']);
        });
    }
};
