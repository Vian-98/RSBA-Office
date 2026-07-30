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
        Schema::create('akre_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('element_id');
            $table->string('nama');
            $table->string('filename');
            $table->string('path');
            $table->string('mime_type');
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamps();

            $table->foreign('element_id')
                ->references('id')
                ->on('akre_elements')
                ->onDelete('cascade');

            $table->index('element_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akre_files');
    }
};
