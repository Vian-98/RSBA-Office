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
        Schema::table('um_stok', function (Blueprint $table) {
            $table->boolean('is_open')->default(true)->after('penyimpanan_id')
                ->comment('Menandakan stok batch ini masih aktif/terbuka untuk opname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('um_stok', function (Blueprint $table) {
            $table->dropColumn('is_open');
        });
    }
};
