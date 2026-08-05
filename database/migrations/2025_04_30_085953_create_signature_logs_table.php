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
        Schema::create('signature_logs', function (Blueprint $table) {
            $table->id();
            $table->json('data');
            $table->longText('signature_hash');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('certificate_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('certificate_id')->references('id')->on('signature_certs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signature_logs');
    }
};
