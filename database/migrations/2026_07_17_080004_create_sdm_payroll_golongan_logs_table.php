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
        Schema::create('sdm_payroll_golongan_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tipe', 30); // Matrix, Tunjangan, Matrix (Struktur)
            $table->string('kunci', 100);
            $table->string('nilai_lama', 100)->nullable();
            $table->string('nilai_baru', 100);
            $table->unsignedBigInteger('user_id');
            $table->timestamp('created_at')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_payroll_golongan_logs');
    }
};
