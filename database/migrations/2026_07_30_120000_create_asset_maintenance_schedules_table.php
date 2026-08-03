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
        Schema::create('asset_maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_barang_id')->constrained('asset_barang')->onDelete('cascade');
            $table->string('judul')->default('Service / Pengecekan Rutin');
            $table->enum('interval_unit', ['month', 'year'])->default('month');
            $table->integer('interval_value')->default(1);
            $table->date('tgl_mulai');
            $table->date('tgl_berikutnya');
            $table->date('terakhir_dilakukan')->nullable();
            $table->text('catatan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_maintenance_schedules');
    }
};
