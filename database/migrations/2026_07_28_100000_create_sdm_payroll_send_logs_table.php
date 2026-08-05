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
        Schema::create('sdm_payroll_send_logs', function (Blueprint $table) {
            $table->id();
            $table->string('periode', 7); // YYYY-MM
            $table->unsignedBigInteger('karyawan_id');
            $table->string('email')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->enum('tipe_pengiriman', ['manual', 'auto_scheduled', 'instant_batch'])->default('manual');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['periode', 'karyawan_id']);
            $table->index(['periode', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_payroll_send_logs');
    }
};
