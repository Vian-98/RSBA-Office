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
        Schema::create('sdm_bagian_shift', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bagian_id');
            $table->unsignedBigInteger('shift_id');
            $table->timestamps();

            $table->foreign('bagian_id')->references('id')->on('bagian')->onDelete('cascade');
            $table->foreign('shift_id')->references('id')->on('sdm_jadwal_shift')->onDelete('cascade');
            $table->unique(['bagian_id', 'shift_id'], 'uniq_bagian_shift');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_bagian_shift');
    }
};
