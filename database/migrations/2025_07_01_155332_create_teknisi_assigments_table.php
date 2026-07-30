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
        Schema::create('asset_maintc_teknisi_assigment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maintc_jadwal_id')->index();
            $table->unsignedBigInteger('teknisi_id')->index();
            $table->enum('role', ['leader', 'helper'])->default('helper');
            $table->timestamps();


            $table->foreign('maintc_jadwal_id')->references('id')->on('asset_maintc_jadwal')->onDelete('cascade');
            $table->foreign('teknisi_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_maintc_teknisi_assigment');
    }
};
