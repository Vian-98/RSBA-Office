<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('um_opname_stok', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['process', 'investigating', 'completed'])->default('process');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('selesai_by')->nullable();
            $table->dateTime('selesai')->nullable();
            $table->unsignedBigInteger('validate_by')->nullable();
            $table->dateTime('validate_at')->nullable();
            $table->timestamps();

            // Relations
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('selesai_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('validate_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('um_opname_stok');
    }
};
