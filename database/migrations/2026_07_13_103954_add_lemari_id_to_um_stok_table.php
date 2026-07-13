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
            $table->unsignedBigInteger('lemari_id')->nullable()->after('penyimpanan_id');

            $table->foreign('lemari_id')->references('id')->on('um_penyimpanan_lemaris')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('um_stok', function (Blueprint $table) {
            $table->dropForeign(['lemari_id']);
            $table->dropColumn('lemari_id');
        });
    }
};
