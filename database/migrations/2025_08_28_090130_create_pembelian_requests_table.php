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
        Schema::create('um_pembelian_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_req_id')->index();
            $table->text('note')->nullable();
            $table->enum('priority', ['normal', 'penting', 'darurat'])->default('normal');
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->unsignedBigInteger('user_verify_id')->nullable();
            $table->text('ket_reject')->nullable()->comment('Alasan rejected');
            $table->json('lampirans')->nullable();
            $table->timestamps();


            // Relations
            $table->foreign('user_req_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_verify_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('um_pembelian_requests');
    }
};
