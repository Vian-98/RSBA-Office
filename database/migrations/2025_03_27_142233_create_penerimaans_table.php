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
        Schema::create('um_penerimaan_beli', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('no_faktur', 20);
            $table->string('keterangan')->nullable();
            $table->unsignedBigInteger('penerima');
            $table->timestamps();

            $table->foreign('penerima')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('um_penerimaan_beli');
    }
};
