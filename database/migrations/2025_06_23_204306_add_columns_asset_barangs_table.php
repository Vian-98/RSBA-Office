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
        Schema::table('asset_barang', function (Blueprint $table) {
            // Tambahkan kolom baru
            $table->enum('jenis', ['main', 'component'])->default('main')->after('keterangan');
            $table->integer('level')->default(0)->after('main_asset_id'); // Level untuk menentukan hierarki atau urutan
            // Tambahkan index untuk kolom jenis
            $table->index('jenis', 'main_asset_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_barang', function (Blueprint $table) {
            // Hapus kolom yang ditambahkan
            $table->dropColumn(['jenis', 'level']);
            // Hapus index jika ada
            $table->dropIndex('main_asset_id');
        });
    }
};
