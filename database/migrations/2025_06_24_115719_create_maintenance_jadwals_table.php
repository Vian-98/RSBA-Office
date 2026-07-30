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
        Schema::create('asset_maintc_jadwal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id')->index();
            $table->unsignedBigInteger('maintc_request_id')->nullable();
            $table->date('tanggal');
            $table->enum('priority', ['normal', 'penting', 'darurat'])->default('normal'); // normal, urgent, emergency
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('asset_id')->references('id')->on('asset_barang')->onDelete('cascade');
            $table->foreign('maintc_request_id')->references('id')->on('asset_maintc_requests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_maintenance_jadwal');
    }
};
