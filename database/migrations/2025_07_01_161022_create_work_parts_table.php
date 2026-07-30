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
        Schema::create('asset_maintc_work_parts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maintc_work_id')->index();
            $table->unsignedBigInteger('barang_id')->index();
            $table->enum('for', ['new', 'bhp', 'replace'])
                ->default('new')
                ->comment('new: New part, bhp: Bahan Habis Pakai, replace: Replace part');
            $table->string('old_asset_id')->nullable()
                ->comment(
                    'new: Null, bhp: Null'
                );
            $table->string('new_asset_id')->nullable()
                ->comment(
                    'bhp: Null'
                );
            $table->decimal('qty', 10, 2)->default(0);
            $table->decimal('harga_satuan', 10, 2)->default(0);
            $table->enum('status', ['distributed', 'requested'])->nullable()
                ->comment(
                    "distributed: telah terpasang, requested : saat end work stok barang kosong dan malakukan request permintaan pengadaan."
                );
            $table->unsignedBigInteger('distribusi_id')->index()->nullable();
            $table->unsignedBigInteger('request_id')->index()->nullable();
            $table->timestamps();

            // relationships
            $table->foreign('maintc_work_id')
                ->references('id')
                ->on('asset_maintc_work')
                ->onDelete('cascade');

            $table->foreign('barang_id')
                ->references('id')
                ->on('um_barang')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_maintc_work_parts');
    }
};
