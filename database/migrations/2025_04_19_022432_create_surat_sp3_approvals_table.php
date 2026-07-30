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
        Schema::create('surat_sp3_approval', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surat_sp3_id');
            $table->unsignedBigInteger('disetujui');
            $table->enum('status', ['approved', 'rejected']);
            $table->string('keterangan')->nullable();
            $table->string('approved_at', 25);
            $table->longText('signature_hash');
            $table->timestamps();

            $table->foreign('surat_sp3_id')->references('id')->on('surat_sp3')->onDelete('cascade');
            $table->foreign('disetujui')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_sp3_approval');
    }
};
