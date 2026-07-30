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
        Schema::create('asset_maintc_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id')->index();
            $table->unsignedBigInteger('user_req_id');
            $table->text('note')->nullable();
            $table->enum('priority', ['normal', 'penting', 'darurat'])->default('normal'); // normal, urgent, emergency
            $table->string('ket_priority')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending'); // pending, approved, rejected, completed
            $table->unsignedBigInteger('user_verify_id')->nullable();
            $table->text('ket_reject')->nullable(); // Reason for rejection
            $table->json('lampiran')->nullable(); // Store file paths or metadata
            $table->timestamps();

            $table->foreign('asset_id')->references('id')->on('asset_barang')->onDelete('cascade');
            $table->foreign('user_req_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_verify_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_maintenance_requests');
    }
};
