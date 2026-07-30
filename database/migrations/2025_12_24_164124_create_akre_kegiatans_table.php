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
        Schema::create('akre_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->date('tanggal');
            $table->string('nama');
            $table->string('standard');
            $table->string('lembaga')->nullable();
            $table->float('total_nilai')->default(0);
            $table->string('folder_path');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();


            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akre_kegiatan');
    }
};
