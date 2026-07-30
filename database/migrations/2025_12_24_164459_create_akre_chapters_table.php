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
        Schema::create('akre_chapter', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kegiatan_id');
            $table->string('nama');
            $table->string('singkatan');
            $table->longText('deskripsi')->nullable();
            $table->unsignedBigInteger('pic_id')->nullable();
            $table->unsignedBigInteger('assesor_id')->nullable();
            $table->float('total_element')->default(0);
            $table->float('total_nilai')->default(0);
            $table->string('folder_path');
            $table->timestamps();

            $table->foreign('kegiatan_id')
                ->references('id')
                ->on('akre_kegiatan')
                ->onDelete('cascade');

            $table->foreign('pic_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('assesor_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index('kegiatan_id');
            $table->index('pic_id');
            $table->index('assesor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akre_chapter');
    }
};
