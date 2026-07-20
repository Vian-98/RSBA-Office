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
        Schema::create('sdm_payroll_edit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_slip_id');
            $table->unsignedBigInteger('karyawan_id');
            $table->string('periode', 7);
            $table->json('perubahan'); // JSON representation of changes: {field_name: {label: x, old: y, new: z}}
            $table->unsignedBigInteger('diubah_oleh');
            $table->timestamps();

            $table->foreign('payroll_slip_id')->references('id')->on('sdm_payroll_slips')->onDelete('cascade');
            $table->foreign('karyawan_id')->references('id')->on('sdm_karyawan')->onDelete('cascade');
            $table->foreign('diubah_oleh')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_payroll_edit_logs');
    }
};
