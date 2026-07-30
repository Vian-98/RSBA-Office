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
        Schema::create('asset_maintc_work', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id')->index();
            $table->unsignedBigInteger('maintc_jadwal_id')->index();
            $table->dateTime('mulai')->nullable();
            $table->unsignedBigInteger('mulai_by')->nullable();
            $table->dateTime('selesai')->nullable();
            $table->unsignedBigInteger('selesai_by')->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'done', 'cancelled'])->default('pending');
            $table->decimal('total_biaya', 16, 2)->default(0); // Cost of the maintenance work
            $table->json('dokumentasi')->nullable(); // JSON field to store documentation, e.g., images
            $table->timestamps();

            $table->foreign('asset_id')->references('id')->on('asset_barang')->onDelete('cascade');
            $table->foreign('maintc_jadwal_id')->references('id')->on('asset_maintc_jadwal')->onDelete('cascade');
            $table->foreign('mulai_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('selesai_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_maintc_work');
    }
};
