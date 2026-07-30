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
        Schema::create('um_distribusi_det', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distribusi_id');
            $table->unsignedBigInteger('stok_id');
            $table->integer('jml');
            $table->timestamps();

            // reference to distribusi
            $table->foreign('distribusi_id')->references('id')->on('um_distribusi')->onDelete('cascade');
            // reference to stok
            $table->foreign('stok_id')->references('id')->on('um_stok')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('um_distribusi_det');
    }
};
