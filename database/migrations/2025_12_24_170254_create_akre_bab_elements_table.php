<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('akre_bab_elements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chapter_id');
            $table->char('no');
            $table->longText('nama');
            if (DB::getDriverName() !== 'sqlite') {
                $table->fullText('nama');
            }
            $table->longText('deskripsi')->nullable();
            $table->longText('maksud_tujuan')->nullable();
            $table->enum('bab', ['bab', 'sub']);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();

            $table->foreign('chapter_id')
                ->references('id')
                ->on('akre_chapter')
                ->onDelete('cascade');

            $table->foreign('parent_id')
                ->references('id')
                ->on('akre_bab_elements')
                ->onDelete('cascade');

            $table->index('chapter_id');
            $table->index('bab');
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akre_bab_elements');
    }
};
