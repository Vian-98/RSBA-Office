<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kuitansi_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kuitansi_id');
            $table->string('keterangan', 255);
            $table->decimal('nominal', 16, 2);
            $table->timestamps();

            $table->foreign('kuitansi_id')->references('id')->on('kuitansi')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuitansi_details');
    }
};
