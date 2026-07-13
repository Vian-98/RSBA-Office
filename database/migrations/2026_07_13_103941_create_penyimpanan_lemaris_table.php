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
        Schema::create('um_penyimpanan_lemaris', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penyimpanan_id');
            $table->string('nama_lemari');
            $table->string('deskripsi')->nullable();
            $table->timestamps();

            $table->foreign('penyimpanan_id')->references('id')->on('um_penyimpanan')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyimpanan_lemaris');
    }
};
