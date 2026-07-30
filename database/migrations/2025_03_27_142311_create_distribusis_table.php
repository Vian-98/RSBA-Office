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
        Schema::create('um_distribusi', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->unsignedBigInteger('tujuan');
            $table->unsignedBigInteger('pengirim');
            $table->unsignedBigInteger('penerima');
            $table->string('dist_as', 16);
            $table->string('keterangan')->nullable();
            $table->timestamps();

            // reference to ruangan
            $table->foreign('tujuan')->references('id')->on('ruangan')->onDelete('cascade');
            // reference to users
            $table->foreign('pengirim')->references('id')->on('users')->onDelete('cascade');

            // reference to karyawan
            $table->foreign('penerima')->references('id')->on('sdm_karyawan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('um_distribusi');
    }
};
